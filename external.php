<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm User Information</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="main-container">
<div class="container">
    <div class="center-text">
        <h2>User Information Confirmation</h2>
    </div>

<?php
session_start();

$registration_done = false;

if (isset($_POST['submit'])) {
    // Sanitize user input
    $uname = htmlspecialchars($_POST['fname']);
    $email = htmlspecialchars($_POST['email']);
    $dob = htmlspecialchars($_POST['DOB']);
    $country = htmlspecialchars($_POST['country']);
    $pass = $_POST['pwd'];              // Store original password
    $confipass = $_POST['confirm_pwd'];
    $gender = isset($_POST['Gender']) ? htmlspecialchars($_POST['Gender']) : null;
    $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : "";
    $role = isset($_POST['registerUserType']) ? htmlspecialchars($_POST['registerUserType']) : "jobseeker";

    if ($uname && $email && $dob && $pass && $confipass && $country && $gender && $role) {

        echo "<p class='center-text'>Name: $uname</p>";
        echo "<p class='center-text'>Email: $email</p>";
        echo "<p class='center-text'>Date of Birth: $dob</p>";
        echo "<p class='center-text'>Country: $country</p>";
        echo "<p class='center-text'>Gender: $gender</p>";
        echo "<p class='center-text'>Description: " . nl2br($description) . "</p>";

        // Save in session without hashing password
        $_SESSION['temp_uname'] = $uname;
        $_SESSION['temp_email'] = $email;
        $_SESSION['temp_dob'] = $dob;
        $_SESSION['temp_country'] = $country;
        $_SESSION['temp_pass'] = $pass;          // plain password saved
        $_SESSION['temp_gender'] = $gender;
        $_SESSION['temp_description'] = $description;
        $_SESSION['temp_role'] = $role;
    } else {
        echo "<p class='center-text' style='color:red;'>Missing required fields.</p>";
    }
}

if (isset($_POST['confirm'])) {
    $conn = new mysqli("localhost", "root", "", "job_portal");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $uname = $_SESSION['temp_uname'];
    $email = $_SESSION['temp_email'];
    $dob = $_SESSION['temp_dob'];
    $country = $_SESSION['temp_country'];
    $pass = $_SESSION['temp_pass'];          // original password here
    $gender = $_SESSION['temp_gender'];
    $description = $_SESSION['temp_description'];
    $role = $_SESSION['temp_role'];

    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "<p class='center-text' style='color:red;'>This email is already registered.</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, dob, country, gender, description, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $uname, $email, $pass, $dob, $country, $gender, $description, $role);

        if ($stmt->execute()) {
            echo "<p class='center-text' style='color:green;'>Registration successful!</p>";
            session_unset();
        } else {
            echo "<p class='center-text' style='color:red;'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }

    $checkStmt->close();
    $conn->close();
    $registration_done = true;
}
?>


    <!-- Confirmation Buttons -->
    <form method="post">
        <div class="center-form">
            <button type="button" onclick="window.location.href='index.html';" class="buttons">Back</button>
            <?php if (!$registration_done): ?>
                <button type="submit" name="confirm" class="buttons">Confirm</button>
            <?php endif; ?>
        </div>
    </form>
</div>
</div>
</body>
</html>
