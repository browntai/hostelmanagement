<?php
    session_start();
    include('includes/dbconn.php');
    include('includes/hostel-helper.php');

    $sess_id = session_id();

    // Get all images from approved hostels with like counts, ordered by likes DESC
    $query = "SELECT hi.*, h.name as hostel_name, h.city as hostel_city, h.id as hostel_id,
              (SELECT COUNT(*) FROM image_likes il WHERE il.image_id = hi.id) as like_count,
              (SELECT COUNT(*) FROM image_likes il WHERE il.image_id = hi.id AND il.session_id = ?) as user_liked
              FROM hostel_images hi
              JOIN hostels h ON hi.hostel_id = h.id
              WHERE h.status = 'approved'
              ORDER BY like_count DESC, hi.uploaded_at DESC";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $sess_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $images = [];
    while ($row = $result->fetch_object()) {
        $images[] = $row;
    }

    $total_images = count($images);
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Browse the photo gallery of all hostel properties. Like your favorite images and discover amazing accommodations.">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title>Photo Gallery — HostelHub</title>
    <link href="dist/css/style.min.css" rel="stylesheet">
    <link href="assets/css/public-pages.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="pub-page">

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
                <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="gallery.php" class="active"><i class="fas fa-images"></i> Gallery</a></li>
                <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
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

    <!-- ── Hero ── -->
    <section class="pub-hero pub-hero-small" style="background: linear-gradient(135deg, rgba(13, 20, 50, 0.8) 0%, rgba(13, 20, 50, 0.4) 100%), url('assets/images/heroes/gallery_hero.png') center/cover no-repeat;">
        <div class="container">
            <div class="pub-hero-content">
                <h1><i class="fas fa-images" style="margin-right:12px; opacity:0.7;"></i>Photo Gallery</h1>
                <p>Explore stunning photos from all our verified hostels. Like your favorites and discover amazing places to stay.</p>
                <div class="pub-stats" style="margin-top:24px;">
                    <div class="pub-stats-item">
                        <span class="stat-number"><?php echo $total_images; ?></span>
                        <span class="stat-label">Photos</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Gallery Grid ── -->
    <div class="container pub-section">

        <?php if($total_images > 0): ?>
        <div class="gallery-grid" id="galleryGrid">
            <?php foreach($images as $img): ?>
            <div class="gallery-card" data-image-id="<?php echo $img->id; ?>">
                <div class="gallery-card-img">
                    <img src="<?php echo htmlentities($img->image_path); ?>" 
                         alt="<?php echo htmlentities($img->hostel_name); ?>"
                         loading="lazy"
                         onerror="this.src='assets/images/hostel-placeholder.jpg'"
                         onclick="openLightbox(this.src, '<?php echo htmlentities(addslashes($img->hostel_name)); ?>')">
                </div>
                <div class="gallery-card-overlay">
                    <div class="gallery-card-info">
                        <h5><?php echo htmlentities($img->hostel_name); ?></h5>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlentities($img->hostel_city); ?></p>
                    </div>
                    <div class="gallery-card-actions">
                        <a href="hostel-details.php?id=<?php echo $img->hostel_id; ?>" class="gallery-btn gallery-btn-view" title="View Hostel">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <button class="gallery-btn gallery-btn-like <?php echo $img->user_liked ? 'liked' : ''; ?>" 
                                onclick="toggleLike(<?php echo $img->id; ?>, this)" 
                                title="Like this photo">
                            <i class="<?php echo $img->user_liked ? 'fas' : 'far'; ?> fa-heart"></i>
                            <span class="like-count"><?php echo $img->like_count; ?></span>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="pub-no-results">
            <i class="fas fa-images" style="font-size:3rem; color:var(--pub-soft); margin-bottom:16px;"></i>
            <h4>No photos yet</h4>
            <p>Photos will appear here once property owners upload images of their hostels.</p>
            <a href="index.php" class="btn-pub-solid" style="margin-top:16px; padding:12px 28px; border-radius:10px;">
                <i class="fas fa-home"></i> Browse Properties
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Lightbox ── -->
    <div class="gallery-lightbox" id="galleryLightbox" onclick="closeLightbox(event)">
        <button class="lightbox-close" onclick="closeLightbox(event)"><i class="fas fa-times"></i></button>
        <div class="lightbox-content">
            <img id="lightboxImg" src="" alt="">
            <p class="lightbox-caption" id="lightboxCaption"></p>
        </div>
    </div>

    <!-- ── Footer ── -->
    <footer class="pub-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="pub-footer-brand">
                        <img src="assets/images/big/icon.png" alt="HostelHub">
                        <span>HostelHub</span>
                    </div>
                    <p class="pub-footer-desc">
                        Your trusted platform for finding quality hostel accommodations. We connect tenants with verified property owners across Kenya.
                    </p>
                    <div class="pub-footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="pub-footer-links">
                        <li><a href="index.php">Browse Properties</a></li>
                        <li><a href="gallery.php">Photo Gallery</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="login.php">Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>For Property Owners</h5>
                    <ul class="pub-footer-links">
                        <li><a href="admin/index.php">Landlord Login</a></li>
                        <li><a href="client-registration.php">Register Account</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Legal</h5>
                    <ul class="pub-footer-links">
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="contact.php">Support</a></li>
                    </ul>
                </div>
            </div>
            <div class="pub-footer-bottom">
                &copy; <?php echo date('Y'); ?> HostelHub. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const nav = document.getElementById('pubNav');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });

        // Toggle Like
        function toggleLike(imageId, btn) {
            const icon = btn.querySelector('i');
            const countEl = btn.querySelector('.like-count');

            // Optimistic UI
            const wasLiked = btn.classList.contains('liked');
            btn.classList.toggle('liked');
            icon.className = wasLiked ? 'far fa-heart' : 'fas fa-heart';
            let currentCount = parseInt(countEl.textContent) || 0;
            countEl.textContent = wasLiked ? Math.max(0, currentCount - 1) : currentCount + 1;

            // Add pop animation
            btn.classList.add('like-pop');
            setTimeout(() => btn.classList.remove('like-pop'), 400);

            // AJAX call
            fetch('api/gallery-like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'image_id=' + imageId
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    // Revert on error
                    btn.classList.toggle('liked');
                    icon.className = wasLiked ? 'fas fa-heart' : 'far fa-heart';
                    countEl.textContent = currentCount;
                } else {
                    countEl.textContent = data.count;
                    btn.classList.toggle('liked', data.liked);
                    icon.className = data.liked ? 'fas fa-heart' : 'far fa-heart';
                }
            })
            .catch(() => {
                // Revert on network error
                btn.classList.toggle('liked');
                icon.className = wasLiked ? 'fas fa-heart' : 'far fa-heart';
                countEl.textContent = currentCount;
            });
        }

        // Lightbox
        function openLightbox(src, caption) {
            document.getElementById('lightboxImg').src = src;
            document.getElementById('lightboxCaption').textContent = caption;
            document.getElementById('galleryLightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox(e) {
            if (e.target === document.getElementById('galleryLightbox') || 
                e.target.closest('.lightbox-close')) {
                document.getElementById('galleryLightbox').classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('galleryLightbox').classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    </script>
</body>

</html>
