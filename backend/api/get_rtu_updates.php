<?php
/**
 * get_rtu_updates.php
 * Fetches RTU RSS feeds using SimplePie.
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../../vendor/autoload.php';

$feed_sources = getenv('RTU_RSS_FEEDS') ?: 'https://www.rtu.edu.ph/feed/,https://www.rtu.edu.ph/category/announcement/feed/';
$feed_urls = explode(',', $feed_sources);
$cache_ttl = (int)(getenv('RTU_CACHE_TTL') ?: 900);

$all_items = [];

foreach ($feed_urls as $feed_url) {
    $feed = new SimplePie\SimplePie();
    $feed->set_feed_url(trim($feed_url));
    $feed->set_cache_duration($cache_ttl);
    $feed->set_cache_location(sys_get_temp_dir());
    $feed->set_useragent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36');
    $feed->set_timeout(15);
    $feed->force_feed(true);
    $feed->init();
    $feed->handle_content_type();

    if ($feed->error()) {
        error_log("SimplePie error for $feed_url: " . $feed->error());
        continue;
    }

    foreach ($feed->get_items() as $item) {
        $thumbnail = '';
        $enclosure = $item->get_enclosure();
        if ($enclosure && str_starts_with($enclosure->get_type() ?? '', 'image/')) {
            $thumbnail = $enclosure->get_link() ?? '';
        }
        if (!$thumbnail) {
            $thumb = $item->get_thumbnail();
            $thumbnail = $thumb['url'] ?? '';
        }

        $content_raw = $item->get_content() ?? $item->get_description() ?? '';

        $all_items[] = [
            'title'     => $item->get_title() ?? '',
            'url'       => $item->get_permalink() ?? '',
            'date'      => $item->get_date('M j, Y') ?? '',
            'timestamp' => $item->get_date('U') ?? 0,
            'category'  => str_contains(strtolower($item->get_title() ?? ''), 'announcement') ? 'announcement' : 'news',
            'excerpt'   => mb_strimwidth(strip_tags($item->get_description() ?? ''), 0, 160, '…'),
            'thumbnail' => $thumbnail,
            'content'   => $content_raw,
        ];
    }
}

// DEDUPLICATION & SORTING
$seen = [];
$unique = [];
foreach ($all_items as $item) {
    if (!empty($item['url']) && !isset($seen[$item['url']])) {
        $seen[$item['url']] = true;
        $unique[] = $item;
    }
}

usort($unique, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
$unique = array_slice($unique, 0, 50);

echo json_encode($unique);