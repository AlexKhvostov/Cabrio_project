<?php
$host = 'mysql80.hostland.ru'; // например, localhost или mysql.your-host.ru
$user = 'host1708875_cabrioride';
$password = 'Protokol911';
$db = 'host1708875_cabrioride';
$port = 3308; // добавлен порт

$conn = new mysqli($host, $user, $password, $db, $port);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
echo "Успешное подключение!";
?>