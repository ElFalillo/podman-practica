<?php
$host = "r3con-db";
$user = "r3con_user";
$password = "CambiaEstaClaveUsuario";
$database = "r3con";

$conexion = @mysqli_connect($host, $user, $password, $database);

echo "<h1>R3con - Laboratorio Podman</h1>";

echo "<p>Contenedor PHP con Apache ejecutándose correctamente.</p>";

if ($conexion) {
    echo "<p style='color:green;'>Conexión a MySQL correcta.</p>";
    mysqli_close($conexion);
} else {
    echo "<p style='color:red;'>Error de conexión: " . mysqli_connect_error() . "</p>";
}
?>
