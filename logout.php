<?php
// =========================================================
// LOGOUT.PHP - cierra la sesion del usuario
// =========================================================
session_start();
session_unset();   // borra todas las variables de sesion
session_destroy(); // destruye la sesion completamente
header('Location: index.php');
exit;
?>
