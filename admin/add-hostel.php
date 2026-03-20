<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    include('../includes/image-upload-handler.php');
    include('../includes/hostel-helper.php');

    if(isset($_POST['submit'])){
        $hostel_name = $_POST['hostel_name'];
        $description = $_POST['description'];
        $address = $_POST['address'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $postal_code = $_POST['postal_code'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $status = 'pending'; // Changed from approved to pending for Admin verification workflow
        
        // Insert hostel basic information
        $query = "INSERT INTO hostels (tenant_id, name, description, address, city, state, postal_code, phone, email, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('isssssssss', $tenantId, $hostel_name, $description, $address, $city, $state, $postal_code, $phone, $email, $status);
        
        if($stmt->execute()){
            $hostel_id = $mysqli->insert_id;
            
            // Handle image uploads
            if(isset($_FILES['hostel_images']) && $_FILES['hostel_images']['error'][0] != UPLOAD_ERR_NO_FILE){
                $uploader = new ImageUploadHandler();
                $upload_results = $uploader->uploadHostelImages($_FILES['hostel_images'], $hostel_id);
                
                $uploaded_paths = [];
                foreach($upload_results as $result){
                    if(isset($result['success']) && $result['success']){
                        $uploaded_paths[] = $result['path'];
                    }
                }
                
                // Save image paths to database
                if(!empty($uploaded_paths)){
                    $primary_index = isset($_POST['primary_image']) ? intval($_POST['primary_image']) : 0;
                    saveHostelImages($mysqli, $hostel_id, $uploaded_paths, $primary_index);
                    
                    // Update featured image in hostels table
                    if(isset($uploaded_paths[$primary_index])){
                        updateFeaturedImage($mysqli, $hostel_id, $uploaded_paths[$primary_index]);
                    }
                }
            }
            
            // Handle hostel types and create room placeholders
            if(isset($_POST['hostel_types']) && is_array($_POST['hostel_types'])){
                foreach($_POST['hostel_types'] as $type_id){
                    $available_count = isset($_POST['type_count_' . $type_id]) ? intval($_POST['type_count_' . $type_id]) : 0;
                    $price = isset($_POST['type_price_' . $type_id]) ? floatval($_POST['type_price_' . $type_id]) : 0;
                    
                    if($available_count > 0 && $price > 0){
                        // 1. Insert into mapping
                        $query = "INSERT INTO hostel_type_mapping (hostel_id, type_id, available_count, price_per_month) 
                                  VALUES (?, ?, ?, ?)";
                        $stmt2 = $mysqli->prepare($query);
                        $stmt2->bind_param('iiid', $hostel_id, $type_id, $available_count, $price);
                        $stmt2->execute();
                        $stmt2->close();

                        // 2. Create Room Placeholders
                        for($i = 1; $i <= $available_count; $i++) {
                            $seater = 1; // Default seater for new unit
                            $temp_room_no = "PENDING-" . $hostel_id . "-" . $type_id . "-" . $i;
                            
                            $roomQuery = "INSERT INTO rooms (hostel_id, room_type_id, seater, room_no, fees, tenant_id) VALUES (?, ?, ?, ?, ?, ?)";
                            $roomStmt = $mysqli->prepare($roomQuery);
                            $roomStmt->bind_param('iiiidi', $hostel_id, $type_id, $seater, $temp_room_no, $price, $tenantId);
                            $roomStmt->execute();
                            $roomStmt->close();
                        }
                    }
                }
            }
            
            // Handle amenities
            if(isset($_POST['amenities']) && is_array($_POST['amenities'])){
                foreach($_POST['amenities'] as $amenity_id){
                    $query = "INSERT INTO hostel_amenities (hostel_id, amenity_id) VALUES (?, ?)";
                    $stmt3 = $mysqli->prepare($query);
                    $stmt3->bind_param('ii', $hostel_id, $amenity_id);
                    $stmt3->execute();
                    $stmt3->close();
                }
            }
            
            // Log Activity
            include_once('../includes/log-helper.php');
            include_once('../includes/toast-helper.php');
            include_once('../includes/notification-helper.php');

            // Notify Admin
            $a_q = "SELECT id FROM users WHERE role='admin' AND tenant_id IS NULL LIMIT 1";
            $a_res = $mysqli->query($a_q);
            if($a_row = $a_res->fetch_object()){
                sendNotification($a_row->id, 'New Hostel Registered', "Landlord {$_SESSION['full_name']} registered a new hostel: $hostel_name", $_SESSION['id']);
            }

            $uemail = isset($_SESSION['login']) ? $_SESSION['login'] : 'unknown';
            logActivity($_SESSION['id'], $uemail, 'Landlord/Admin', 'Register Hostel', "Registered hostel: $hostel_name (ID: $hostel_id)");
            
            setToast('success', 'Hostel has been registered successfully! Please assign room numbers now.');
            header("Location: init-hostel-rooms.php?hostel_id=$hostel_id");
            exit();
        } else {
            include_once('../includes/toast-helper.php');
            setToast('error', 'Error: Could not register hostel.');
            header("Location: add-hostel.php");
            exit();
        }
    }
    
    // Fetch all hostel types for the form
    $hostel_types = getAllHostelTypes($mysqli);
    
    // Fetch all amenities for the form
    $amenities = getAllAmenities($mysqli);
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <title>Add Hostel - Hostel Management System</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../dist/css/icons/font-awesome/css/fontawesome-all.min.css" rel="stylesheet">
    
    <style>
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .image-preview {
            position: relative;
            width: 150px;
            height: 150px;
            border: 2px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .image-preview .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
        }
        .image-preview .primary-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: #28a745;
            color: white;
            padding: 2px 8px;
            font-size: 10px;
            border-radius: 3px;
        }
        .type-pricing-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        .amenity-checkbox {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        
        <header class="topbar" data-navbarbg="skin6">
            <?php include 'includes/navigation.php'?>
        </header>
        
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <?php include 'includes/sidebar.php'?>
            </div>
        </aside>
        
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Add New Hostel</h4>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">
                <form method="POST" enctype="multipart/form-data">
                    
                    <!-- Basic Information Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Basic Information</h4>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Hostel Name <span class="text-danger">*</span></label>
                                                <input type="text" name="hostel_name" class="form-control" placeholder="Enter hostel name" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone Number</label>
                                                <input type="text" name="phone" class="form-control" placeholder="Enter phone number">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" name="email" class="form-control" placeholder="Enter email address">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>City(Area) <span class="text-danger">*</span></label>
                                                <input type="text" name="city" class="form-control" placeholder="Lowlands,Mungoni..." required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Address(Where situated) <span class="text-danger">*</span></label>
                                                <input type="text" name="address" class="form-control" placeholder="Near Lowlands Hotel..." required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Region(county)</label>
                                                <input type="text" name="state" class="form-control" placeholder="Tharaka Nithi...">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Caretaker No:</label>
                                                <input type="text" name="postal_code" class="form-control" placeholder="Enter Phone Number">
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Description</label>
                                                <textarea name="description" class="form-control" rows="4" placeholder="Enter hostel description"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image Upload Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Hostel Images</h4>
                                    <p class="card-subtitle">Upload multiple images include images of rooms(JPG, PNG, GIF - Max 5MB each)</p>
                                    
                                    <div class="form-group">
                                        <label>Select Images</label>
                                        <input type="file" name="hostel_images[]" id="hostel_images" class="form-control" multiple accept="image/*" onchange="previewImages(event)">
                                        <small class="form-text text-muted">Hold Ctrl (Cmd on Mac) to select multiple images</small>
                                    </div>
                                    
                                    <div id="image-preview-container" class="image-preview-container"></div>
                                    
                                    <input type="hidden" name="primary_image" id="primary_image" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Room Types & Pricing Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Room Types & Pricing</h4>
                                    <p class="card-subtitle">Select available room types and set pricing</p>
                                    
                                    <?php foreach($hostel_types as $type): ?>
                                    <div class="type-pricing-row">
                                        <div class="custom-control custom-checkbox" style="min-width: 200px;">
                                            <input type="checkbox" class="custom-control-input" id="type_<?php echo $type->id; ?>" 
                                                   name="hostel_types[]" value="<?php echo $type->id; ?>" onchange="toggleTypeInputs(<?php echo $type->id; ?>)">
                                            <label class="custom-control-label" for="type_<?php echo $type->id; ?>">
                                                <?php echo $type->type_name; ?>
                                            </label>
                                        </div>
                                        
                                        <div style="flex: 1; display: flex; gap: 10px;">
                                            <input type="number" name="type_count_<?php echo $type->id; ?>" id="count_<?php echo $type->id; ?>" 
                                                   class="form-control" placeholder="Available Count" min="0" disabled>
                                            <input type="number" name="type_price_<?php echo $type->id; ?>" id="price_<?php echo $type->id; ?>" 
                                                   class="form-control" placeholder="Price/Month (KSh)" step="0.01" min="0" disabled>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Amenities Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Amenities</h4>
                                    <p class="card-subtitle">Select all available amenities</p>
                                    
                                    <div>
                                        <?php foreach($amenities as $amenity): ?>
                                        <div class="amenity-checkbox">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="amenity_<?php echo $amenity->id; ?>" 
                                                       name="amenities[]" value="<?php echo $amenity->id; ?>">
                                                <label class="custom-control-label" for="amenity_<?php echo $amenity->id; ?>">
                                                    <i class="fas <?php echo $amenity->icon_class; ?>"></i>
                                                    <?php echo $amenity->amenity_name; ?>
                                                </label>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="row">
                        <div class="col-12">
                            <div class="form-actions">
                                <div class="text-center">
                                    <button type="submit" name="submit" class="btn btn-success">Register Hostel</button>
                                    <button type="reset" class="btn btn-danger">Reset</button>
                                    <a href="manage-hostels.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>
            
            <?php include 'includes/footer.php' ?>
        </div>
    </div>
    
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../dist/js/app-style-switcher.js"></script>
    <script src="../dist/js/feather.min.js"></script>
    <script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="../dist/js/sidebarmenu.js"></script>
    <script src="../dist/js/custom.min.js"></script>
    
    <script>
        $(".preloader").fadeOut();
        
        // Image preview functionality
        let selectedFiles = [];
        let primaryImageIndex = 0;
        
        function previewImages(event) {
            const files = event.target.files;
            selectedFiles = Array.from(files);
            const container = document.getElementById('image-preview-container');
            container.innerHTML = '';
            
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'image-preview';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-btn" onclick="removeImage(${index})">&times;</button>
                        ${index === primaryImageIndex ? '<span class="primary-badge">Primary</span>' : ''}
                    `;
                    div.onclick = function() { setAsPrimary(index); };
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }
        
        function removeImage(index) {
            event.stopPropagation();
            selectedFiles.splice(index, 1);
            if (primaryImageIndex >= selectedFiles.length) {
                primaryImageIndex = Math.max(0, selectedFiles.length - 1);
            }
            updateImagePreviews();
        }
        
        function setAsPrimary(index) {
            primaryImageIndex = index;
            document.getElementById('primary_image').value = index;
            updateImagePreviews();
        }
        
        function updateImagePreviews() {
            const container = document.getElementById('image-preview-container');
            container.innerHTML = '';
            
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'image-preview';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-btn" onclick="removeImage(${index})">&times;</button>
                        ${index === primaryImageIndex ? '<span class="primary-badge">Primary</span>' : ''}
                    `;
                    div.onclick = function() { setAsPrimary(index); };
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
            
            // Update file input
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            document.getElementById('hostel_images').files = dataTransfer.files;
        }
        
        // Toggle type pricing inputs
        function toggleTypeInputs(typeId) {
            const checkbox = document.getElementById('type_' + typeId);
            const countInput = document.getElementById('count_' + typeId);
            const priceInput = document.getElementById('price_' + typeId);
            
            if (checkbox.checked) {
                countInput.disabled = false;
                priceInput.disabled = false;
                countInput.required = true;
                priceInput.required = true;
            } else {
                countInput.disabled = true;
                priceInput.disabled = true;
                countInput.required = false;
                priceInput.required = false;
                countInput.value = '';
                priceInput.value = '';
            }
        }
    </script>
</body>

</html>
