<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['switch_type'])) {
    $_SESSION['app_type'] = $_POST['switch_type'];
}
header("Location: index.php");
exit();
?>