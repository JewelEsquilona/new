<?php
$connectionFile = '../connection.php';
if (!file_exists($connectionFile)) {
    die("Connection file not found.");
}
include($connectionFile);
if (!$con) {
    die("Database connection failed: " . $con->errorInfo()[2]);
}


// Check access to this page
if (!hasAccess('alumni_list.php')) {
    header('Location: no_access.php'); // Redirect to an access denied page
    exit();
}

// Fetch colleges and handle dropdowns based on user role...
// (Include the previously provided code for fetching colleges, departments, and sections)

// Ensure filters are applied based on the user's role
if ($_SESSION['user_role'] === 'Registrar') {
    // Logic to allow filtering by college, department, and section
} elseif ($_SESSION['user_role'] === 'Dean') {
    // Logic to allow filtering by college and department
} elseif ($_SESSION['user_role'] === 'Program Chair') {
    // Logic to allow filtering by department
} elseif ($_SESSION['user_role'] === 'Alumni') {
    // Logic to filter by section only
}

// Fetch colleges
$collegesQuery = "SELECT DISTINCT College FROM `2024-2025`";
$collegesStmt = $con->prepare($collegesQuery);
$collegesStmt->execute();
$colleges = $collegesStmt->fetchAll(PDO::FETCH_COLUMN);

// Get filter values
$collegeFilter = isset($_GET['college']) ? $_GET['college'] : '';
$departmentFilter = isset($_GET['department']) ? $_GET['department'] : '';
$sectionFilter = isset($_GET['section']) ? $_GET['section'] : '';

// Prepare the query
$query = "SELECT 
    a.*, 
    e.Employment, 
    e.Employment_Status, 
    e.Present_Occupation, 
    e.Name_of_Employer, 
    e.Address_of_Employer, 
    e.Number_of_Years_in_Present_Employer, 
    e.Type_of_Employer, 
    e.Major_Line_of_Business,
    CONCAT('AL', LPAD(a.Alumni_ID_Number, 5, '0')) AS Alumni_ID_Number_Format
FROM `2024-2025` a
LEFT JOIN `2024-2025_ed` e 
    ON a.`Alumni_ID_Number` = e.`Alumni_ID_Number`
WHERE e.`Alumni_ID_Number` IS NULL OR e.`ID` = (SELECT MAX(`ID`) FROM `2024-2025_ed` WHERE `Alumni_ID_Number` = a.`Alumni_ID_Number`)
AND (:college IS NULL OR a.College = :college)
AND (:department IS NULL OR a.Department = :department)
AND (:section IS NULL OR a.Section = :section)";

$statement = $con->prepare($query);
$statement->bindParam(':college', $collegeFilter);
$statement->bindParam(':department', $departmentFilter);
$statement->bindParam(':section', $sectionFilter);
$statement->execute();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni List</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" crossorigin="anonymous" />
    <style>
        .filter-container {
            display: flex;
            gap: 10px; /* Space between dropdowns */
        }
        .form-select {
            width: auto; /* Adjust width */
            min-width: 150px; /* Minimum width for better visibility */
        }
    </style>
</head>
<body class="bg-content">
    <main class="dashboard d-flex">
        <?php include "component/sidebar.php"; ?>
        <div class="container-fluid px">
            <?php include "component/header.php"; ?>
            <div class="alumni-list-header d-flex justify-content-between align-items-center py-2">
                <div class="title h6 fw-bold">Alumni List</div>
                <div class="btn-add d-flex gap-3 align-items-center">
                    <div class="short">
                        <i class="far fa-sort"></i>
                    </div>
                    <?php include 'alumni_add.php'; ?>
                </div>
            </div>

            <!-- Filtering Dropdowns -->
            <div class="filter-container mb-3">
                <?php if ($_SESSION['user_role'] === 'Registrar' || $_SESSION['user_role'] === 'Dean' || $_SESSION['user_role'] === 'Program Chair'): ?>
                    <select id="collegeFilter" class="form-select">
                        <option value="">Select College</option>
                        <?php foreach ($colleges as $college): ?>
                            <option value="<?= htmlspecialchars($college) ?>" <?= $college === $collegeFilter ? 'selected' : '' ?>><?= htmlspecialchars($college) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <select id="departmentFilter" class="form-select" <?= ($_SESSION['user_role'] === 'Dean' || $_SESSION['user_role'] === 'Program Chair') ? '' : 'disabled' ?>>
                    <option value="">Select Department</option>
                    <?php if ($_SESSION['user_role'] === 'Dean' || $_SESSION['user_role'] === 'Program Chair'): ?>
                        <?php
                        // Fetch departments based on college filter for Dean or Program Chair
                        if ($collegeFilter) {
                            $departmentsQuery = "SELECT DISTINCT Department FROM `2024-2025` WHERE College = :college";
                            $departmentsStmt = $con->prepare($departmentsQuery);
                            $departmentsStmt->bindParam(':college', $collegeFilter);
                            $departmentsStmt->execute();
                            $departments = $departmentsStmt->fetchAll(PDO::FETCH_COLUMN);
                        } else {
                            $departments = [];
                        }
                        foreach ($departments as $department): ?>
                            <option value="<?= htmlspecialchars($department) ?>" <?= $department === $departmentFilter ? 'selected' : '' ?>><?= htmlspecialchars($department) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <select id="sectionFilter" class="form-select" <?= ($_SESSION['user_role'] === 'Alumni') ? 'disabled' : '' ?>>
                    <option value="">Select Section</option>
                    <?php if ($_SESSION['user_role'] === 'Dean' || $_SESSION['user_role'] === 'Program Chair'): ?>
                        <?php
                        // Fetch sections based on department filter
                        if ($departmentFilter) {
                            $sectionsQuery = "SELECT DISTINCT Section FROM `2024-2025` WHERE Department = :department";
                            $sectionsStmt = $con->prepare($sectionsQuery);
                            $sectionsStmt->bindParam(':department', $departmentFilter);
                            $sectionsStmt->execute();
                            $sections = $sectionsStmt->fetchAll(PDO::FETCH_COLUMN);
                        } else {
                            $sections = [];
                        }
                        foreach ($sections as $section): ?>
                            <option value="<?= htmlspecialchars($section) ?>" <?= $section === $sectionFilter ? 'selected' : '' ?>><?= htmlspecialchars($section) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="table-responsive table-container">
                <table class="table alumni_list table-borderless">
                    <thead>
                        <tr class="align-middle">
                            <th>ID</th>
                            <th>Alumni ID Number</th>
                            <th>Student Number</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>College</th>
                            <th>Department</th>
                            <th>Section</th>
                            <th>Year Graduated</th>
                            <th>Contact Number</th>
                            <th>Personal Email</th>
                            <th>Employment</th>
                            <th>Employment Status</th>
                            <th>Present Occupation</th>
                            <th>Name of Employer</th>
                            <th>Address of Employer</th>
                            <th>Number of Years in Present Employer</th>
                            <th>Type of Employer</th>
                            <th>Major Line of Business</th>
                            <th class="opacity">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($statement->rowCount() > 0): ?>
                            <?php while ($row = $statement->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['ID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Alumni_ID_Number_Format']); ?></td> 
                                    <td><?php echo htmlspecialchars($row['Student_Number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Last_Name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['First_Name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Middle_Name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['College']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Department']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Section']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Year_Graduated']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Contact_Number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Personal_Email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Employment']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Employment_Status']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Present_Occupation']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Name_of_Employer']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Address_of_Employer']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Number_of_Years_in_Present_Employer']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Type_of_Employer']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Major_Line_of_Business']); ?></td>
                                    <td>
                                        <a href="alumni_edit.php?Alumni_ID_Number=<?php echo $row['Alumni_ID_Number'] ?>"><i class="far fa-pen"></i></a>
                                        <a href="alumni_process.php?action=delete&alumni_id=<?php echo $row['Alumni_ID_Number']; ?>"><i class="far fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="20" class="text-center">No alumni records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/bootstrap.bundle.js"></script>

    <script>
        document.getElementById('collegeFilter').addEventListener('change', function() {
            const college = this.value;
            const departmentFilter = document.getElementById('departmentFilter');
            const sectionFilter = document.getElementById('sectionFilter');

            // Clear previous options
            departmentFilter.innerHTML = '<option value="">Select Department</option>';
            sectionFilter.innerHTML = '<option value="">Select Section</option>';
            sectionFilter.disabled = true;

            if (college) {
                // Fetch departments based on selected college
                fetch(`get_departments.php?college=${college}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(department => {
                            const option = document.createElement('option');
                            option.value = department.department;
                            option.textContent = department.department;
                            departmentFilter.appendChild(option);
                        });
                        departmentFilter.disabled = false;
                    });
            } else {
                departmentFilter.disabled = true;
            }
        });

        document.getElementById('departmentFilter').addEventListener('change', function() {
            const department = this.value;
            const sectionFilter = document.getElementById('sectionFilter');

            // Clear previous options
            sectionFilter.innerHTML = '<option value="">Select Section</option>';

            if (department) {
                // Fetch sections based on selected department
                fetch(`get_sections.php?department=${department}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(section => {
                            const option = document.createElement('option');
                            option.value = section;
                            option.textContent = section;
                            sectionFilter.appendChild(option);
                        });
                        sectionFilter.disabled = false;
                    });
            } else {
                sectionFilter.disabled = true;
            }
        });
    </script>
</body>
</html>
