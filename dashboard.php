<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: index.html");
    exit();
}

$name = $_SESSION['user_name'];
$email = $_SESSION['user_email'];
$role = $_SESSION['user_role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fdfdfd;
            text-align: center;
            padding: 40px;
        }
        .dashboard {
            max-width: 700px;
            height:300px;
            margin: 60px auto;
            background: black;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        h2 {
            margin-bottom: 10px;
            color: #fdfdfd;
        }
        .info {
            margin-bottom: 30px;
            color: #fdfdfd;
        }
        .role-display {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 14px;
            color: #fdfdfd;;
        }
        .nav-buttons button {
            margin: 10px;
            padding: 12px 24px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            background-color: #007BFF;
            color: white;
            cursor: pointer;
        }
        .nav-buttons button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="role-display">
            Role: <?php echo htmlspecialchars($role); ?>
        </div>

        <h2>Welcome, <?php echo htmlspecialchars($name); ?>!</h2>
        <p class="info">Email: <?php echo htmlspecialchars($email); ?></p>

        <div class="nav-buttons">
            <?php if ($role === 'employer'): ?>
                <button onclick="location.href='post-job.php'">Post a Job</button>
                <!-- Optional: View applicants -->
            <?php elseif ($role === 'jobseeker'): ?>
                <button onclick="location.href='jobs.php'">View Jobs</button>
                <button onclick="location.href='apply.php'">Apply for a Job</button>
                <button onclick="location.href='my-applications.php'">My Applications</button>
            <?php endif; ?>

            <button onclick="location.href='index.html'">Logout</button>
        </div>
    </div>
</body>
</html>
