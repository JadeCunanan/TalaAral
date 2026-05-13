<?php
/**
 * get_rtu_updates.php
 * Fetches multiple pages of each RSS feed to work around WordPress's 10-item limit.
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// 1. DYNAMIC CONFIGURATION
$feed_sources = getenv('RTU_RSS_FEEDS') ?: 'https://www.rtu.edu.ph/feed/,https://www.rtu.edu.ph/category/announcement/feed/';
define('RTU_FEED_BASES', explode(',', $feed_sources));

define('FEED_PAGES', getenv('RTU_FEED_PAGES') ?: 3);
define('CACHE_FILE', sys_get_temp_dir() . '/talaaral_rtu_cache.json');
define('CACHE_TTL', getenv('RTU_CACHE_TTL') ?: 0);
define('MAX_ITEMS', 50);

// 2. CACHE CHECK
if (file_exists(CACHE_FILE) && CACHE_TTL > 0) {
    $age = time() - filemtime(CACHE_FILE);
    if ($age < CACHE_TTL) {
        $cached = json_decode(file_get_contents(CACHE_FILE), true);
        if (is_array($cached)) {
            echo json_encode(apply_preview($cached));
            exit;
        }
    }
}

$all_items = [];

foreach (RTU_FEED_BASES as $base_url) {
    for ($page = 1; $page <= FEED_PAGES; $page++) {
        $feed_url = trim($base_url) . '?paged=' . $page;
        $xml_string = fetch_url($feed_url);
        if (!$xml_string) continue;

        $page_items = parse_rss($xml_string);

        if (empty($page_items)) break;

        $all_items = array_merge($all_items, $page_items);
    }
}

// 3. DEDUPLICATION & SORTING
$seen   = [];
$unique = [];
foreach ($all_items as $item) {
    if (!isset($seen[$item['url']])) {
        $seen[$item['url']] = true;
        $unique[] = $item;
    }
}

usort($unique, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
$unique = array_slice($unique, 0, MAX_ITEMS);

if (!empty($unique)) {
    file_put_contents(CACHE_FILE, json_encode($unique));
}

echo json_encode(apply_preview($unique));
exit;

function is_valid_image(string $url): bool {
    if (empty($url)) return false;
    $lowUrl = strtolower($url);
    $banned = ['sustainable-development-goals', 'sdg', 'goal', 'program', 'cropped-', 'logo', 'favicon', 'banner', 'header', 'footer', 'sidebar', 'facebook', 'twitter', 'instagram'];

    foreach ($banned as $word) {
        if (str_contains($lowUrl, $word)) return false;
    }
    return !preg_match('/\/\d+(-\d+)?-e\d*\./', $lowUrl);
}

function fetch_url(string $url): string|false {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'TalaAral-Academic-Dashboard/1.0');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($httpCode === 200) ? $response : false;
}

function strip_thumbnail_from_content(string $content, string $thumbnail_url): string {
    if (empty($content) || empty($thumbnail_url)) return $content;
    $thumb_filename = preg_quote(basename(strtok($thumbnail_url, '?#')), '/');
    $content = preg_replace('/<figure[^>]*>(?:(?!<\/figure>).)*?' . $thumb_filename . '(?:(?!<\/figure>).)*?<\/figure>/si', '', $content, 1);
    return preg_replace('/<img[^>]+' . $thumb_filename . '[^>]*>/i', '', $content, 1);
}

function parse_rss(string $xml_string): array {
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
    if ($xml === false) return [];

    $items = [];
    foreach ($xml->channel->item as $item) {
        $title = trim((string) $item->title);
        $url   = trim((string) $item->link);
        $pub   = trim((string) $item->pubDate);
        if (empty($title) || empty($url)) continue;

        $timestamp   = strtotime($pub) ?: 0;
        $ns_content  = $item->children('content', true);
        $content_raw = isset($ns_content->encoded) ? (string)$ns_content->encoded : '';
        $potential_images = [];

        $ns_media = $item->children('media', true);
        if (isset($ns_media->thumbnail)) { $potential_images[] = (string)$ns_media->thumbnail->attributes()['url']; }

        if (isset($item->enclosure)) {
            $enc = $item->enclosure->attributes();
            if (str_starts_with((string)$enc['type'], 'image/')) { $potential_images[] = (string)$enc['url']; }
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

        $items[] = [
            'title'     => $title,
            'url'       => $url,
            'date'      => date('M j, Y', $timestamp),
            'timestamp' => $timestamp,
            'category'  => str_contains(strtolower($title), 'announcement') ? 'announcement' : 'news',
            'excerpt'   => mb_strimwidth(strip_tags((string)$item->description), 0, 160, '…'),
            'thumbnail' => $thumbnail,
            'content'   => $thumbnail ? strip_thumbnail_from_content($content_raw, $thumbnail) : $content_raw,
        ];
    }
    return $items;
}

function apply_preview(array $items): array {
    return (isset($_GET['preview']) && $_GET['preview'] === '1') ? array_slice($items, 0, 3) : $items;
}