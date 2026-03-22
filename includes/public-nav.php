<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- ── Navigation ── -->
<nav class="pub-navbar" id="pubNav">
    <div class="container">
        <a class="pub-nav-brand" href="index.php">
            <img src="assets/images/big/icon.png" alt="HostelHub">
            <span>HostelHub</span>
        </a>

        <button class="pub-nav-toggle" onclick="document.getElementById('navLinks').classList.toggle('show')">
            <i class="fas fa-bars"></i>
        </button>

        <ul class="pub-nav-links" id="navLinks">
            <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="hostels.php" class="<?php echo $current_page == 'hostels.php' ? 'active' : ''; ?>"><i class="fas fa-building"></i> Hostels</a></li>
            <li><a href="gallery.php" class="<?php echo $current_page == 'gallery.php' ? 'active' : ''; ?>"><i class="fas fa-images"></i> Gallery</a></li>
            <li><a href="owners.php" class="<?php echo $current_page == 'owners.php' ? 'active' : ''; ?>"><i class="fas fa-user-tie"></i> For Owners</a></li>
            <li><a href="about.php" class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>"><i class="fas fa-info-circle"></i> About</a></li>
            <li><a href="contact.php" class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>"><i class="fas fa-envelope"></i> Contact</a></li>
        </ul>

        <div class="pub-nav-actions">
            <?php if(isset($_SESSION['login'])): ?>
                <a href="client/dashboard.php" class="btn-pub-solid"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="btn-pub-outline">Login</a>
                <a href="client-registration.php" class="btn-pub-solid">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
