<?php
/**
 * get_rtu_updates.php
 * Fetches RTU RSS feeds using SimplePie.
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../vendor/autoload.php';

$feed_sources = getenv('RTU_RSS_FEEDS') ?: 'https://www.rtu.edu.ph/feed/,https://www.rtu.edu.ph/category/announcement/feed/,https://www.rtu.edu.ph/university-news/feed/';
$feed_urls    = explode(',', $feed_sources);
$feed_pages   = (int)(getenv('RTU_FEED_PAGES') ?: 3);
$cache_ttl    = (int)(getenv('RTU_CACHE_TTL') ?: 900);

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

// Serve from cache if still fresh
$cache_file = sys_get_temp_dir() . '/talaaral_rtu_cache.json';
if ($cache_ttl > 0 && file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_ttl) {
    $cached = json_decode(file_get_contents($cache_file), true);
    if (!empty($cached)) {
        $limit = isset($_GET['preview']) ? 4 : 50;
        echo json_encode(array_slice($cached, 0, $limit));
        exit;
    }
}

$all_items = [];
$seen      = [];

foreach ($feed_urls as $base_url) {
    $is_announcement_feed = str_contains(trim($base_url), 'announcement');

    for ($page = 1; $page <= $feed_pages; $page++) {
        $feed_url = $page === 1
            ? trim($base_url)
            : rtrim(trim($base_url), '/') . '?paged=' . $page;

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

        $page_urls = [];

        foreach ($page_items as $item) {
            $title   = trim($item->get_title() ?? '');
            $url     = trim($item->get_permalink() ?? '');
            $excerpt = mb_strimwidth(strip_tags($item->get_description() ?? ''), 0, 160, '…');

            // Only skip if truly no title or URL
            if (empty($title) || empty($url)) continue;

            $page_urls[] = $url;

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

            // Determine category from feed URL or item categories
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

            $all_items[] = [
                'title'     => $title,
                'url'       => $url,
                'date'      => $item->get_date('M j, Y') ?? '',
                'timestamp' => $item->get_date('U') ?? 0,
                'category'  => $is_announcement ? 'announcement' : 'news',
                'excerpt'   => $excerpt,
                'thumbnail' => $thumbnail,
                'content'   => $thumbnail ? strip_thumbnail_from_content($content_raw, $thumbnail) : $content_raw,
            ];
        }

        // If RTU ignored ?paged and returned same URLs, stop paginating
        if ($page > 1 && !empty($page_urls)) {
            $already_seen = array_filter($page_urls, fn($u) => isset($seen[$u]));
            if (count($already_seen) === count($page_urls)) break;
        }

        foreach ($page_urls as $u) {
            $seen[$u] = true;
        }
    }
}

// Deduplicate & sort
$unique = [];
foreach ($all_items as $item) {
    if (!empty($item['url']) && !isset($unique[$item['url']])) {
        $unique[$item['url']] = $item;
    }
}
$unique = array_values($unique);
usort($unique, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
$unique = array_slice($unique, 0, 50);

// Write to cache
if ($cache_ttl > 0 && !empty($unique)) {
    file_put_contents($cache_file, json_encode($unique));
}

// Return 4 for dashboard preview, 50 for full page
$limit = isset($_GET['preview']) ? 4 : 50;
echo json_encode(array_slice($unique, 0, $limit));