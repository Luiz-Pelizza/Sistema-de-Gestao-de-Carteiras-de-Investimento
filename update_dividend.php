<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dividend_type']) && isset($_POST['dividend_value']) && isset($_POST['update_id'])) {
    $update_id = $_POST['update_id'];
    $dividend_type = $_POST['dividend_type'];
    $dividend_value = $_POST['dividend_value'];

    if ($dividend_type == 'automatic' && $dividend_value == 0) {
        $dividend_value = 0;
    } elseif ($dividend_type == 'manual' && $dividend_value == 0) {
        $dividend_value = 0;
    }

    $stmt = $conn->prepare("UPDATE acoes SET dividend_type = ?, dividend_value = ? WHERE id = ?");
    $stmt->bind_param("sdi", $dividend_type, $dividend_value, $update_id);
    $stmt->execute();
}
?>
