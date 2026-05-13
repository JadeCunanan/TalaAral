<?php
/**
 * get_rtu_updates.php
 * Fetches RTU RSS feeds using SimplePie.
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    // Allow direct API calls but log unauthorized attempts
    session_start();
}

$feed_sources = getenv('RTU_RSS_FEEDS') ?: 'https://www.rtu.edu.ph/feed/,https://www.rtu.edu.ph/category/announcement/feed/,https://www.rtu.edu.ph/university-news/feed/';
$feed_urls    = array_map('trim', explode(',', $feed_sources));
$feed_pages   = (int)(getenv('RTU_FEED_PAGES') ?: 1);
$cache_ttl    = (int)(getenv('RTU_CACHE_TTL') ?: 900);
$cache_file   = sys_get_temp_dir() . '/talaaral_rtu_cache.json';
$max_items    = 50;

function fetch_rss_raw(string $url): string|false {
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
            'Accept: application/rss+xml, application/xml, text/xml, */*',
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

function is_valid_image(string $url): bool {
    if (empty($url)) return false;
    $lowUrl = strtolower($url);
    $banned = ['sustainable-development-goals', 'sdg', 'goal', 'program', 'cropped-', 'logo', 'favicon', 'banner', 'header', 'footer', 'sidebar', 'facebook', 'twitter', 'instagram'];
    foreach ($banned as $word) {
        if (str_contains($lowUrl, $word)) return false;
    }
    return !preg_match('/\/\d+(-\d+)?-e\d*\./', $lowUrl);
}

function strip_thumbnail_from_content(string $content, string $thumbnail_url): string {
    if (empty($content) || empty($thumbnail_url)) return $content;
    $thumb_filename = preg_quote(basename(strtok($thumbnail_url, '?#')), '/');
    $content = preg_replace('/<figure[^>]*>(?:(?!<\/figure>).)*?' . $thumb_filename . '(?:(?!<\/figure>).)*?<\/figure>/si', '', $content, 1);
    return preg_replace('/<img[^>]+' . $thumb_filename . '[^>]*>/i', '', $content, 1);
}

// Load existing accumulative cache
$cached_items = [];
if (file_exists($cache_file)) {
    $decoded = json_decode(file_get_contents($cache_file), true);
    if (is_array($decoded)) {
        $cached_items = $decoded;
    }
}

// Serve from cache if still fresh
if ($cache_ttl > 0 && !empty($cached_items) && (time() - filemtime($cache_file)) < $cache_ttl) {
    $limit = isset($_GET['preview']) ? 4 : 50;
    echo json_encode(array_slice($cached_items, 0, $limit));
    exit;
}

// Fetch each feed via cURL then pass raw XML to SimplePie
$fresh_items = [];

foreach ($feed_urls as $base_url) {
    $is_announcement_feed = str_contains($base_url, 'announcement');

    $raw = fetch_rss_raw($base_url);
    if ($raw === false) continue; // skip to next feed, don't stop entirely

    $feed = new SimplePie\SimplePie();
    $feed->set_raw_data($raw);
    $feed->enable_cache(false);
    $feed->force_feed(true);
    $feed->init();

    if ($feed->error()) {
        error_log("SimplePie parse error for $base_url: " . $feed->error());
        continue;
    }

    $items = $feed->get_items(0, $max_items);
    if (empty($items)) continue;

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
        if (!empty($thumb['url'])) {
            $potential_images[] = $thumb['url'];
        }

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
            foreach ($item_cats as $cat) {
                $cat_names[] = strtolower($cat->get_label() ?? '');
            }
        }
        $is_announcement = $is_announcement_feed
            || in_array('announcements', $cat_names)
            || in_array('announcement', $cat_names)
            || str_contains(strtolower($title), 'announcement');

        $raw_desc = html_entity_decode($item->get_description() ?? '');
        $excerpt  = mb_strimwidth(strip_tags($raw_desc), 0, 160, '…');

        $fresh_items[] = [
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
}

// Merge fresh into accumulated cache
$merged = [];
foreach ($fresh_items as $item) {
    $merged[$item['url']] = $item;
}
foreach ($cached_items as $item) {
    if (!isset($merged[$item['url']])) {
        $merged[$item['url']] = $item;
    }
}

$merged = array_values($merged);
usort($merged, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
$merged = array_slice($merged, 0, 100);

// Save accumulative cache
if (!empty($merged)) {
    file_put_contents($cache_file, json_encode($merged));
}

$limit = isset($_GET['preview']) ? 4 : 50;
echo json_encode(array_slice($merged, 0, $limit));