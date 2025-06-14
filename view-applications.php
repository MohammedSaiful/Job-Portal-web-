<?php
session_start();

// Restrict access to employers
if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== 'employer') {
    header("Location: index.html");
    exit();
}

$employer_email = $_SESSION['user_email'];
$employer_id = null;

$conn = new mysqli("localhost", "root", "", "job_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get employer ID
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $employer_email);
$stmt->execute();
$stmt->bind_result($employer_id);
$stmt->fetch();
$stmt->close();

// Fetch jobs and applications
$jobs = [];

$sql = "
    SELECT 
        jobs.id AS job_id,
        jobs.title,
        jobs.description,
        applications.applied_at,
        seekers.name AS seeker_name,
        seekers.email AS seeker_email
    FROM jobs
    LEFT JOIN applications ON jobs.id = applications.job_id
    LEFT JOIN users AS seekers ON applications.seeker_id = seekers.id
    WHERE jobs.employer_id = ?
    ORDER BY jobs.id DESC, applications.applied_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $job_id = $row['job_id'];
    if (!isset($jobs[$job_id])) {
        $jobs[$job_id] = [
            'title' => $row['title'],
            'description' => $row['description'],
            'applications' => []
        ];
    }

    if ($row['seeker_name']) {
        $jobs[$job_id]['applications'][] = [
            'seeker_name' => $row['seeker_name'],
            'seeker_email' => $row['seeker_email'],
            'applied_at' => $row['applied_at']
        ];
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Applications Received</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: Arial, sans-serif;
            padding: 40px;
        }
        .container {
            max-width: 900px;
            margin: auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        .job {
            background-color: #1a1a1a;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255,255,255,0.1);
        }
        .job h3 {
            margin-top: 0;
        }
        .application {
            background-color: #262626;
            padding: 12px;
            margin-top: 10px;
            border-radius: 6px;
        }
        .application p {
            margin: 5px 0;
        }
        .no-apps {
            color: #aaa;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Applications Received</h2>

        <?php if (empty($jobs)): ?>
            <p>You haven't posted any jobs yet.</p>
        <?php else: ?>
            <?php foreach ($jobs as $job): ?>
                <div class="job">
                    <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>

                    <?php if (empty($job['applications'])): ?>
                        <p class="no-apps">No applications yet.</p>
                    <?php else: ?>
                        <?php foreach ($job['applications'] as $app): ?>
                            <div class="application">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($app['seeker_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($app['seeker_email']); ?></p>
                                <p><strong>Applied on:</strong> <?php echo htmlspecialchars($app['applied_at']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
