<?php
if (!isset($_SESSION)) {
    session_start();
}

/**
 * Set a toast message for the next page load.
 * 
 * @param string $type 'success', 'error', 'warning', 'info'
 * @param string $message The message to display
 */
function setToast($type, $message) {
    $_SESSION['toast'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Output JavaScript to show the toast if one is set.
 * Should be called in the footer.
 */
function showAlerts() {
    if (isset($_SESSION['toast'])) {
        $type = $_SESSION['toast']['type'];
        $message = addslashes($_SESSION['toast']['message']);
        
        // Map types to Toastify colors/styles
        $colors = [
            'success' => "linear-gradient(135deg, #1D976C 0%, #93F9B9 100%)",
            'error' => "linear-gradient(135deg, #FF512F 0%, #DD2476 100%)",
            'warning' => "linear-gradient(135deg, #F09819 0%, #EDDE5D 100%)",
            'info' => "linear-gradient(135deg, #2E5E99 0%, #7BA4D0 100%)"
        ];
        
        $bg = isset($colors[$type]) ? $colors[$type] : $colors['info'];
        
        echo "<script>
            Toastify({
                text: '{$message}',
                duration: 3000,
                close: true,
                gravity: 'top', 
                position: 'center', 
                style: { background: '{$bg}' },
                stopOnFocus: true
            }).showToast();
        </script>";
        
        // Clear the toast
        unset($_SESSION['toast']);
    }
}
?>
