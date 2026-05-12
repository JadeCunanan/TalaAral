<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$api_token = getenv('CANVAS_API_TOKEN');
// Grab the dynamic Canvas URL from your .env
$canvas_base_url = getenv('CANVAS_BASE_URL'); 

$file_url  = $_GET['url']      ?? '';
$filename  = $_GET['filename'] ?? '';

if (empty($file_url)) {
    http_response_code(400);
    exit('Invalid file URL');
}

$max_redirects = 5;
$http_code = 0;
$response = '';
$header_size = 0;
$mime_type = 'application/octet-stream';
$file_data = '';
$final_headers = '';
$physical_url = '';
$target_url = $file_url;

// Dynamically determine what host the frontend asked for
$frontend_host = parse_url($file_url, PHP_URL_HOST);
$frontend_port = parse_url($file_url, PHP_URL_PORT);
$spoofed_host = $frontend_port ? $frontend_host . ':' . $frontend_port : ($frontend_host ?: 'localhost');

// Extract the actual internal network host from our .env variable
$internal_canvas_host = parse_url($canvas_base_url, PHP_URL_HOST);

for ($i = 0; $i < $max_redirects; $i++) {
    
    // PHYSICAL ROUTING: Dynamically translate local domains to the internal network host.
    // If deployed with a Cloudflare tunnel, it just uses the tunnel URL directly!
    $physical_url = str_replace(['localhost:3000', 'localhost', 'canvas.docker'], $internal_canvas_host, $target_url);

    $ch = curl_init($physical_url);
    
    $headers = [
        "Authorization: Bearer {$api_token}",
        "Host: localhost",                  // The "ID Badge" that tricks ngrok
        "ngrok-skip-browser-warning: true"   // Bypasses the annoying ngrok landing page
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => false, 
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    
    if ($http_code >= 300 && $http_code < 400) {
        $final_headers = substr($response, 0, $header_size);
        preg_match('/^Location:\s*([^\r\n]+)/mi', $final_headers, $matches);
        $redirect_url = trim($matches[1] ?? '');
        
        if (!$redirect_url) break;

        if (strpos($redirect_url, '/') === 0) {
            $redirect_url = 'http://' . $spoofed_host . $redirect_url;
        }

        $target_url = $redirect_url;
        
        // Update spoofed host for the next hop
        $parsed_redirect = parse_url($redirect_url);
        $new_host = $parsed_redirect['host'] ?? '';
        
        if ($new_host) {
            $port = isset($parsed_redirect['port']) ? ':' . $parsed_redirect['port'] : '';
            $spoofed_host = $new_host . $port;
        }

        curl_close($ch);
        continue;
    }
    
    if ($http_code === 200) {
        $final_headers = substr($response, 0, $header_size);
        $mime_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $file_data = substr($response, $header_size);
        curl_close($ch);
        break;
    }
    
    $file_data = substr($response, $header_size);
    curl_close($ch);
    break;
}

if ($http_code !== 200 || empty($file_data)) {
    http_response_code(404);
    echo "<h3>System Diagnostic Error</h3>";
    echo "<b>HTTP Code:</b> {$http_code}<br>";
    echo "<b>Final URL Attempted:</b> " . htmlspecialchars($physical_url) . "<br>";
    echo "<b>Final Spoofed Host:</b> " . htmlspecialchars($spoofed_host) . "<br><br>";
    echo "<b>Raw Headers:</b><br><pre>" . htmlspecialchars(substr($response, 0, $header_size)) . "</pre>";
    exit();
}

$mime_type = explode(';', $mime_type)[0];

if (empty($filename)) {
    if (preg_match('/filename="?([^"]+)"?/i', $final_headers, $name_matches)) {
        $filename = $name_matches[1];
    } else {
        $filename = basename(parse_url($target_url, PHP_URL_PATH));
    }
}
$filename = preg_replace('/[^a-zA-Z0-9._\- ]/', '_', $filename);

header("Content-Type: {$mime_type}");
header("Content-Length: " . strlen($file_data));
header("Cache-Control: private, max-age=3600");

$inline_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
if (in_array($mime_type, $inline_types)) {
    header("Content-Disposition: inline; filename=\"{$filename}\"");
} else {
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
}

echo $file_data;
exit();
?>