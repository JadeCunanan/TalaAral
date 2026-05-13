<?php

/**
 * get_rtu_updates.php
 * Fetches RTU RSS feeds using SimplePie.
 * Supports ?page= for paginated Load More on university-news feed.
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if (session_status() === PHP_SESSION_NONE) session_start();

require_once _DIR_ . '/../vendor/autoload.php';

$feed_sources = getenv('RTU_RSS_FEEDS') ?: 'https://www.rtu.edu.ph/feed/,https://www.rtu.edu.ph/category/announcement/feed/,https://www.rtu.edu.ph/university-news/feed/';
$feed_urls    = array_map('trim', explode(',', $feed_sources));
$cache_ttl    = (int)(getenv('RTU_CACHE_TTL') ?: 900);
$cache_file   = sys_get_temp_dir() . '/talaaral_rtu_cache.json';
$max_items    = 50;

// ?page= param — 1 = default feeds, 2+ = paginate university-news only
$requested_page = max(1, (int)($_GET['page'] ?? 1));
$is_preview     = isset($_GET['preview']);
$is_load_more   = $requested_page > 1;

function fetch_rss_raw(string $url): string|false
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING       => '',
        CURLOPT_HEADER         => true,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/rss+xml, application/xml, text/xml, /',
            'Accept-Language: en-US,en;q=0.9',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Connection: keep-alive',
        ],
    ]);
    $response    = curl_exec($ch);
    $http_code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $accepted_codes = [200, 415];
    if ($response === false || !in_array($http_code, $accepted_codes)) {
        error_log("fetch_rss_raw failed for $url — HTTP $http_code");
        return false;
    }

    return substr($response, $header_size);
}

function is_valid_image(string $url): bool
{
    if (empty($url)) return false;
    $lowUrl = strtolower($url);
    $banned = ['sustainable-development-goals', 'sdg', 'goal', 'program', 'cropped-', 'logo', 'favicon', 'banner', 'header', 'footer', 'sidebar', 'facebook', 'twitter', 'instagram'];
    foreach ($banned as $word) {
        if (str_contains($lowUrl, $word)) return false;
    }
    return !preg_match('/\/\d+(-\d+)?-e\d*\./', $lowUrl);
}

function strip_thumbnail_from_content(string $content, string $thumbnail_url): string
{
    if (empty($content) || empty($thumbnail_url)) return $content;
    $thumb_filename = preg_quote(basename(strtok($thumbnail_url, '?#')), '/');
    $content = preg_replace('/<figure[^>]*>(?:(?!<\/figure>).)*?' . $thumb_filename . '(?:(?!<\/figure>).)*?<\/figure>/si', '', $content, 1);
    return preg_replace('/<img[^>]+' . $thumb_filename . '[^>]*>/i', '', $content, 1);
}

function parse_feed(string $raw, bool $is_announcement_feed): array
{
    $feed = new SimplePie\SimplePie();
    $feed->set_raw_data($raw);
    $feed->enable_cache(false);
    $feed->force_feed(true);
    $feed->init();

    if ($feed->error()) {
        error_log("SimplePie parse error: " . $feed->error());
        return [];
    }

    $items  = $feed->get_items(0, 50);
    $result = [];

    foreach ($items as $item) {
        $title = html_entity_decode(trim($item->get_title() ?? ''));
        $url   = trim($item->get_permalink() ?? '');
        if (empty($title) || empty($url)) continue;

        $content_raw      = $item->get_content() ?? $item->get_description() ?? '';
        $potential_images = [];

        $enclosure = $item->get_enclosure();
        if ($enclosure && str_starts_with($enclosure->get_type() ?? '', 'image/')) {
            $potential_images[] = $enclosure->get_link() ?? '';
        }
        $thumb = $item->get_thumbnail();
        if (!empty($thumb['url'])) $potential_images[] = $thumb['url'];
        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content_raw, $matches)) {
            $potential_images = array_merge($potential_images, $matches[1]);
        }

        $thumbnail = '';
        foreach ($potential_images as $img_url) {
            if (is_valid_image($img_url)) {
                $thumbnail = $img_url;
                break;
            }
        }

        $item_cats = $item->get_categories();
        $cat_names = [];
        if ($item_cats) {
            foreach ($item_cats as $cat) $cat_names[] = strtolower($cat->get_label() ?? '');
        }
        $is_announcement = $is_announcement_feed
            || in_array('announcements', $cat_names)
            || in_array('announcement', $cat_names)
            || str_contains(strtolower($title), 'announcement');

        $excerpt = mb_strimwidth(strip_tags(html_entity_decode($item->get_description() ?? '')), 0, 160, '…');

        // Skip items with empty or whitespace-only excerpts
        if (empty($title) || empty($url) || empty(trim(str_replace('…', '', $excerpt)))) continue;

        $result[] = [
            'id'        => md5($url),
            'title'     => $title,
            'url'       => $url,
            'date'      => $item->get_date('M j, Y') ?? '',
            'timestamp' => (int)($item->get_date('U') ?? 0),
            'category'  => $is_announcement ? 'announcement' : 'news',
            'excerpt'   => $excerpt,
            'thumbnail' => $thumbnail,
            'content'   => $thumbnail ? strip_thumbnail_from_content($content_raw, $thumbnail) : $content_raw,
        ];
    }

    return $result;
}

// -------------------------------------------------------
// LOAD MORE — fetch a specific page of university-news
// Returns only new items not already known by the client
// -------------------------------------------------------
if ($is_load_more) {
    $known_ids = [];
    if (!empty($_GET['known'])) {
        // Frontend sends comma-separated md5 IDs it already has
        $known_ids = array_flip(explode(',', $_GET['known']));
    }

    $paginated_url = 'https://www.rtu.edu.ph/university-news/feed/?paged=' . $requested_page;
    $raw = fetch_rss_raw($paginated_url);

    if ($raw === false) {
        echo json_encode(['items' => [], 'has_more' => false, 'error' => 'fetch_failed']);
        exit;
    }

    $new_items = parse_feed($raw, false);

    // Filter out items the frontend already has
    $new_items = array_values(array_filter($new_items, fn($i) => !isset($known_ids[$i['id']])));

    // If all items were duplicates, no more pages
    $has_more = count($new_items) > 0 && $requested_page < 5; // RTU has 5 pages

    // Merge into accumulative cache
    $cached_items = [];
    if (file_exists($cache_file)) {
        $decoded = json_decode(file_get_contents($cache_file), true);
        if (is_array($decoded)) $cached_items = $decoded;
    }
    $merged = [];
    foreach ($new_items as $item) $merged[$item['url']] = $item;
    foreach ($cached_items as $item) {
        if (!isset($merged[$item['url']])) $merged[$item['url']] = $item;
    }
    $merged = array_values($merged);
    usort($merged, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
    $merged = array_slice($merged, 0, 100);
    file_put_contents($cache_file, json_encode($merged));

    echo json_encode(['items' => $new_items, 'has_more' => $has_more]);
    exit;
}

// -------------------------------------------------------
// INITIAL LOAD — fetch all feeds, serve cache if fresh
// -------------------------------------------------------
$cached_items = [];
if (file_exists($cache_file)) {
    $decoded = json_decode(file_get_contents($cache_file), true);
    if (is_array($decoded)) $cached_items = $decoded;
}

if ($cache_ttl > 0 && !empty($cached_items) && (time() - filemtime($cache_file)) < $cache_ttl) {
    $limit = $is_preview ? 4 : 50;
    echo json_encode(array_slice($cached_items, 0, $limit));
    exit;
}

$fresh_items = [];
foreach ($feed_urls as $base_url) {
    $raw = fetch_rss_raw($base_url);
    if ($raw === false) continue;
    $parsed = parse_feed($raw, str_contains($base_url, 'announcement'));
    $fresh_items = array_merge($fresh_items, $parsed);
}

// Merge fresh + cache
$merged = [];
foreach ($fresh_items as $item) $merged[$item['url']] = $item;
foreach ($cached_items as $item) {
    if (!isset($merged[$item['url']])) $merged[$item['url']] = $item;
}
$merged = array_values($merged);
usort($merged, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
$merged = array_slice($merged, 0, 100);

if (!empty($merged)) {
    file_put_contents($cache_file, json_encode($merged));
}

$limit = $is_preview ? 4 : 50;
echo json_encode(array_slice($merged, 0, $limit));