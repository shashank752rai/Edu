
<header class="admin-header">
    <div class="header-content">
        <h2><?php echo ucfirst(str_replace('.php', '', basename($_SERVER['PHP_SELF']))); ?></h2>
        <div class="header-actions">
            <span>Welcome, <?php echo $_SESSION['admin_email']; ?></span>
            <a href="../index.html" target="_blank" class="btn btn-outline">View Site</a>
        </div>
    </div>
</header>
