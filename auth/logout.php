<?php
// Ausloggen
session_start();
session_destroy();
//Umleitung zurück auf index.php dann auth/login.php
header("Location: ../index.php");
?>