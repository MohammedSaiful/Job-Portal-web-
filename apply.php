<?php
session_start();

// Check if user is logged in and is a job seeker
if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== 'jobseeker') {
    header("Location: index.html");
    exit();
}

$user_email = $_SESSION['user_email'];
$job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;

if ($job_id <= 0) {
    $_SESSION['apply_message'] = "<p style='color:red;'>Invalid job selected.</p>";
    header("Location: jobs.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "job_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($seeker_id);
$stmt->fetch();
$stmt->close();

// Check if already applied
$check = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND seeker_id = ?");
$check->bind_param("ii", $job_id, $seeker_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $_SESSION['apply_message'] = "<p style='color:orange;'>You have already applied for this job.</p>";
    $check->close();
    $conn->close();
    header("Location: jobs.php");
    exit();
}
$check->close();

// Insert application
$apply = $conn->prepare("INSERT INTO applications (job_id, seeker_id) VALUES (?, ?)");
$apply->bind_param("ii", $job_id, $seeker_id);

if ($apply->execute()) {
    $_SESSION['apply_message'] = "<p style='color:green;'>Application submitted successfully!</p>";
} else {
    $_SESSION['apply_message'] = "<p style='color:red;'>Failed to apply: " . $apply->error . "</p>";
}

$apply->close();
$conn->close();
header("Location: jobs.php");
exit();
?>
