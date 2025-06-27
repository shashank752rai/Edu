
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

// Handle file upload
if ($_POST && isset($_FILES['material_file'])) {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $subject = $_POST['subject'];
    $file = $_FILES['material_file'];
    
    // Combine subject into title for better categorization
    $fullTitle = $subject . ' - ' . $title;
    
    // Validate file
    $allowed_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'mp4', 'avi'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $max_size = 50 * 1024 * 1024; // 50MB
    
    if (!in_array($file_ext, $allowed_types)) {
        $error = "File type not allowed. Allowed types: " . implode(', ', $allowed_types);
    } elseif ($file['size'] > $max_size) {
        $error = "File size too large. Maximum 50MB allowed.";
    } else {
        // Create upload directory if it doesn't exist
        $upload_dir = '../uploads/materials/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $file_path = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Save to database
            $query = "INSERT INTO materials (title, category, file_name, file_path, file_type) VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            if ($stmt->execute([$fullTitle, $category, $file['name'], $file_path, $file_ext])) {
                $message = "Material uploaded successfully!";
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
    $query = "SELECT file_path FROM materials WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $material = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($material) {
        // Delete from database
        $query = "DELETE FROM materials WHERE id = ?";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$id])) {
            // Delete file
            if (file_exists($material['file_path'])) {
                unlink($material['file_path']);
            }
            $message = "Material deleted successfully!";
        }
    }
}

// Get all materials
$query = "SELECT * FROM materials ORDER BY uploaded_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materials Management - Admin</title>
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
                    <h2>Upload New Material</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="title">Title:</label>
                                <input type="text" id="title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="category">Class:</label>
                                <select id="category" name="category" required>
                                    <option value="">Select Class</option>
                                    <option value="Class 6">Class 6</option>
                                    <option value="Class 7">Class 7</option>
                                    <option value="Class 8">Class 8</option>
                                    <option value="Class 9">Class 9</option>
                                    <option value="Class 10">Class 10</option>
                                    <option value="Class 11">Class 11</option>
                                    <option value="Class 12">Class 12</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="subject">Subject:</label>
                                <select id="subject" name="subject" required>
                                    <option value="">Select Subject</option>
                                    <option value="Mathematics">Mathematics</option>
                                    <option value="Science">Science</option>
                                    <option value="Physics">Physics</option>
                                    <option value="Chemistry">Chemistry</option>
                                    <option value="Biology">Biology</option>
                                    <option value="English">English</option>
                                    <option value="Hindi">Hindi</option>
                                    <option value="Social Science">Social Science</option>
                                    <option value="Economics">Economics</option>
                                    <option value="Business Studies">Business Studies</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="material_file">Select File:</label>
                            <div class="file-upload-area" onclick="document.getElementById('material_file').click()">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #3498db;"></i>
                                <p>Click to select file or drag and drop</p>
                                <p style="font-size: 0.9rem; color: #666;">Max size: 50MB | Allowed: PDF, DOC, PPT, Images, Videos</p>
                            </div>
                            <input type="file" id="material_file" name="material_file" style="display: none;" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Material
                        </button>
                    </form>
                </div>
                
                <div class="admin-table">
                    <h2>Uploaded Materials</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>File Type</th>
                                <th>Uploaded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materials as $material): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($material['title']); ?></td>
                                <td><?php echo htmlspecialchars($material['category']); ?></td>
                                <td><?php echo strtoupper($material['file_type']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($material['uploaded_at'])); ?></td>
                                <td>
                                    <a href="<?php echo $material['file_path']; ?>" target="_blank" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?delete=<?php echo $material['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this material?')" 
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
    
    <script>
        // File upload drag and drop
        const fileUploadArea = document.querySelector('.file-upload-area');
        const fileInput = document.getElementById('material_file');
        
        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });
        
        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });
        
        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                updateFileInfo(files[0]);
            }
        });
        
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                updateFileInfo(e.target.files[0]);
            }
        });
        
        function updateFileInfo(file) {
            const fileInfo = document.querySelector('.file-upload-area p');
            fileInfo.textContent = `Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        }
    </script>
</body>
</html>
