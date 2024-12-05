<?php
session_start();
include '../connection.php';
include '../dashboard/user_privileges.php';

session_unset();
session_destroy();

if (isset($_SESSION['user_id'])) {
    header('../dashboard/home.php');
    exit();
}

if (isset($_POST['login_submit'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['pass'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $requete = "SELECT * FROM users WHERE email = :email";
        $statement = $con->prepare($requete);
        $statement->bindParam(':email', $email);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            if (password_verify($password, $result['password'])) {
                session_regenerate_id(true); // Regenerate session ID
                setUserSession($result);
                header('Location: home.php'); // Redirect to home after login
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No user found with that email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Alumni System</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="bg-sign-in d-flex justify-content-center align-items-center">
        <div class="form-sign-in bg-white mt-2 h-auto mb-2 text-center pt-2 pe-4 ps-4 d-flex flex-column">
            <h3 class="sign-in text-uppercase">Sign In</h3>
            <p>Enter your credentials to access your account</p>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-3 mt-3 text-start">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" placeholder="Enter email" name="email" required autocomplete="email">
                </div>
                <div class="mb-3 text-start">
                    <label for="pwd">Password:</label>
                    <input type="password" class="form-control" id="pwd" placeholder="Enter password" name="pass" required autocomplete="current-password">
                </div>
                <div class="mb-3 form-check d-flex gap-2">
                    <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember Me</label>
                </div>
                <button type="submit" name="login_submit" class="btn text-white w-100 text-uppercase">Sign In</button>
                <p class="mt-4">Forgot your password? <a href="resetpass.php">Reset Password</a></p>
                <button type="button" class="btn btn-success mb-3" onclick="window.location.href='signup.php';">Create Account</button>
            </form>
        </div>
    </main>
    <script src="../assets/js/bootstrap.bundle.js"></script>
    <script src="../assets/js/validation.js"></script>
</body>
</html>
