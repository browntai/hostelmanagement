<?php
    session_start();
    include('../includes/pdoconfig.php');
    
    // Manual Tenant Logic for PDO file
    $tenantId = isset($_SESSION['tenant_id']) ? $_SESSION['tenant_id'] : null;
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'super_admin' && isset($_SESSION['impersonate_tenant_id'])) {
        $tenantId = $_SESSION['impersonate_tenant_id'];
    }

    if(!empty($_POST["roomid"])) {	
        $id=$_POST['roomid'];
        $stmt = $DB_con->prepare("SELECT * FROM rooms WHERE room_no = :id AND tenant_id = :tid");
        $stmt->execute(array(':id' => $id, ':tid' => $tenantId));
        ?>
        <?php
            while($row=$stmt->fetch(PDO::FETCH_ASSOC))
            {
        ?>
        <?php echo htmlentities($row['seater']); ?>
        <?php
        }
    }

    if(!empty($_POST["rid"])) {	
        $id=$_POST['rid'];
        $stmt = $DB_con->prepare("SELECT * FROM rooms WHERE room_no = :id AND tenant_id = :tid");
        $stmt->execute(array(':id' => $id, ':tid' => $tenantId));
        ?>
        <?php
        while($row=$stmt->fetch(PDO::FETCH_ASSOC))
        {
        ?>
        <?php echo htmlentities($row['fees']); ?>
        <?php
        }
    }
?>
