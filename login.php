<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password);
    $stmt->fetch();

    if ($hashed_password !== null && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        header("Location: atv.php");
        exit;
    } else {
        echo "Usuário ou senha inválidos (Volte para a página anterior e tente novamente)";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/main3.css">
    <title>Erro</title>
</head>
<body>
    
</body>
</html>