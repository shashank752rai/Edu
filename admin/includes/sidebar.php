<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h3>Educare Admin</h3>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a></li>
            <li>
                    <a href="materials.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'materials.php' ? 'active' : ''; ?>">
                        <i class="fas fa-file-alt"></i>
                        <span>Student Materials</span>
                    </a>
                </li>
                <li>
                    <a href="banners.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'banners.php' ? 'active' : ''; ?>">
                        <i class="fas fa-images"></i>
                        <span>Banners</span>
                    </a>
                </li>
            <li><a href="gallery.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : ''; ?>">
                <i class="fas fa-camera"></i> Image Gallery
            </a></li>
            <li><a href="announcements.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : ''; ?>">
                <i class="fas fa-bullhorn"></i> Announcements
            </a></li>
            <li><a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a></li>
        </ul>
    </nav>
</aside>