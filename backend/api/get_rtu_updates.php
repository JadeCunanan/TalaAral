<?php
/**
 * get_rtu_updates.php
 * Fetches RTU RSS feeds using SimplePie.
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../vendor/autoload.php';

$feed_sources = getenv('RTU_RSS_FEEDS') ?: 'https://www.rtu.edu.ph/feed/,https://www.rtu.edu.ph/category/announcement/feed/';
$feed_urls    = explode(',', $feed_sources);
$feed_pages   = (int)(getenv('RTU_FEED_PAGES') ?: 3);
$cache_ttl    = (int)(getenv('RTU_CACHE_TTL') ?: 900);

/**
 * Fetch raw RSS XML via cURL with browser-like headers.
 * RTU returns HTTP 415 but sends valid RSS body anyway — we accept it.
 */
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

    // Strip HTTP response headers, pass only XML body to SimplePie
    return substr($response, $header_size);
}

$all_items = [];

foreach ($feed_urls as $base_url) {
    for ($page = 1; $page <= $feed_pages; $page++) {
        $feed_url = $page === 1 ? trim($base_url) : trim($base_url) . '?paged=' . $page;

        $raw = fetch_rss_raw($feed_url);
        if ($raw === false) break;

        $feed = new SimplePie\SimplePie();
        $feed->set_raw_data($raw);
        $feed->enable_cache(false);
        $feed->force_feed(true);
        $feed->init();

        if ($feed->error()) {
            error_log("SimplePie parse error for $feed_url: " . $feed->error());
            break;
        }

        $page_items = $feed->get_items();
        if (empty($page_items)) break;

        foreach ($page_items as $item) {
            $thumbnail = '';
            $enclosure = $item->get_enclosure();
            if ($enclosure && str_starts_with($enclosure->get_type() ?? '', 'image/')) {
                $thumbnail = $enclosure->get_link() ?? '';
            }
            if (!$thumbnail) {
                $thumb     = $item->get_thumbnail();
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
}

// DEDUPLICATION & SORTING
$seen   = [];
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