<?php
session_start();
require_once '../layout/layout.php';
include("password.php");
require_once '../database.php';

if(isset($_GET['register'])) {
    $error = false;
    $email = $_POST['email'];
    $passwort = $_POST['passwort'];
    $passwort2 = $_POST['passwort2'];
    $vorname = $_POST['vorname'];
    $nachname = $_POST['nachname'];

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<div class="alert alert-danger" role="alert">Bitte eine gültige E-Mail-Adresse eingeben</div>';
        $error = true;
    }     
    if(strlen($passwort) == 0) {
        echo '<div class="alert alert-danger" role="alert">Bitte ein Passwort angeben</div>';
        $error = true;
    }
    if($passwort != $passwort2) {
        echo '<div class="alert alert-danger" role="alert">Die Passwörter müssen übereinstimmen</div>';
        $error = true;
    }
    if(strlen($vorname) == 0 || strlen($nachname) == 0) {
        echo '<div class="alert alert-danger" role="alert">Bitte Vorname und Nachname angeben</div>';
        $error = true;
    } //Überprüfe, dass die E-Mail-Adresse noch nicht registriert wurde
    if(!$error) { 
        $statement = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $result = $statement->execute(array('email' => $email));
        $user = $statement->fetch();
        
        if($user !== false) {
            echo '<div class="alert alert-danger" role="alert">Diese E-Mail-Adresse ist bereits vergeben</div>';
            $error = true;
        }    
    } //Keine Fehler, wir können den Nutzer registrieren
    if(!$error) {    
        $passwort_hash = password_hash($passwort, PASSWORD_DEFAULT);
        
        $statement = $pdo->prepare("INSERT INTO users (email, passwort, vorname, nachname) VALUES (:email, :passwort, :vorname, :nachname)");
        $result = $statement->execute(array('email' => $email, 'passwort' => $passwort_hash, 'vorname' => $vorname, 'nachname' => $nachname));
        
        if($result) {        
            header("Location: login.php");
            exit();
        } else {
            echo 'Beim Abspeichern ist leider ein Fehler aufgetreten<br>';
        }
    } 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrieren</title>
</head>
<body data-bs-theme="dark">
    <div class="container d-flex justify-content-center align-items-center gap-2 mt-5">
        <!--TITEL-->
        <h1 class="fw-bold text-center">vidbase</h1>
        <img src="../assets/logo.jpg" alt="Logo" class="rounded-3" height="80" draggable="false">
    </div>
    <!--REGISTRIERUNG-->
    <div class="container mt-5 border border-2 border-light w-50 rounded-3 p-2">
        <h1 class="p-3 ms-3">Registrieren</h1>
                <form action="?register=1" method="post" class="mb-3 m-3">
            <div class="form-floating mb-3 m-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com">
                <label for="email">E-Mail</label>
            </div>
            <div class="form-floating mb-3 m-3">
                <input type="password" class="form-control" id="passwort" name="passwort" placeholder="Password">
                <label for="passwort">Passwort</label>
            </div>
            <div class="form-floating mb-3 m-3">
                <input type="password" class="form-control" id="passwort2" name="passwort2" placeholder="Password">
                <label for="passwort2">Passwort Wiederholen</label>
            </div>
            <div class="form-floating mb-3 m-3">
                <input type="text" class="form-control" id="fname" name="vorname" placeholder="Vorname">
                <label for="fname">Vorname</label>
            </div>
            <div class="form-floating mb-3 m-3">
                <input type="text" class="form-control" id="lname" name="nachname" placeholder="Nachname">
                <label for="lname">Nachname</label>
            </div>
            <div class="d-flex">
            <input class="btn btn-outline-light ms-3" type="submit" value="Registrieren">
                <p class="ms-5 me-1 pt-2">Schon ein Account?</p>
                <a class="link-light pt-2" href="login.php">Login</a>
            </div>
        </form>
    </div>
</body>
</html>