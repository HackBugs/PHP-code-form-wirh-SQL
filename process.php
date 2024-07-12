<?php
// Database connection parameters
$servername = "localhost";
$username = "root"; // Default username for localhost
$password = ""; // Default password for localhost
$dbname = "people_details";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Proceed with handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $first_name = filter_var(trim($_POST['first_name']), FILTER_SANITIZE_STRING);
    $last_name = filter_var(trim($_POST['last_name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone_number = filter_var(trim($_POST['phone_number']), FILTER_SANITIZE_STRING);

    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone_number) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Please fill in all fields correctly.";
        exit;
    }

    // Handle file upload
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["resume"]["name"]);
    $resumeFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a valid document
    $allowed_types = array("pdf", "doc", "docx");
    if (!in_array($resumeFileType, $allowed_types)) {
        echo "Only PDF, DOC, and DOCX files are allowed.";
        exit;
    }

    if (!move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
        echo "There was an error uploading your file.";
        exit;
    }

    // Prepare SQL statement to insert data into the database
    $stmt = $conn->prepare("INSERT INTO contacts (first_name, last_name, email, phone_number, resume) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone_number, $target_file);

    // Execute the prepared statement
    if ($stmt->execute()) {
        echo "Hello, " . $first_name . " " . $last_name . "! Your form has been submitted successfully.";
    } else {
        // Check for specific error message indicating duplicate entry
        if ($stmt->errno == 1062) {
            echo "Error: Duplicate entry. This email address may already exist in our records.";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    // Close statement
    $stmt->close();
} else {
    echo "Invalid request method.";
}

// Close connection
$conn->close();
?> 