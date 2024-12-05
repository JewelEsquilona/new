<?php
session_start();
include 'user_privileges.php'; 

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

include 'component/sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Alumni System</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-content">
    <div class="container-fluid">
        <header>
            <h1>Welcome to the Alumni System</h1>
        </header>
        <main>
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2>Dashboard Overview</h2>
                    <p>Here you can find an overview of the system's functionalities.</p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Alumni</h5>
                            <p class="card-text">
                                <?php
                                include '../connection.php';
                                $nbr_alumni = $con->query("SELECT COUNT(*) FROM `2024-2025`")->fetchColumn();
                                echo $nbr_alumni;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Courses</h5>
                            <p class="card-text">
                                <?php
                                $nbr_courses = $con->query("SELECT COUNT(*) FROM courses")->fetchColumn();
                                echo $nbr_courses;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Staffs</h5>
                            <p class="card-text">3</p> 
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/bootstrap.bundle.js"></script>
</body>
</html>
