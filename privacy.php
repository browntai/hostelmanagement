<?php session_start(); ?>
<?php 
    $page_title = "Privacy Policy — HostelHub";
    include('includes/public-header.php'); 
?>

<body class="pub-page">

    <?php include('includes/public-nav.php'); ?>

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

<?php include('includes/public-footer.php'); ?>

</body>

</html>
