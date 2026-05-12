<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../includes/db.php';

header('Content-Type: application/json');

$api_token = getenv('CANVAS_API_TOKEN');
$base_url  = rtrim(getenv('CANVAS_BASE_URL') ?: 'https://canvas.instructure.com', '/');
$preview   = isset($_GET['preview']);

if (!$api_token) {
    http_response_code(500);
    echo json_encode(['error' => 'Canvas API token not configured.']);
    exit();
}

// 1. Dynamically identify the network hosts
$internal_canvas_host = parse_url($base_url, PHP_URL_HOST);
$frontend_host = $_SERVER['HTTP_HOST'] ?? 'localhost:3000'; // Auto-detects Render URL or localhost

try {
    // ── Fetch active courses JOIN with programs ──
    $stmt = $pdo->prepare("
        SELECT 
            cc.course_id, 
            cc.course_name,
            p.id         AS program_id,
            p.program_name,
            p.abbreviation
        FROM canvas_courses cc
        LEFT JOIN programs p ON cc.program_id = p.id
        WHERE cc.is_active = 1
        ORDER BY p.id ASC, cc.created_at ASC
    ");
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($courses)) {
        echo json_encode([]);
        exit();
    }

    $all_files = [];

    foreach ($courses as $course) {
        $course_id    = $course['course_id'];
        $course_name  = $course['course_name'];
        $program_id   = $course['program_id']   ?? null;
        $program_name = $course['program_name'] ?? 'General';
        $abbreviation = $course['abbreviation'] ?? 'GEN';

        $url = "{$base_url}/api/v1/courses/{$course_id}/files?per_page=50&sort=created_at&order=desc";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$api_token}",
                "Content-Type: application/json",
                // 2. Dynamically spoof the host to match the environment
                "Host: {$frontend_host}" 
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response   = curl_exec($ch);
        $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

       if ($curl_error) {
            echo json_encode(['error' => "cURL Error on Course {$course_id}: {$curl_error}"]);
            exit();
        }

        if ($http_code !== 200) {
            echo json_encode(['error' => "API Error on Course {$course_id} (HTTP {$http_code}): {$response}"]);
            exit();
        }

        $files = json_decode($response, true);
        if (!is_array($files)) continue;

        foreach ($files as $file) {
            if (!empty($file['hidden']) || !empty($file['locked'])) continue;

            // 3. Dynamically swap the internal Docker name for the live Frontend URL
            $raw_url = $file['url'] ?? null;
            $safe_url = $raw_url ? str_replace($internal_canvas_host, $frontend_host, $raw_url) : null;

            $all_files[] = [
                'id'           => $file['id'] ?? null,
                'title'        => $file['display_name'] ?? $file['filename'] ?? 'Untitled',
                'url'          => $safe_url,
                'type'         => getFileType($file['content-type'] ?? ''),
                'mime'         => $file['content-type'] ?? '',
                'size'         => formatFileSize($file['size'] ?? 0),
                'course_name'  => $course_name,
                'course_id'    => $course_id,
                'program_id'   => $program_id,
                'program_name' => $program_name,
                'abbreviation' => $abbreviation,
            ];
        }
    }

    if ($preview) {
        $all_files = array_slice($all_files, 0, 4);
    }

    echo json_encode($all_files);
} catch (PDOException $e) {
    error_log("TalaAral Canvas DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}

function getFileType(string $mime): string
{
    if (str_contains($mime, 'pdf'))                                      return 'pdf';
    if (str_contains($mime, 'word') || str_contains($mime, 'docx'))      return 'word';
    if (str_contains($mime, 'presentation') || str_contains($mime, 'powerpoint')) return 'ppt';
    if (str_contains($mime, 'spreadsheet') || str_contains($mime, 'excel'))       return 'excel';
    if (str_contains($mime, 'image'))                                    return 'image';
    if (str_contains($mime, 'video'))                                    return 'video';
    if (str_contains($mime, 'audio'))                                    return 'audio';
    if (str_contains($mime, 'zip') || str_contains($mime, 'compressed')) return 'zip';
    return 'file';
}

function formatFileSize(int $bytes): string
{
    if ($bytes === 0)        return '—';
    if ($bytes < 1024)       return $bytes . ' B';
    if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
    return round($bytes / 1073741824, 1) . ' GB';
}
?>