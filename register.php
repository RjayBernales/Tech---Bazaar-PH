<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'config.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contact'] ?? '');

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($fullname) || empty($address) || empty($contact)) {
        $error_message = 'Please fill in all fields';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address';
    } else if (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long';
    } else {
        try {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error_message = 'This email address is already registered.';
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, fullname, address, contact) VALUES (?, ?, ?, ?, ?, ?)");
                
                // Log the query for debugging
                error_log("Executing insert query: " . $stmt->queryString);
                
                if ($stmt->execute([$username, $email, $hashed_password, $fullname, $address, $contact])) {
                    // Get the user ID
                    $user_id = $conn->lastInsertId();

                    // Set session variables
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;

                    // Redirect to profile page
                    header('Location: index.php');
                    exit();
                } else {
                    $error_message = 'Failed to save user information. Please try again.';
                }
            }
        } catch (PDOException $e) {
            // Log the error for debugging
            error_log("Registration error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Show a more specific error message
            $error_message = 'Registration failed: ' . $e->getMessage();
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error_message = 'This email address is already registered.';
            } else if (strpos($e->getMessage(), 'users') !== false) {
                $error_message = 'There was an error saving your information. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Tech Bazaar PH</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }
    .auth-form {
      max-width: 500px;
      margin: 3rem auto;
      padding: 2.5rem;
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .auth-form h2 {
      text-align: center;
      color: var(--primary, #3498db);
      margin-bottom: 1.5rem;
      font-weight: 700;
    }
    .form-control:focus {
      box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
      border-color: var(--primary, #3498db);
    }
    .auth-footer {
      text-align: center;
      margin-top: 1rem;
    }
    .auth-footer a {
      color: var(--primary, #3498db);
      text-decoration: none;
      font-weight: 500;
    }
    .auth-footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="auth-form">
      <h2>Create an Account</h2>
      
      <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($error_message) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <form method="POST" id="register-form" novalidate>
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
          <div class="invalid-feedback">Please enter a username.</div>
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" placeholder="your.email@example.com" required>
          <div class="invalid-feedback">Please enter a valid email.</div>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
          <div class="invalid-feedback">Please enter a password.</div>
        </div>
        <div class="mb-3">
          <label for="fullname" class="form-label">Full Name</label>
          <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Your full name" required>
          <div class="invalid-feedback">Please enter your full name.</div>
        </div>
        <div class="mb-3">
          <label for="address" class="form-label">Address</label>
          <input type="text" class="form-control" id="address" name="address" placeholder="Your address" required>
          <div class="invalid-feedback">Please enter your address.</div>
        </div>
        <div class="mb-3">
          <label for="contact" class="form-label">Contact Number</label>
          <input type="tel" class="form-control" id="contact" name="contact" placeholder="Your contact number" required>
          <div class="invalid-feedback">Please enter your contact number.</div>
        </div>
        <div class="d-grid gap-2 mb-3">
          <button type="submit" class="btn btn-primary">Register</button>
        </div>
      </form>
      <div class="auth-footer">
        <p>Already have an account? <a href="login.php">Login here</a></p>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>