<?php
function check_login()
{
    if(strlen($_SESSION['id'])==0)
    {	
        $host = $_SERVER['HTTP_HOST'];
        $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra="login.php";		
        $_SESSION["id"]="";
        // Use a relative path assuming we are in a subfolder (admin/client) 
        // or try to detect root. 
        // For now, let's try the original logic but corrected to go UP one level if we are in a subdir.
        // Actually, let's just use the absolute path for this setup since it seems fixed.
        // header("Location: /HostelManagement-PHP/login.php");
        
        // BETTER: Use relative path ../login.php which works for admin/ and client/
        // If we are in root, this might fail, but root pages usually don't require login directly via this check 
        // (index.php is public).
        header("Location: ../login.php"); 
        exit();
    } else {
        // Double check user and tenant status
        if(isset($_SESSION['role']) && $_SESSION['role'] !== 'admin' && isset($_SESSION['tenant_id'])) {
            global $mysqli;
            
            if(!$mysqli) include('dbconn.php');

            // Check user status and role
            $stmt = $mysqli->prepare("SELECT status, role FROM users WHERE id = ?");
            $stmt->bind_param('i', $_SESSION['id']);
            $stmt->execute();
            $stmt->bind_result($u_status, $u_role);
            $stmt->fetch();
            $stmt->close();

            // Sync role if changed
            if ($u_role && $_SESSION['role'] !== $u_role) {
                $_SESSION['role'] = $u_role;
            }

            // Check tenant status
            $stmt = $mysqli->prepare("SELECT status FROM tenants WHERE id = ?");
            $stmt->bind_param('i', $_SESSION['tenant_id']);
            $stmt->execute();
            $stmt->bind_result($t_status);
            $stmt->fetch();
            $stmt->close();

            if($u_status === 'suspended' || $t_status === 'suspended') {
                session_destroy();
                header("Location: /login.php?msg=account_suspended");
                exit();
            }
        }
    }
}
?>
