<?php
    session_start();
    include('../includes/pdoconfig.php');
    if(!empty($_POST["room_no"]) && !empty($_POST["hostel_id"])) {	
    $room_no=$_POST['room_no'];
    $hostel_id = intval($_POST['hostel_id']);
    $stmt = $DB_con->prepare("SELECT * FROM rooms WHERE room_no = :room_no AND hostel_id = :hostel_id");
    $stmt->execute(array(':room_no' => $room_no, ':hostel_id' => $hostel_id));
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
        echo htmlentities($row['seater']);
    }
}

if(!empty($_POST["rid"]) && !empty($_POST["hostel_id"])) {	
    $room_no=$_POST['rid'];
    $hostel_id = intval($_POST['hostel_id']);
    $stmt = $DB_con->prepare("SELECT * FROM rooms WHERE room_no = :room_no AND hostel_id = :hostel_id");
    $stmt->execute(array(':room_no' => $room_no, ':hostel_id' => $hostel_id));
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
        echo htmlentities($row['fees']);
    }
}

?>
