<?php
    session_start();
    include('includes/dbconn.php');
    include('includes/hostel-helper.php');
    $page_title = "For property Owners — HostelHub";
    include('includes/public-header.php');
?>

    <?php include('includes/public-nav.php'); ?>

    <!-- ── Hero ── -->
    <section class="pub-hero" style="background: linear-gradient(135deg, rgba(13, 20, 50, 0.85) 0%, rgba(13, 20, 50, 0.5) 100%), url('assets/images/heroes/owners_hero.png') center/cover no-repeat; padding: 100px 0;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 text-left">
                    <div class="pub-hero-content" style="text-align:left;">
                        <span class="badge badge-primary py-2 px-3 mb-3" style="background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); border-radius:20px; font-weight:600; letter-spacing:1px; text-transform:uppercase; font-size:0.75rem;">Partner with HostelHub</span>
                        <h1 style="font-size:3.2rem; font-weight:900; line-height:1.1; margin-bottom:20px;">Maximize Your Property's Potential</h1>
                        <p style="font-size:1.15rem; opacity:0.9; margin-bottom:32px; max-width: None;">The most trusted platform for hostel owners in Kenya. Reach thousands of verified tenants, manage bookings effortlessly, and grow your business.</p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="client-registration.php" class="btn-pub-solid" style="padding:16px 36px; font-size:1.05rem; border-radius:14px; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">List Your Property</a>
                            <a href="#benefits" class="btn-pub-outline" style="padding:16px 36px; font-size:1.05rem; border-radius:14px; color:#fff; border-color:rgba(255,255,255,0.5);">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Stats Ribbon ── -->
    <section style="background: #fff; border-bottom: 1px solid rgba(0,0,0,0.05); padding: 30px 0;">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h3 style="font-weight:800; color:var(--pub-navy); margin-bottom:4px;">10k+</h3>
                    <p style="color:#8a9bb2; margin:0; font-size:0.9rem;">Monthly Active Tenants</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h3 style="font-weight:800; color:var(--pub-navy); margin-bottom:4px;">500+</h3>
                    <p style="color:#8a9bb2; margin:0; font-size:0.9rem;">Verified Properties</p>
                </div>
                <div class="col-md-4">
                    <h3 style="font-weight:800; color:var(--pub-navy); margin-bottom:4px;">24hr</h3>
                    <p style="color:#8a9bb2; margin:0; font-size:0.9rem;">Average Booking Time</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Why Choose Us ── -->
    <section id="benefits" class="pub-section">
        <div class="container">
            <div class="pub-section-title">
                <div class="title-accent"></div>
                <h2>Why List on HostelHub?</h2>
                <p>Everything you need to succeed as a property owner in the modern age</p>
            </div>

            <div class="row mt-5">
                <div class="col-lg-4 mb-4">
                    <div class="pub-card text-center h-100" style="padding:40px 30px;">
                        <div class="pub-card-icon mx-auto" style="background:rgba(46,94,153,0.08); color:var(--pub-blue); width:70px; height:70px; line-height:70px; font-size:1.8rem; border-radius:20px; margin-bottom:24px;">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h5 style="font-weight:700; color:var(--pub-navy); margin-bottom:14px;">High Visibility</h5>
                        <p style="color:#5a6b7d; font-size:0.95rem; line-height:1.6;">Our platform ranks #1 for hostel searches. Get your property in front of thousands of students and young professionals every day.</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="pub-card text-center h-100" style="padding:40px 30px;">
                        <div class="pub-card-icon mx-auto" style="background:rgba(217,119,6,0.08); color:#d97706; width:70px; height:70px; line-height:70px; font-size:1.8rem; border-radius:20px; margin-bottom:24px;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h5 style="font-weight:700; color:var(--pub-navy); margin-bottom:14px;">Verified Tenants</h5>
                        <p style="color:#5a6b7d; font-size:0.95rem; line-height:1.6;">Say goodbye to unreliable occupants. We verify every tenant's ID and contact details before they can even request a booking.</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="pub-card text-center h-100" style="padding:40px 30px;">
                        <div class="pub-card-icon mx-auto" style="background:rgba(22,163,74,0.08); color:#16a34a; width:70px; height:70px; line-height:70px; font-size:1.8rem; border-radius:20px; margin-bottom:24px;">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h5 style="font-weight:700; color:var(--pub-navy); margin-bottom:14px;">Secure Payments</h5>
                        <p style="color:#5a6b7d; font-size:0.95rem; line-height:1.6;">Direct bank transfers or M-Pesa. Choose how you get paid. Our system tracks every transaction so you never lose a cent.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── How It Works (Owners) ── -->
    <section class="pub-section pub-section-alt">
        <div class="container">
            <div class="pub-section-title">
                <div class="title-accent"></div>
                <h2>Getting Started is Easy</h2>
                <p>Start receiving bookings in as little as 24 hours</p>
            </div>

            <div class="row mt-5">
                <div class="col-md-3 mb-4">
                    <div class="text-center">
                        <div style="width:50px; height:50px; background:var(--pub-blue); color:#fff; border-radius:50%; line-height:50px; font-weight:800; margin:0 auto 20px; font-size:1.2rem; box-shadow:0 0 0 8px rgba(46,94,153,0.1);">1</div>
                        <h6 style="font-weight:700; color:var(--pub-navy);">Register Account</h6>
                        <p style="font-size:0.85rem; color:#8a9bb2;">Sign up as a landlord and verify your email.</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="text-center">
                        <div style="width:50px; height:50px; background:var(--pub-blue); color:#fff; border-radius:50%; line-height:50px; font-weight:800; margin:0 auto 20px; font-size:1.2rem; box-shadow:0 0 0 8px rgba(46,94,153,0.1);">2</div>
                        <h6 style="font-weight:700; color:var(--pub-navy);">Add Your Property</h6>
                        <p style="font-size:0.85rem; color:#8a9bb2;">Upload photos, set prices, and list amenities.</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="text-center">
                        <div style="width:50px; height:50px; background:var(--pub-blue); color:#fff; border-radius:50%; line-height:50px; font-weight:800; margin:0 auto 20px; font-size:1.2rem; box-shadow:0 0 0 8px rgba(46,94,153,0.1);">3</div>
                        <h6 style="font-weight:700; color:var(--pub-navy);">Get Verified</h6>
                        <p style="font-size:0.85rem; color:#8a9bb2;">Our team will review and approve your listing.</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="text-center">
                        <div style="width:50px; height:50px; background:var(--pub-blue); color:#fff; border-radius:50%; line-height:50px; font-weight:800; margin:0 auto 20px; font-size:1.2rem; box-shadow:0 0 0 8px rgba(46,94,153,0.1);">4</div>
                        <h6 style="font-weight:700; color:var(--pub-navy);">Start Earning</h6>
                        <p style="font-size:0.85rem; color:#8a9bb2;">Accept bookings and manage your rooms online.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Trusted Partners (Landlords) ── -->
    <section class="pub-section pub-section-alt">
        <div class="container">
            <div class="pub-section-title">
                <div class="title-accent"></div>
                <h2>Meet Our Trusted Partners</h2>
                <p>The dedicated owners behind Kenya's top-rated hostels</p>
            </div>

            <div class="row mt-5">
                <?php
                $landlord_q = "SELECT u.id, u.full_name, u.tenant_id, u.profile_pic,
                               (SELECT COUNT(*) FROM hostels WHERE tenant_id = u.tenant_id AND status = 'approved') as hostel_countValue
                               FROM users u 
                               WHERE u.role = 'landlord' 
                               HAVING hostel_countValue > 0
                               ORDER BY hostel_countValue DESC
                               LIMIT 4";
                $landlord_res = $mysqli->query($landlord_q);
                
                if($landlord_res && $landlord_res->num_rows > 0):
                    while($lnord = $landlord_res->fetch_object()):
                        // Get one hostel image for background preview
                        $preview_img = $mysqli->query("SELECT image_path FROM hostel_images hi JOIN hostels h ON hi.hostel_id = h.id WHERE h.tenant_id = '$lnord->tenant_id' LIMIT 1")->fetch_object();
                        $bg_path = $preview_img ? $preview_img->image_path : 'assets/images/hostel-placeholder.jpg';
                        
                        // Use actual profile pic if available
                        $avatar_path = 'assets/images/users/user-icn.png';
                        if (!empty($lnord->profile_pic)) {
                            // Try common upload paths
                            if(file_exists("uploads/profiles/".$lnord->profile_pic)) $avatar_path = "uploads/profiles/".$lnord->profile_pic;
                            elseif(file_exists("client/img/".$lnord->profile_pic)) $avatar_path = "client/img/".$lnord->profile_pic;
                            elseif(file_exists("admin/img/".$lnord->profile_pic)) $avatar_path = "admin/img/".$lnord->profile_pic;
                        }
                ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="pub-card text-center p-0 overflow-hidden h-100" style="border-radius:24px;">
                        <div style="height:120px; background: url('<?php echo $bg_path; ?>') center/cover; position:relative;">
                            <div style="position:absolute; inset:0; background:rgba(46,94,153,0.4);"></div>
                             <img src="<?php echo $avatar_path; ?>" style="width:90px; height:90px; border-radius:50%; object-fit:cover; border:4px solid #fff; position:absolute; bottom:-45px; left:50%; transform:translateX(-50%); box-shadow:var(--pub-shadow-sm); background:#f8f9fa;">
                        </div>
                        <div class="p-4 pt-5 mt-2">
                            <h5 style="font-weight:700; color:var(--pub-navy); margin-bottom:5px;"><?php echo $lnord->full_name; ?></h5>
                            <p style="font-size:0.85rem; color:#8a9bb2; margin-bottom:15px;">Property Owner</p>
                            <div style="background:rgba(46,94,153,0.05); border-radius:12px; padding:10px; display:inline-block; width:100%;">
                                <span style="font-weight:700; color:var(--pub-blue); font-size:1.1rem;"><?php echo $lnord->hostel_countValue; ?></span>
                                <span style="display:block; font-size:0.75rem; color:#5a6b7d; text-transform:uppercase; letter-spacing:0.5px;">Properties Listed</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; else: ?>
                    <div class="col-12 text-center py-4">
                        <p style="color:#8a9bb2;">Joining our network of trusted owners...</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ── Dashboard Preview ── -->
    <section class="pub-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 style="font-weight:800; color:var(--pub-navy); margin-bottom:20px;">Powerful Management Tools</h2>
                    <p style="color:#4a5568; line-height:1.8; margin-bottom:24px;">Our intuitive Landlord Dashboard gives you full control over your business. Monitor occupancy rates, track rent payments, and communicate with tenants all in one place.</p>
                    <ul style="list-style:none; padding:0; margin-bottom:32px;">
                        <li style="margin-bottom:12px; display:flex; align-items:center; gap:12px; color:#4a5568;">
                            <i class="fas fa-check-circle" style="color:#16a34a;"></i> Real-time occupancy tracking
                        </li>
                        <li style="margin-bottom:12px; display:flex; align-items:center; gap:12px; color:#4a5568;">
                            <i class="fas fa-check-circle" style="color:#16a34a;"></i> Automated rent collection & reporting
                        </li>
                        <li style="margin-bottom:12px; display:flex; align-items:center; gap:12px; color:#4a5568;">
                            <i class="fas fa-check-circle" style="color:#16a34a;"></i> Integrated tenant messaging system
                        </li>
                        <li style="margin-bottom:12px; display:flex; align-items:center; gap:12px; color:#4a5568;">
                            <i class="fas fa-check-circle" style="color:#16a34a;"></i> Maintenance request management
                        </li>
                    </ul>
                    <a href="admin/index.php" class="btn-pub-outline">Explore Dashboard Demo</a>
                </div>
                <div class="col-lg-6">
                    <div style="border-radius:16px; overflow:hidden; box-shadow: 0 30px 60px rgba(0,0,0,0.22), 0 8px 20px rgba(0,0,0,0.12);">
                        <img src="assets/images/big/dashboard-preview.png" alt="Dashboard Preview" style="width:100%; display:block; border-radius:16px;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── CTA Section ── -->
    <section class="pub-section" style="padding:0;">
        <div class="container">
            <div style="background:var(--pub-navy); border-radius:32px; padding:80px 40px; text-align:center; color:#fff; overflow:hidden; position:relative;">
                <div style="position:absolute; top:-100px; right:-100px; width:300px; height:300px; background:rgba(255,255,255,0.03); border-radius:50%;"></div>
                <div style="position:absolute; bottom:-100px; left:-100px; width:300px; height:300px; background:rgba(255,255,255,0.03); border-radius:50%;"></div>
                
                <h2 style="font-weight:900; font-size:2.5rem; margin-bottom:16px;">Ready to grow your business?</h2>
                <p style="opacity:0.8; font-size:1.1rem; max-width:600px; margin:0 auto 40px;">Join hundreds of successful property owners in Kenya. Start your 30-day free trial today.</p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="client-registration.php" class="btn-pub-solid" style="background:#fff; color:var(--pub-navy); padding:16px 40px; font-weight:800; border-radius:14px;">Sign Up Now</a>
                    <a href="contact.php" class="btn-pub-outline" style="border-color:rgba(255,255,255,0.3); color:#fff; padding:16px 40px; border-radius:14px;">Contact Sales</a>
                </div>
                <p style="margin-top:24px; font-size:0.85rem; opacity:0.6;">No credit card required. Cancel anytime.</p>
            </div>
        </div>
    </section>

    <!-- ── Spacer ── -->
    <div style="height:80px;"></div>

<?php include('includes/public-footer.php'); ?>
