<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sistema_acoes";
$api_key = "kXEdomGxBpRS8VUv1MidRz"; //Troque por sua chave API BRAPI

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}