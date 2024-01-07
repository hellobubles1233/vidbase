<?php 
session_start();
require_once '../layout/layout.php';
require_once '../database.php';

//Prüfen Ob der User eingeloggt ist
if(isset($_GET['login'])) {
    $email = $_POST['email'];
    $passwort = $_POST['passwort'];
    
    $statement = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $result = $statement->execute(array('email' => $email));
    $user = $statement->fetch();
        
    //Überprüfung des Passworts
    if ($user !== false && password_verify($passwort, $user['passwort'])) {
        $_SESSION['userid'] = $user['id'];
        header("Location: ../index.php");
    } else {
        //Wenn Login Daten Falsch oder nicht Vorhanden wird:
        $errorMessage = "E-Mail oder Passwort war ungültig<br>";
        //Ausgegeben
    }
    
}
//Fehler Ausgabe
if(isset($errorMessage)) {echo $errorMessage;}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body data-bs-theme="dark">
    <div class="container d-flex justify-content-center align-items-center gap-2 mt-5">
        <!--TITEL-->
        <h1 class="fw-bold text-center">vidbase</h1>
        <img src="../assets/logo.jpg" alt="Logo" class="rounded-3" height="80" draggable="false">
    </div>
        <!--LOGIN-->
        <div class="container mt-5 border border-2 border-light w-50 rounded-3 p-2">
            <h1 class="p-3 ms-3">Login</h1>
                <form action="?login=1" method="post" class="mb-3 m-3">
                    <div class="form-floating mb-3 m-3">
                        <input type="email" class="form-control" id="email" placeholder="name@example.com" name="email">
                        <label for="email">E-Mail</label>
                    </div>
                    <div class="form-floating mb-3 m-3">
                        <input type="password" class="form-control" id="passwort" placeholder="Password" name="passwort">
                        <label for="passwort">Passwort</label>
                    </div>
                    <div class="d-flex">
                        <button class="btn btn-outline-light ms-3" type="submit">Login</button>
                        <p class="ms-5 me-1 pt-2">Noch kein Account?</p>
                        <a class="link-light pt-2" href="register.php"> Registrieren</a>
                    </div>
                </form>
            </div>
</body>
</html>