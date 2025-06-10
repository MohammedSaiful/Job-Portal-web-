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
        <h2>User information</h2>
    </div>
    
     <?php
    session_start();

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['logemail']) && isset($_POST['logpwd'])) {
        $email = trim($_POST['logemail']);
        $password = trim($_POST['logpwd']);

        $conn = new mysqli("localhost", "root", "", "job_portal");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check user with plain text password (not recommended for production)
        $stmt = $conn->prepare("SELECT name, role FROM users WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($name, $role);
            $stmt->fetch();

            $_SESSION["user_name"] = $name;  //
            $_SESSION["user_email"] = $email;
            $_SESSION["user_role"] = $role;

            header("Location: dashboard.php");
            exit;
        } else {
            echo "<p style='color:red;' class='center-text'>Invalid email or password.</p>";
        }

        $stmt->close();
        $conn->close();
    }
    ?>


    <!-- Buttons -->
     
    <form method="post">
        <div class="center-form">
            <button type="button" onclick="window.location.href='index.html';" class="buttons">Back</button>
        </div>
    </form>
</div>
</div>
</body>
</html>
