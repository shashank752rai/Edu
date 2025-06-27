
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$class = $_GET['class'] ?? '';
$subject = $_GET['subject'] ?? '';
$type = $_GET['type'] ?? 'all'; // notes, videos, worksheets

if (empty($class) || empty($subject)) {
    echo json_encode(['error' => 'Class and subject are required']);
    exit();
}

// Map the frontend subject format back to database format
$subjectMap = [
    'mathematics' => 'Mathematics',
    'science' => 'Science', 
    'english' => 'English',
    'hindi' => 'Hindi',
    'social-science' => 'Social Science',
    'physics' => 'Physics',
    'chemistry' => 'Chemistry',
    'biology' => 'Biology',
    'economics' => 'Economics',
    'business-studies' => 'Business Studies'
];

$dbSubject = $subjectMap[$subject] ?? ucfirst(str_replace('-', ' ', $subject));
$classCategory = "Class " . $class;

$query = "SELECT * FROM materials WHERE category = ? AND (title LIKE ? OR title LIKE ?) ORDER BY uploaded_at DESC";
$stmt = $db->prepare($query);
$subjectPattern = "%{$dbSubject}%";
$stmt->execute([$classCategory, $subjectPattern, "%{$subject}%"]);

$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Categorize materials by type
$result = [
    'notes' => [],
    'videos' => [],
    'worksheets' => []
];

foreach ($materials as $material) {
    $title = strtolower($material['title']);
    $fileType = strtolower($material['file_type']);
    
    // Determine category based on title keywords and file type
    if (strpos($title, 'worksheet') !== false || strpos($title, 'practice') !== false || strpos($title, 'exercise') !== false) {
        $category = 'worksheets';
    } elseif ($fileType === 'mp4' || $fileType === 'avi' || strpos($title, 'video') !== false || strpos($title, 'lecture') !== false) {
        $category = 'videos';
    } else {
        $category = 'notes';
    }
    
    $item = [
        'id' => $material['id'],
        'title' => $material['title'],
        'description' => "Study material for {$dbSubject}",
        'file' => '../' . $material['file_path'],
        'file_type' => $material['file_type'],
        'uploaded_at' => $material['uploaded_at']
    ];
    
    // Add video-specific fields
    if ($category === 'videos') {
        $item['duration'] = '00:00'; // You can extend this later
        $item['videoId'] = 'dQw4w9WgXcQ'; // Default for now
        $item['thumbnail'] = 'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg';
    }
    
    $result[$category][] = $item;
}

if ($type !== 'all') {
    echo json_encode($result[$type] ?? []);
} else {
    echo json_encode($result);
}
?>
