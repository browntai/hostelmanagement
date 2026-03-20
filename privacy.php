<?php session_start(); ?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Privacy Policy for HostelHub — how we collect, use, and protect your personal information.">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title>Privacy Policy — HostelHub</title>
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
    <section class="pub-hero pub-hero-small" style="background: linear-gradient(135deg, rgba(13, 20, 50, 0.85) 0%, rgba(13, 20, 50, 0.45) 100%), url('assets/images/heroes/gallery_hero.png') center/cover no-repeat;">
        <div class="container">
            <div class="pub-hero-content">
                <h1>Privacy Policy</h1>
                <p>Your privacy and data security are important to us</p>
            </div>
        </div>
    </section>

    <!-- ── Policy Content ── -->
    <section class="pub-section">
        <div class="container" style="max-width:860px;">
            <div class="pub-policy-content">
                <span class="policy-effective"><i class="fas fa-calendar-alt"></i> &nbsp;Last updated: <?php echo date('F Y'); ?></span>

                <h3>1. Information We Collect</h3>
                <p>When you use HostelHub, we may collect the following types of information:</p>
                <ul>
                    <li><strong>Personal Information:</strong> Name, email address, phone number, ID number, and gender during account registration.</li>
                    <li><strong>Usage Data:</strong> Pages visited, search queries, booking activity, and interaction patterns on the platform.</li>
                    <li><strong>Device Information:</strong> IP address, browser type, operating system, and access timestamps for security and analytics.</li>
                    <li><strong>Location Data:</strong> City and country information derived from your IP address for service improvement.</li>
                </ul>

                <h3>2. How We Use Your Information</h3>
                <p>We use collected data for the following purposes:</p>
                <ul>
                    <li>To create and manage your account on the platform.</li>
                    <li>To process bookings and facilitate communication between tenants and property owners.</li>
                    <li>To improve our services, features, and user experience.</li>
                    <li>To send important notifications about your bookings and account activity.</li>
                    <li>To detect, prevent, and address fraud or technical issues.</li>
                </ul>

                <h3>3. Cookies & Tracking</h3>
                <p>HostelHub uses session cookies to maintain your login state and provide a smooth browsing experience. We do not use third-party tracking cookies for advertising purposes. Essential cookies are necessary for the operation of the platform and cannot be disabled.</p>

                <h3>4. Data Sharing & Third Parties</h3>
                <p>We do not sell, trade, or rent your personal information to third parties. Your information may be shared only in the following circumstances:</p>
                <ul>
                    <li><strong>With Property Owners:</strong> Limited contact details are shared with landlords when you make a booking, solely for the purpose of facilitating your accommodation.</li>
                    <li><strong>Legal Requirements:</strong> We may disclose information if required by law or in response to valid legal requests.</li>
                    <li><strong>Service Providers:</strong> Trusted partners who assist with platform operations under strict data protection agreements.</li>
                </ul>

                <h3>5. Data Security</h3>
                <p>We implement industry-standard security measures to protect your personal information, including:</p>
                <ul>
                    <li>Encrypted password storage using secure hashing algorithms.</li>
                    <li>Secure session management to prevent unauthorized access.</li>
                    <li>Regular security audits and updates to our systems.</li>
                    <li>Access controls limiting data access to authorized personnel only.</li>
                </ul>

                <h3>6. Your Rights</h3>
                <p>As a user of HostelHub, you have the following rights regarding your personal data:</p>
                <ul>
                    <li><strong>Access:</strong> Request a copy of the personal data we hold about you.</li>
                    <li><strong>Correction:</strong> Update or correct inaccurate personal information through your profile settings.</li>
                    <li><strong>Deletion:</strong> Request deletion of your account and associated data by contacting our support team.</li>
                    <li><strong>Portability:</strong> Request your data in a portable, machine-readable format.</li>
                </ul>

                <h3>7. Children's Privacy</h3>
                <p>HostelHub is not intended for individuals under the age of 18. We do not knowingly collect personal information from minors. If you believe we have inadvertently collected such information, please contact us immediately.</p>

                <h3>8. Changes to This Policy</h3>
                <p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated effective date. We encourage you to review this policy periodically to stay informed about how we protect your data.</p>

                <h3>9. Contact Us</h3>
                <p>If you have questions or concerns about this Privacy Policy or our data practices, please contact us:</p>
                <ul>
                    <li><strong>Email:</strong> <a href="mailto:support@leaseandstay.com" style="color:var(--pub-blue);">support@leaseandstay.com</a></li>
                    <li><strong>Web:</strong> <a href="contact.php" style="color:var(--pub-blue);">Contact Page</a></li>
                </ul>
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
