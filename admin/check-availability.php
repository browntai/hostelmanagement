<?php
    require_once("../includes/dbconn.php");
    if(!empty($_POST["emailid"])) {
        $email= $_POST["emailid"];
        if (filter_var($email, FILTER_VALIDATE_EMAIL)===false) {

            echo "error : You did not enter a valid email.";
        } else {
            $result ="SELECT count(*) FROM users WHERE email=?";
            $stmt = $mysqli->prepare($result);
            $stmt->bind_param('s',$email);
            $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    if($count>0){
    echo "<span style='color:red'> Email already exist .</span>";
        } else {
            echo "<span style='color:green'> Email available for bookings .</span>";
        }
     }
    }

    if(!empty($_POST["oldpassword"])) {
    $pass=$_POST["oldpassword"];
    $result ="SELECT password FROM users WHERE password=?";
    $stmt = $mysqli->prepare($result);
    $stmt->bind_param('s',$pass);
    $stmt->execute();
    $stmt -> bind_result($result_pass);
    $stmt -> fetch();
    $stmt->close();
    $opass=$result_pass;
    if($opass==$pass) 
    echo "<span style='color:green'> Password  matched.</span>";
    else echo "<span style='color:red'>Password doesnot match!</span>";
    }


    if(!empty($_POST["roomno"])) {
    $roomno=$_POST["roomno"];
    
    // Add Tenant Check
    include_once '../includes/tenant_manager.php';
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    // 1. Get Room Capacity
    $roomQuery = "SELECT seater FROM rooms WHERE room_no=? AND tenant_id=?";
    $roomStmt = $mysqli->prepare($roomQuery);
    $roomStmt->bind_param('ii', $roomno, $tenantId);
    $roomStmt->execute();
    $roomStmt->bind_result($capacity);
    $roomExists = $roomStmt->fetch();
    $roomStmt->close();

    if (!$roomExists) {
        echo "<span style='color:red'>Room not found.</span>";
    } else {
        // 2. Count Current Bookings
        $countQuery = "SELECT count(*) FROM bookings WHERE roomno=? AND tenant_id=?";
        $countStmt = $mysqli->prepare($countQuery);
        $countStmt->bind_param('ii', $roomno, $tenantId);
        $countStmt->execute();
        $countStmt->bind_result($currentCount);
        $countStmt->fetch();
        $countStmt->close();

    // 1. Get Room Capacity and Status
    $roomQuery = "SELECT seater, status FROM rooms WHERE room_no=? AND tenant_id=?";
    $roomStmt = $mysqli->prepare($roomQuery);
    $roomStmt->bind_param('ii', $roomno, $tenantId);
    $roomStmt->execute();
    $roomStmt->bind_result($capacity, $status);
    $roomExists = $roomStmt->fetch();
    $roomStmt->close();

    if (!$roomExists) {
        echo "<span style='color:red'>Room not found.</span>";
    } else {
        // 2. Count Current Bookings
        $countQuery = "SELECT count(*) FROM bookings WHERE roomno=? AND tenant_id=? AND (booking_status != 'cancelled' AND booking_status != 'rejected')";
        $countStmt = $mysqli->prepare($countQuery);
        $countStmt->bind_param('ii', $roomno, $tenantId);
        $countStmt->execute();
        $countStmt->bind_result($currentCount);
        $countStmt->fetch();
        $countStmt->close();

        // 3. Logic Check
        if($status === 'booked' || $currentCount > 0) {
            echo "<span style='color:red'>This room is already booked.</span>";
        } else {
            echo "<span style='color:green'>Room is available. Capacity: $capacity</span>";
        }
    }
    }
    }
?>
