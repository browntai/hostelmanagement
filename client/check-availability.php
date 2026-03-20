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
    echo "<span style='color:red'> Email already exist! Try using new one.</span>";
        } else {
            echo "<span style='color:green'> Email available for bookings!!</span>";
        }
     }
    }

    if(!empty($_POST["oldpassword"])) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $userId = $_SESSION['id'];
        $pass=$_POST["oldpassword"];
        $pass=md5($pass);
        $result ="SELECT password FROM users WHERE password=? AND id=?";
        $stmt = $mysqli->prepare($result);
        $stmt->bind_param('si',$pass, $userId);
        $stmt->execute();
        $stmt -> bind_result($result_pass);
        $stmt -> fetch();
        $stmt->close();
        $opass=$result_pass;
        if($opass==$pass) 
            echo "<span style='color:green'> Password  matched.</span>";
        else 
            echo "<span style='color:red'>Password doesnot match!</span>";
    }


    if(!empty($_POST["roomno"]) && !empty($_POST["hostel_id"])) {
        $roomno = $_POST["roomno"];
        $hostel_id = intval($_POST["hostel_id"]);

        // 1. Get Room Capacity and Status
        $roomQuery = "SELECT seater, status FROM rooms WHERE room_no=? AND hostel_id=?";
        $roomStmt = $mysqli->prepare($roomQuery);
        $roomStmt->bind_param('ii', $roomno, $hostel_id);
        $roomStmt->execute();
        $roomStmt->bind_result($capacity, $status);
        $roomExists = $roomStmt->fetch();
        $roomStmt->close();

        if (!$roomExists) {
            echo "<span style='color:red'>Room not found.</span>";
        } else {
            // 2. Count Current Bookings
            $countQuery = "SELECT count(*) FROM bookings WHERE roomno=? AND hostel_id=? AND (booking_status != 'cancelled' AND booking_status != 'rejected')";
            $countStmt = $mysqli->prepare($countQuery);
            $countStmt->bind_param('ii', $roomno, $hostel_id);
            $countStmt->execute();
            $countStmt->bind_result($currentCount);
            $countStmt->fetch();
            $countStmt->close();

            // 3. Logic Check
            if ($status === 'booked' || $currentCount > 0) {
                echo "<span style='color:red'>This room is already booked.</span>";
            } else {
                echo "<span style='color:green'>Room is available for booking.</span>";
            }
        }
    }
?>
