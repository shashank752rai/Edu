
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM banners WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();

$banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($banners);
?>
