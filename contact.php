<?php session_start(); ?>
<?php 
    $page_title = "Contact Us — HostelHub";
    include('includes/public-header.php'); 
?>

<body class="pub-page">

    <?php include('includes/public-nav.php'); ?>

    <!-- ── Hero ── -->
    <section class="pub-hero pub-hero-small" style="background: linear-gradient(135deg, rgba(13, 20, 50, 0.8) 0%, rgba(13, 20, 50, 0.4) 100%), url('assets/images/heroes/contact_hero.png') center/cover no-repeat;">
        <div class="container">
            <div class="pub-hero-content">
                <h1>Get In Touch</h1>
                <p>Have questions or feedback? We'd love to hear from you</p>
            </div>
        </div>
    </section>

    <!-- ── Contact Info Cards ── -->
    <section class="pub-section" style="padding-bottom:30px;">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="pub-contact-card">
                        <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <h6>Our Location</h6>
                        <p>Nairobi, Kenya<br>CBD, Kenyatta Avenue</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="pub-contact-card">
                        <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                        <h6>Email Us</h6>
                        <p>support@leaseandstay.com<br>info@leaseandstay.com</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="pub-contact-card">
                        <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
                        <h6>Call Us</h6>
                        <p>+254 700 000 000<br>Mon — Fri, 8AM — 6PM</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Contact Form & Map ── -->
    <section class="pub-section pub-section-alt" style="padding-top:30px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 mb-4">
                    <div class="pub-policy-content" style="margin-bottom:0; padding:40px;">
                        <h3 style="margin-top:0; border:none; padding-bottom:4px;">Send Us a Message</h3>
                        <p style="color:#5a6b7d; margin-bottom:24px;">Fill out the form below and we'll get back to you as soon as possible.</p>

                        <form id="contactForm" onsubmit="handleContactSubmit(event)">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group" style="margin-bottom:16px;">
                                        <label style="font-weight:600; color:var(--pub-navy); font-size:0.85rem; margin-bottom:6px;">Full Name</label>
                                        <input type="text" class="form-control" placeholder="Your name" required
                                               style="border-radius:var(--pub-radius-sm); border:1.5px solid rgba(123,164,208,0.25); padding:11px 14px; font-size:0.9rem;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" style="margin-bottom:16px;">
                                        <label style="font-weight:600; color:var(--pub-navy); font-size:0.85rem; margin-bottom:6px;">Email Address</label>
                                        <input type="email" class="form-control" placeholder="Your email" required
                                               style="border-radius:var(--pub-radius-sm); border:1.5px solid rgba(123,164,208,0.25); padding:11px 14px; font-size:0.9rem;">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom:16px;">
                                <label style="font-weight:600; color:var(--pub-navy); font-size:0.85rem; margin-bottom:6px;">Subject</label>
                                <select class="form-control" required
                                        style="border-radius:var(--pub-radius-sm); border:1.5px solid rgba(123,164,208,0.25); padding:11px 14px; font-size:0.9rem;">
                                    <option value="">Select a topic</option>
                                    <option value="booking">Booking Inquiry</option>
                                    <option value="property">List My Property</option>
                                    <option value="account">Account Issue</option>
                                    <option value="feedback">Feedback</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom:20px;">
                                <label style="font-weight:600; color:var(--pub-navy); font-size:0.85rem; margin-bottom:6px;">Message</label>
                                <textarea class="form-control" rows="5" placeholder="Tell us how we can help..." required
                                          style="border-radius:var(--pub-radius-sm); border:1.5px solid rgba(123,164,208,0.25); padding:11px 14px; font-size:0.9rem; resize:vertical;"></textarea>
                            </div>
                            <button type="submit" class="btn-auth-submit" id="contactBtn">
                                <i class="fas fa-paper-plane"></i> &nbsp;Send Message
                            </button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5 mb-4">
                    <!-- Map Placeholder -->
                    <div style="background:var(--pub-gradient); border-radius:var(--pub-radius); height:100%; min-height:300px; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#fff; padding:40px; text-align:center;">
                        <i class="fas fa-map-marked-alt" style="font-size:3rem; margin-bottom:20px; opacity:0.8;"></i>
                        <h5 style="font-weight:700; margin-bottom:10px;">Visit Our Office</h5>
                        <p style="opacity:0.85; font-size:0.9rem; margin-bottom:24px;">Nairobi CBD, Kenyatta Avenue<br>Kenya</p>
                        <div style="background:rgba(255,255,255,0.15); border-radius:12px; padding:20px; width:100%;">
                            <p style="margin:0 0 8px; font-size:0.85rem; opacity:0.9;"><i class="fas fa-clock"></i> &nbsp;Working Hours</p>
                            <p style="margin:0; font-weight:600;">Monday — Friday</p>
                            <p style="margin:0; opacity:0.85;">8:00 AM — 6:00 PM</p>
                            <hr style="border-color:rgba(255,255,255,0.15); margin:12px 0;">
                            <p style="margin:0; font-weight:600;">Saturday</p>
                            <p style="margin:0; opacity:0.85;">9:00 AM — 1:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── FAQ ── -->
    <section class="pub-section">
        <div class="container" style="max-width:760px;">
            <div class="pub-section-title">
                <div class="title-accent"></div>
                <h2>Frequently Asked Questions</h2>
            </div>
            <div class="pub-policy-content" style="padding:30px 36px;">
                <div style="margin-bottom:20px;">
                    <h6 style="font-weight:700; color:var(--pub-navy); margin-bottom:6px;">How do I book a hostel?</h6>
                    <p style="margin:0;">Simply browse our properties, select a hostel you like, create an account (or log in), and complete the booking. It takes just a few minutes!</p>
                </div>
                <hr style="border-color:rgba(123,164,208,0.12);">
                <div style="margin-bottom:20px;">
                    <h6 style="font-weight:700; color:var(--pub-navy); margin-bottom:6px;">Are all properties verified?</h6>
                    <p style="margin:0;">Yes! Every property listed on HostelHub goes through our verification process before being published.</p>
                </div>
                <hr style="border-color:rgba(123,164,208,0.12);">
                <div style="margin-bottom:20px;">
                    <h6 style="font-weight:700; color:var(--pub-navy); margin-bottom:6px;">How can I list my property?</h6>
                    <p style="margin:0;">Property owners can register as landlords through our platform. Visit the <a href="admin/index.php" style="color:var(--pub-blue); font-weight:600;">Landlord Portal</a> to get started.</p>
                </div>
                <hr style="border-color:rgba(123,164,208,0.12);">
                <div>
                    <h6 style="font-weight:700; color:var(--pub-navy); margin-bottom:6px;">Can I cancel my booking?</h6>
                    <p style="margin:0;">Yes, you can cancel your booking from your dashboard. Please review the cancellation terms in your booking details.</p>
                </div>
            </div>
        </div>
    </section>

<?php include('includes/public-footer.php'); ?>


        function handleContactSubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('contactBtn');
            btn.innerHTML = '<i class="fas fa-check-circle"></i> &nbsp;Message Sent!';
            btn.style.background = 'linear-gradient(135deg, #16a34a 0%, #22c55e 100%)';
            btn.disabled = true;
            setTimeout(function() {
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> &nbsp;Send Message';
                btn.style.background = '';
                btn.disabled = false;
                document.getElementById('contactForm').reset();
            }, 3000);
        }
    </script>
</body>

</html>
