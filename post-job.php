<?php
session_start();

// Redirect if not logged in or not employer
if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== 'employer') {
    header("Location: index.html");
    exit();
}

$employer_email = $_SESSION['user_email'];
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $salary = trim($_POST['salary']);

    if ($title && $description && $location && $salary) {
        $conn = new mysqli("localhost", "root", "", "job_portal");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Get employer id from email
        $stmtUser = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmtUser->bind_param("s", $employer_email);
        $stmtUser->execute();
        $stmtUser->bind_result($employer_id);
        if (!$stmtUser->fetch()) {
            $message = "<p style='color:red;'>Employer not found.</p>";
            $stmtUser->close();
            $conn->close();
        } else {
            $stmtUser->close();

            // Insert job with employer_id
            $stmt = $conn->prepare("INSERT INTO jobs (employer_id, title, description, location, salary) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $employer_id, $title, $description, $location, $salary);

            if ($stmt->execute()) {
                $message = "<p style='color:green;'>Job posted successfully!</p>";
            } else {
                $message = "<p style='color:red;'>Error: " . $stmt->error . "</p>";
            }
            $stmt->close();
            $conn->close();
        }
    } else {
        $message = "<p style='color:red;'>Please fill in all fields.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Posting</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fdfdfd;
            padding: 40px;
        }
        .form-container {
            max-width: 700px;
            margin: auto;
            background: black;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #fdfdfd;
        }
        form label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
        }
        form input, form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            resize: vertical;
        }
        form button {
            margin-top: 20px;
            padding: 12px 24px;
            font-size: 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Post a Job</h2>
        <div class="message"><?php echo $message; ?></div>
        <form method="post">
            <label for="title">Job Title</label>
            <input type="text" name="title" id="title" required>

            <label for="description">Job Description</label>
            <textarea name="description" id="description" rows="5" required></textarea>

            <label for="location">Location</label>
            <input type="text" name="location" id="location" required>

            <label for="salary">Salary</label>
            <input type="text" name="salary" id="salary" required>

            <label for="deadline">Application Deadline</label>
            <input type="date" name="deadline" id="deadline" required>

            <button type="submit">Submit Job</button>
        </form>
    </div>
</body>
</html>
