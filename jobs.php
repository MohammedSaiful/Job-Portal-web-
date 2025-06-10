<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: index.html");
    exit();
}


$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];
$user_id = null;

$conn = new mysqli("localhost", "root", "", "job_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get current user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Fetch jobs
$jobs = [];
$result = $conn->query("SELECT jobs.id, jobs.title, jobs.description, jobs.location, jobs.salary, users.name AS employer_name 
                        FROM jobs 
                        JOIN users ON jobs.employer_id = users.id");


if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Jobs</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #000;
            color: #f0f0f0;
            padding: 40px;
        }
        .job-listing {
            max-width: 800px;
            margin: auto;
        }
        .job {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #333;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.05);
            margin-bottom: 20px;
        }
        .job h3 {
            margin-top: 0;
            color: #ffffff;
        }
        .job p {
            color: #dcdcdc;
        }
        .apply-button {
            margin-top: 10px;
            padding: 10px 18px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .apply-button:hover {
            background-color: #0056b3;
        }
        h2 {
            text-align: center;
            color: #ffffff;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="job-listing">
        <h2>Job Openings</h2>

        <?php
        if (isset($_SESSION['apply_message'])) {
            echo '<div style="margin-bottom: 15px;">' . $_SESSION['apply_message'] . '</div>';
            unset($_SESSION['apply_message']);
        }
        ?>
        <?php if (count($jobs) === 0): ?>
            <p>No job postings available at the moment.</p>
        <?php else: ?>
            <?php foreach ($jobs as $job): ?>
                <div class="job">
                    <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                    <p><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary']); ?></p>
                    <p><strong>Posted by:</strong> <?php echo htmlspecialchars($job['employer_name']); ?></p>

                    <?php if ($user_role === 'jobseeker'): ?>
                        <form method="post" action="apply.php">
                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                            <button type="submit" class="apply-button">Apply</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
