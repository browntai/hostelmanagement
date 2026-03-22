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

<?php 
    $page_title = "Photo Gallery — HostelHub";
    include('includes/public-header.php'); 
?>

<body class="pub-page">

    <?php include('includes/public-nav.php'); ?>

    <!-- ── Hero ── -->
    <section class="pub-hero pub-hero-small" style="background: linear-gradient(135deg, rgba(13, 20, 50, 0.8) 0%, rgba(13, 20, 50, 0.4) 100%), url('assets/images/heroes/gallery_hero.png') center/cover no-repeat;">
        <div class="container">
            <div class="pub-hero-content">
                <h1><i class="fas fa-images" style="margin-right:12px; opacity:0.7;"></i>Photo Gallery</h1>
                <p>Explore stunning photos from all our verified hostels. Like your favorites and discover amazing places to stay.</p>
                <div class="pub-stats" style="margin-top:24px; display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                    <div class="pub-stats-item">
                        <span class="stat-number"><?php echo $total_images; ?></span>
                        <span class="stat-label">Photos</span>
                    </div>
                    <?php if($total_images > 0): ?>
                    <button class="btn-pub-solid slideshow-trigger" onclick="startSlideshow()">
                        <i class="fas fa-play" style="margin-right:8px;"></i> Play Slideshow
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Gallery Grid ── -->
    <div class="container pub-section">

        <?php if($total_images > 0): ?>
        <div class="gallery-grid" id="galleryGrid">
            <?php foreach($images as $index => $img): ?>
            <div class="gallery-card" data-index="<?php echo $index; ?>" onclick="openLightboxByIndex(<?php echo $index; ?>)">
                <div class="gallery-card-img">
                    <img src="<?php echo htmlentities($img->image_path); ?>" 
                         alt="<?php echo htmlentities($img->hostel_name); ?>"
                         loading="lazy"
                         onerror="this.src='assets/images/hostel-placeholder.jpg'">
                </div>
                <div class="gallery-card-overlay">
                    <div class="gallery-card-info">
                        <h5><?php echo htmlentities($img->hostel_name); ?></h5>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlentities($img->hostel_city); ?></p>
                    </div>
                    <div class="gallery-card-actions" onclick="event.stopPropagation()">
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
        <button class="lightbox-close" onclick="closeLightbox(event)" title="Close (Esc)"><i class="fas fa-times"></i></button>
        
        <button class="lightbox-nav lightbox-prev" onclick="prevImage(event)" title="Previous"><i class="fas fa-chevron-left"></i></button>
        <button class="lightbox-nav lightbox-next" onclick="nextImage(event)" title="Next"><i class="fas fa-chevron-right"></i></button>

        <div class="lightbox-content">
            <img id="lightboxImg" src="" alt="">
            <div class="lightbox-info">
                <p class="lightbox-caption" id="lightboxCaption"></p>
                <div class="lightbox-meta">
                    <span id="lightboxCounter">1 of 1</span>
                    <button id="slideshowToggle" onclick="toggleSlideshow(event)" title="Play/Pause Slideshow">
                        <i class="fas fa-pause"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php include('includes/public-footer.php'); ?>

    <script>
        // Gallery Data for Slideshow
        const galleryImages = <?php echo json_encode($images); ?>;
        let currentIndex = 0;
        let slideshowInterval = null;
        const slideshowDelay = 4000;

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

        // --- Lightbox & Slideshow Logic ---

        function openLightbox(src, caption) {
            // Stop any running slideshow
            pauseSlideshow();
            
            // Find current index based on src
            // Extract filename to be safer with paths
            const getFilename = (path) => path.split('/').pop().split('\\').pop();
            const targetFile = getFilename(src);
            
            currentIndex = galleryImages.findIndex(img => getFilename(img.image_path) === targetFile);
            if (currentIndex === -1) currentIndex = 0;

            updateLightboxUI();
            document.getElementById('galleryLightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function openLightboxByIndex(index) {
            if (index < 0) index = galleryImages.length - 1;
            if (index >= galleryImages.length) index = 0;
            currentIndex = index;
            updateLightboxUI();
        }

        function updateLightboxUI() {
            const img = galleryImages[currentIndex];
            const lbImg = document.getElementById('lightboxImg');
            
            // Add fade effect
            lbImg.style.opacity = '0';
            setTimeout(() => {
                lbImg.src = img.image_path;
                document.getElementById('lightboxCaption').textContent = img.hostel_name;
                document.getElementById('lightboxCounter').textContent = (currentIndex + 1) + ' of ' + galleryImages.length;
                lbImg.style.opacity = '1';
            }, 50);
        }

        function nextImage(e) {
            if (e) e.stopPropagation();
            openLightboxByIndex(currentIndex + 1);
        }

        function prevImage(e) {
            if (e) e.stopPropagation();
            openLightboxByIndex(currentIndex - 1);
        }

        function startSlideshow() {
            currentIndex = 0;
            updateLightboxUI();
            document.getElementById('galleryLightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
            playSlideshow();
        }

        function playSlideshow() {
            if (slideshowInterval) clearInterval(slideshowInterval);
            document.getElementById('slideshowToggle').innerHTML = '<i class="fas fa-pause"></i>';
            slideshowInterval = setInterval(() => {
                nextImage();
            }, slideshowDelay);
        }

        function pauseSlideshow() {
            if (slideshowInterval) {
                clearInterval(slideshowInterval);
                slideshowInterval = null;
                document.getElementById('slideshowToggle').innerHTML = '<i class="fas fa-play"></i>';
            }
        }

        function toggleSlideshow(e) {
            if (e) e.stopPropagation();
            if (slideshowInterval) {
                pauseSlideshow();
            } else {
                playSlideshow();
            }
        }

        function closeLightbox(e) {
            if (e === 'force' || e.target === document.getElementById('galleryLightbox') || 
                e.target.closest('.lightbox-close')) {
                pauseSlideshow();
                document.getElementById('galleryLightbox').classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLightbox('force');
            } else if (e.key === 'ArrowRight') {
                nextImage();
            } else if (e.key === 'ArrowLeft') {
                prevImage();
            } else if (e.key === ' ') { // Spacebar to play/pause
                e.preventDefault();
                toggleSlideshow();
            }
        });
    </script>
</body>

</html>
