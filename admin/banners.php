
<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle banner upload
if ($_POST && isset($_FILES['banner_image'])) {
    $title = $_POST['title'];
    $duration = $_POST['duration'];
    $file = $_FILES['banner_image'];
    
    // Validate file
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $max_size = 10 * 1024 * 1024; // 10MB
    
    if (!in_array($file_ext, $allowed_types)) {
        $error = "File type not allowed. Allowed types: " . implode(', ', $allowed_types);
    } elseif ($file['size'] > $max_size) {
        $error = "File size too large. Maximum 10MB allowed.";
    } else {
        // Create upload directory if it doesn't exist
        $upload_dir = '../images/banners/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $file_path = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Get next display order
            $orderQuery = "SELECT MAX(display_order) + 1 as next_order FROM banners";
            $orderStmt = $db->prepare($orderQuery);
            $orderStmt->execute();
            $nextOrder = $orderStmt->fetchColumn() ?: 1;
            
            // Save to database
            $query = "INSERT INTO banners (title, image_path, duration, display_order) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            if ($stmt->execute([$title, 'images/banners/' . $filename, $duration, $nextOrder])) {
                $message = "Banner uploaded successfully!";
            } else {
                $error = "Database error occurred.";
            }
        } else {
            $error = "File upload failed.";
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get file path first
    $query = "SELECT image_path FROM banners WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $banner = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($banner) {
        // Delete from database
        $query = "DELETE FROM banners WHERE id = ?";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$id])) {
            // Delete file
            if (file_exists('../' . $banner['image_path'])) {
                unlink('../' . $banner['image_path']);
            }
            $message = "Banner deleted successfully!";
        }
    }
}

// Handle toggle active status
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $query = "UPDATE banners SET is_active = NOT is_active WHERE id = ?";
    $stmt = $db->prepare($query);
    if ($stmt->execute([$id])) {
        $message = "Banner status updated successfully!";
    }
}

// Get all banners
$query = "SELECT * FROM banners ORDER BY display_order ASC, created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banner Management - Admin</title>
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
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="admin-form">
                    <h2>Upload New Banner</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="title">Banner Title:</label>
                                <input type="text" id="title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="duration">Display Duration (seconds):</label>
                                <input type="number" id="duration" name="duration" value="5" min="1" max="60" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="banner_image">Select Banner Image:</label>
                            <div class="file-upload-area" onclick="document.getElementById('banner_image').click()">
                                <i class="fas fa-image" style="font-size: 3rem; color: #3498db;"></i>
                                <p>Click to select image or drag and drop</p>
                                <p style="font-size: 0.9rem; color: #666;">Max size: 10MB | Allowed: JPG, PNG, GIF</p>
                            </div>
                            <input type="file" id="banner_image" name="banner_image" accept="image/*" style="display: none;" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Banner
                        </button>
                    </form>
                </div>
                
                <div class="admin-table">
                    <h2>Existing Banners</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Preview</th>
                                <th>Title</th>
                                <th>Duration</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($banners as $banner): ?>
                            <tr>
                                <td>
                                    <img src="../<?php echo $banner['image_path']; ?>" 
                                         alt="<?php echo htmlspecialchars($banner['title']); ?>" 
                                         style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px;">
                                </td>
                                <td><?php echo htmlspecialchars($banner['title']); ?></td>
                                <td><?php echo $banner['duration']; ?>s</td>
                                <td><?php echo $banner['display_order']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $banner['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $banner['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($banner['created_at'])); ?></td>
                                <td>
                                    <a href="?toggle=<?php echo $banner['id']; ?>" 
                                       class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                        <i class="fas fa-toggle-<?php echo $banner['is_active'] ? 'on' : 'off'; ?>"></i>
                                    </a>
                                    <a href="?delete=<?php echo $banner['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this banner?')" 
                                       class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <style>
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-badge.active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-badge.inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</body>
</html>
