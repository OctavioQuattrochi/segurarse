<?php
$nombre = "Juan";
$apellido = "Perez";
$authHeader = base64_encode(str_replace(' ', '', $nombre . $apellido));
echo "Authorization Header: " . $authHeader . PHP_EOL;
?>

