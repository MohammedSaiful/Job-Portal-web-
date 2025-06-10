<?php
session_start();

// Redirect if not logged in or not a job seeker
if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== 'jobseeker') {
    header("Location: index.html");
    exit();
}

$user_email = $_SESSION['user_email'];
$user_id = null;

$conn = new mysqli("localhost", "root", "", "job_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get seeker ID
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Fetch applications
$applications = [];
$sql = "SELECT jobs.title, jobs.description, jobs.location, jobs.salary, jobs.deadline, users.name AS employer_name, applications.applied_at 
        FROM applications
        JOIN jobs ON applications.job_id = jobs.id
        JOIN users ON jobs.employer_id = users.id
        WHERE applications.seeker_id = ?
        ORDER BY applications.applied_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Applications</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #000;
            color: #f1f1f1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 40px;
            font-size: 32px;
            color: #00bfff;
        }
        .application {
            background-color: #111;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 191, 255, 0.1);
            transition: transform 0.2s ease;
        }
        .application:hover {
            transform: translateY(-4px);
        }
        .application h3 {
            margin-top: 0;
            font-size: 24px;
            color: #00bfff;
        }
        .application p {
            margin: 8px 0;
            line-height: 1.5;
        }
        .label {
            font-weight: bold;
            color: #ccc;
        }
        .employer {
            color: #66ccff;
        }
        .date {
            font-size: 14px;
            color: #999;
            margin-top: 10px;
        }
        .no-apps {
            text-align: center;
            font-size: 18px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>My Applications</h2>

        <?php if (count($applications) === 0): ?>
            <p class="no-apps">You haven't applied for any jobs yet.</p>
        <?php else: ?>
            <?php foreach ($applications as $app): ?>
                <div class="application">
                    <h3><?php echo htmlspecialchars($app['title']); ?></h3>
                    <p><span class="label">Description:</span> <?php echo nl2br(htmlspecialchars($app['description'])); ?></p>
                    <p><span class="label">Location:</span> <?php echo htmlspecialchars($app['location']); ?></p>
                    <p><span class="label">Salary:</span> <?php echo htmlspecialchars($app['salary']); ?></p>
                    <?php if (!empty($app['deadline'])): ?>
                        <p><span class="label">Deadline:</span> <?php echo htmlspecialchars($app['deadline']); ?></p>
                    <?php endif; ?>
                    <p class="employer"><span class="label">Employer:</span> <?php echo htmlspecialchars($app['employer_name']); ?></p>
                    <p class="date">Applied on: <?php echo htmlspecialchars($app['applied_at']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
