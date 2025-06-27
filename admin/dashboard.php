
<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [
    'materials' => $db->query("SELECT COUNT(*) FROM materials")->fetchColumn(),
    'banners' => $db->query("SELECT COUNT(*) FROM banners WHERE is_active = 1")->fetchColumn(),
    'gallery_images' => $db->query("SELECT COUNT(*) FROM gallery_images")->fetchColumn(),
    'announcements' => $db->query("SELECT COUNT(*) FROM announcements WHERE is_active = 1")->fetchColumn()
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Educare Institute</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="admin-content">
                <h1>Dashboard</h1>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['materials']; ?></h3>
                            <p>Study Materials</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-images"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['banners']; ?></h3>
                            <p>Active Banners</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['gallery_images']; ?></h3>
                            <p>Gallery Images</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['announcements']; ?></h3>
                            <p>Active Announcements</p>
                        </div>
                    </div>
                </div>
                
                <div class="recent-activity">
                    <h2>Quick Actions</h2>
                    <div class="action-buttons">
                        <a href="materials.php" class="action-btn">
                            <i class="fas fa-upload"></i>
                            Upload Material
                        </a>
                        <a href="banners.php" class="action-btn">
                            <i class="fas fa-plus"></i>
                            Add Banner
                        </a>
                        <a href="gallery.php" class="action-btn">
                            <i class="fas fa-camera-retro"></i>
                            Add Gallery Images
                        </a>
                        <a href="announcements.php" class="action-btn">
                            <i class="fas fa-megaphone"></i>
                            Create Announcement
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
