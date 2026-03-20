<?php session_start(); ?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Learn about HostelHub — Kenya's trusted hostel booking platform connecting tenants with verified property owners.">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title>About Us — HostelHub</title>
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
                <li><a href="gallery.php"><i class="fas fa-images"></i> Gallery</a></li>
                <li><a href="about.php" class="active"><i class="fas fa-info-circle"></i> About</a></li>
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
    <section class="pub-hero pub-hero-small" style="background: linear-gradient(135deg, rgba(13, 20, 50, 0.8) 0%, rgba(13, 20, 50, 0.4) 100%), url('assets/images/heroes/about_hero.png') center/cover no-repeat;">
        <div class="container">
            <div class="pub-hero-content">
                <h1>About HostelHub</h1>
                <p>Connecting tenants with quality, verified hostel accommodations since day one</p>
            </div>
        </div>
    </section>

    <!-- ── Mission ── -->
    <section class="pub-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="pub-section-title" style="text-align:left; margin-bottom:20px;">
                        <div class="title-accent"></div>
                        <h2>Our Mission</h2>
                    </div>
                    <p style="color:#4a5568; line-height:1.8; font-size:1rem;">
                        At HostelHub, we believe everyone deserves a safe, comfortable, and affordable place to live. Our platform bridges the gap between property owners and tenants by providing a transparent, easy-to-use marketplace for hostel accommodations.
                    </p>
                    <p style="color:#4a5568; line-height:1.8; font-size:1rem;">
                        We rigorously verify every listed property to ensure quality standards are met, giving tenants peace of mind and property owners a trusted channel to reach their ideal clients.
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="pub-card text-center" style="padding:22px;">
                                <div style="font-size:2rem; font-weight:800; color:var(--pub-blue);">100%</div>
                                <p style="margin:4px 0 0; font-size:0.85rem;">Verified Listings</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="pub-card text-center" style="padding:22px;">
                                <div style="font-size:2rem; font-weight:800; color:var(--pub-blue);">24/7</div>
                                <p style="margin:4px 0 0; font-size:0.85rem;">Customer Support</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="pub-card text-center" style="padding:22px;">
                                <div style="font-size:2rem; font-weight:800; color:var(--pub-blue);">Fast</div>
                                <p style="margin:4px 0 0; font-size:0.85rem;">Instant Booking</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="pub-card text-center" style="padding:22px;">
                                <div style="font-size:2rem; font-weight:800; color:var(--pub-blue);">Safe</div>
                                <p style="margin:4px 0 0; font-size:0.85rem;">Secure Payments</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── How It Works ── -->
    <section class="pub-section pub-section-alt">
        <div class="container">
            <div class="pub-section-title">
                <div class="title-accent"></div>
                <h2>How It Works</h2>
                <p>Three simple steps to your new home</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="pub-card text-center">
                        <div class="pub-card-icon mx-auto"><i class="fas fa-search"></i></div>
                        <h5>1. Browse & Discover</h5>
                        <p>Search through our curated collection of verified hostels. Filter by city, price, room type, and amenities to find the perfect match.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="pub-card text-center">
                        <div class="pub-card-icon mx-auto"><i class="fas fa-calendar-check"></i></div>
                        <h5>2. Book Instantly</h5>
                        <p>Found your ideal place? Create an account and book your room in just minutes. No long forms, no complicated processes.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="pub-card text-center">
                        <div class="pub-card-icon mx-auto"><i class="fas fa-key"></i></div>
                        <h5>3. Move In</h5>
                        <p>Once confirmed, coordinate with your landlord and move into your new home. Rate your experience to help future tenants.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Why Choose Us ── -->
    <section class="pub-section">
        <div class="container">
            <div class="pub-section-title">
                <div class="title-accent"></div>
                <h2>Why Choose Us</h2>
                <p>What sets HostelHub apart from the rest</p>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="pub-card text-center">
                        <div class="pub-card-icon mx-auto"><i class="fas fa-shield-alt"></i></div>
                        <h5>Verified Properties</h5>
                        <p>Every property is inspected and approved before listing.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="pub-card text-center">
                        <div class="pub-card-icon mx-auto"><i class="fas fa-tags"></i></div>
                        <h5>Best Prices</h5>
                        <p>Transparent pricing with no hidden fees or surprises.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="pub-card text-center">
                        <div class="pub-card-icon mx-auto"><i class="fas fa-headset"></i></div>
                        <h5>Dedicated Support</h5>
                        <p>Our team is always ready to help with any questions.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="pub-card text-center">
                        <div class="pub-card-icon mx-auto"><i class="fas fa-user-friends"></i></div>
                        <h5>Community</h5>
                        <p>Join a growing community of tenants and landlords.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── CTA ── -->
    <section class="pub-hero" style="padding:60px 0;">
        <div class="container text-center">
            <div class="pub-hero-content">
                <h2 style="font-size:2rem; font-weight:800;">Ready to Find Your Perfect Stay?</h2>
                <p style="margin-bottom:24px;">Browse our collection of verified properties and book your room today.</p>
                <a href="index.php" class="btn-pub-solid" style="padding:14px 36px; font-size:1rem; border-radius:12px; background:rgba(255,255,255,0.2); border:2px solid rgba(255,255,255,0.4);">
                    <i class="fas fa-search"></i> Browse Properties
                </a>
            </div>
        </div>
    </section>

    <!-- ── Footer ── -->
    <footer class="pub-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="pub-footer-brand">
                        <img src="assets/images/big/icon.png" alt="HostelHub">
                        <span>HostelHub</span>
                    </div>
                    <p class="pub-footer-desc">Your trusted platform for finding quality hostel accommodations across Kenya.</p>
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
        window.addEventListener('scroll', function() {
            const nav = document.getElementById('pubNav');
            nav.classList.toggle('scrolled', window.scrollY > 50);
        });
    </script>
</body>

</html>
