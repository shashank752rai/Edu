
<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'educare_institute';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Create database tables if they don't exist
function createTables() {
    $database = new Database();
    $db = $database->getConnection();
    
    // Admin users table
    $query = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($query);
    
    // Insert default admin if not exists
    $check = $db->prepare("SELECT COUNT(*) FROM admin_users WHERE email = ?");
    $check->execute(['admin@educare.com']);
    if ($check->fetchColumn() == 0) {
        $stmt = $db->prepare("INSERT INTO admin_users (email, password) VALUES (?, ?)");
        $stmt->execute(['admin@educare.com', password_hash('admin123', PASSWORD_DEFAULT)]);
    }
    
    // Materials table
    $query = "CREATE TABLE IF NOT EXISTS materials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(100) NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_type VARCHAR(50) NOT NULL,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($query);
    
    // Banners table
    $query = "CREATE TABLE IF NOT EXISTS banners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        image_path VARCHAR(500) NOT NULL,
        duration INT DEFAULT 5,
        display_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($query);
    
    // Gallery images table
    $query = "CREATE TABLE IF NOT EXISTS gallery_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        caption TEXT,
        image_path VARCHAR(500) NOT NULL,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($query);
    
    // Announcements table
    $query = "CREATE TABLE IF NOT EXISTS announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        link_url VARCHAR(500),
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($query);
}

createTables();

// Create default admin user if it doesn't exist
function createDefaultAdmin($db) {
    $checkQuery = "SELECT COUNT(*) FROM admin_users WHERE email = 'admin@educare.com'";
    $stmt = $db->prepare($checkQuery);
    $stmt->execute();
    
    if ($stmt->fetchColumn() == 0) {
        $insertQuery = "INSERT INTO admin_users (email, password, created_at) VALUES (?, ?, NOW())";
        $stmt = $db->prepare($insertQuery);
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt->execute(['admin@educare.com', $hashedPassword]);
    }
}

$database = new Database();
$db = $database->getConnection();
createDefaultAdmin($db);
?>
