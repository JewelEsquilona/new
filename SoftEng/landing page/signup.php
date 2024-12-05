<?php
include '../connection.php';

try {
    $collegesQuery = "SELECT DISTINCT college FROM courses";
    $collegesStmt = $con->prepare($collegesQuery);
    $collegesStmt->execute();
    $existingColleges = $collegesStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $role = $_POST['role'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['pass'] ?? null;
    $confirmPassword = $_POST['conPass'] ?? null;
    $college = $_POST['college'] ?? null; 
    $department = $_POST['department'] ?? null; 

    error_log("Role: $role, Email: $email, College: $college, Department: $department");

    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert into the database
        $stmt = $con->prepare("INSERT INTO users (email, password, role, college, department) VALUES (?, ?, ?, ?, ?)");

        try {
            if ($stmt->execute([$email, $hashedPassword, $role, $college, $department])) {
                // Redirect to index.php after successful registration
                echo "<script>
                        alert('Registration successful! Redirecting to login page.');
                        window.location.href='index.php';
                      </script>";
                exit; // Ensure no further code is executed
            } else {
                echo "<script>alert('Registration failed! Please try again.');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-Step Signup Form</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-sign-in">
    <form id="signup" method="POST" action="">
        <div class="container">
            <h2 class="sign-in">Sign Up</h2>
            <div class="d-flex justify-content-center">
                <p>Enter your credentials to create your account</p>
            </div>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
            <div class="mb-3"></div>

            <div class="steps">
                <div class="step active"></div>
                <div class="step"></div>
            </div>

            <!-- Slide 1: Role Selection -->
            <div class="slider active" id="step1">
                <h5>Please Select a Role</h5>
                <div class="radio-options">
                    <label><input type="radio" name="role" value="Admin" required> Admin</label>
                    <label><input type="radio" name="role" value="Registrar"> Registrar</label>
                    <label><input type="radio" name="role" value="Dean"> Dean</label>
                    <label><input type="radio" name="role" value="Program Chair"> Program Chair</label>
                    <label><input type="radio" name="role" value="Alumni"> Alumni</label>
                </div>
                <div class="text-center">
                    <button type="button" class="next continue-button">Continue</button>
                </div>
                <div class="continue-sign-in-text">
                    <p class="mt-4">Already have an account? <a href="index.php">Sign In</a></p>
                </div>
            </div>

            <!-- Slide 2: Account Information -->
            <div class="slider" id="step2" style="display: none;">
                <div id="collegeDepartmentInfo" style="display: none;">
                    <label for="college">Select College:</label>
                    <select id="college" name="college">
                        <option value="" selected>Select College</option>
                        <?php foreach ($existingColleges as $college): ?>
                            <option value="<?= htmlspecialchars($college) ?>"><?= htmlspecialchars($college) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div id="departmentContainer" style="display: none;">
                        <label for="department">Select Department:</label>
                        <select id="department" name="department">
                            <option value="" selected>Select Department</option>
                        </select>
                    </div>
                </div>

                <div id="accountInfo">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required autocomplete="email">
                    <label for="pass">Password:</label>
                    <input type="password" id="pass" name="pass" required autocomplete="new-password">
                    <label for="conPass">Confirm Password:</label>
                    <input type="password" id="conPass" name="conPass" required autocomplete="new-password">
                </div>

                <div class="button-container">
                    <button type="button" class="back">Back</button>
                    <button type="submit" class="submit-button">Submit</button>
                </div>
            </div>
        </div>
    </form>

    <script src="../assets/js/bootstrap.bundle.js"></script>
    <script src="../assets/js/validation.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.slider');
            const steps = document.querySelectorAll('.step');
            let currentSlide = 0;

            function showSlide(index) {
                slides.forEach((slide, i) => {
                    slide.style.display = (i === index) ? 'block' : 'none';
                });
                steps.forEach((step, i) => {
                    step.classList.toggle('active', i <= index);
                });
            }

            // Handle role selection
            document.querySelectorAll('input[name="role"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const selectedRole = this.value;
                    if (selectedRole === 'Alumni') {
                        window.location.href = 'register.php'; // Redirect to register page
                    } else {
                        document.getElementById('collegeDepartmentInfo').style.display =
                            (selectedRole === 'Dean' || selectedRole === 'Program Chair') ? 'block' : 'none';
                    }
                });
            });

            // Show next slide
            document.querySelector('.next').addEventListener('click', function() {
                if (currentSlide === 0) {
                    currentSlide++;
                    showSlide(currentSlide);
                }
            });

            // Show previous slide
            document.querySelector('.back').addEventListener('click', function() {
                if (currentSlide === 1) {
                    currentSlide--;
                    showSlide(currentSlide);
                }
            });

            // Fetch departments based on selected college
            document.getElementById('college').addEventListener('change', function() {
                const college = this.value;
                const departmentSelect = document.getElementById('department');

                // Clear previous departments
                departmentSelect.innerHTML = '<option value="" selected>Select Department</option>';

                if (college) {
                    fetch(`../dashboard/get_departments.php?college=${encodeURIComponent(college)}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(department => {
                                const option = document.createElement('option');
                                option.value = department.department; // Ensure this matches your database field
                                option.textContent = department.department; // Ensure this matches your database field
                                departmentSelect.appendChild(option);
                            });
                            // Show the department container only for Program Chair
                            const role = document.querySelector('input[name="role"]:checked');
                            if (role && role.value === 'Program Chair') {
                                document.getElementById('departmentContainer').style.display = 'block';
                            }
                        })
                        .catch(error => console.error('Error fetching departments:', error));
                } else {
                    document.getElementById('departmentContainer').style.display = 'none'; // Hide if no college selected
                }
            });

            // Prevent form submission if required fields are not filled
            document.getElementById('signup').addEventListener('submit', function(event) {
                const college = document.getElementById('college').value;
                const departmentContainer = document.getElementById('departmentContainer');
                const department = departmentContainer.style.display === 'block' ? document.getElementById('department').value : '';

                // Check if college is selected only if it is visible
                if (document.getElementById('collegeDepartmentInfo').style.display === 'block' && !college) {
                    event.preventDefault(); // Prevent form submission
                    alert('Please select a college.');
                    document.getElementById('college').focus(); // Focus on the college select
                }

                // Additional checks for department if needed
                if (departmentContainer.style.display === 'block' && !department) {
                    event.preventDefault(); // Prevent form submission
                    alert('Please select a department.');
                    document.getElementById('department').focus(); // Focus on the department select
                }

                // Debugging: Log the visibility of college and department
                console.log('College selected:', college);
                console.log('Department selected:', department);
            });
        });
    </script>
</body>

</html>
