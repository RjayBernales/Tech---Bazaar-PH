<?php
session_start();
require_once 'config.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = 'Please fill in all fields';
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Success login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Redirect home page
                header('Location: index.php');
                exit();
            } else {
                $error_message = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $error_message = 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Tech Bazaar PH'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
  <style id="app-style">
    :root {
      --primary: #3498db;
      --secondary: #2ecc71;
      --dark: #2c3e50;
      --light: #ecf0f1;
      --danger: #e74c3c;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      color: var(--dark);
    }
    
    .navbar-brand {
      font-weight: 700;
      color: var(--primary);
    }
    
    .nav-link {
      font-weight: 500;
    }
    
    .btn-primary {
      background-color: var(--primary);
      border-color: var(--primary);
    }
    
    .btn-primary:hover {
      background-color: #2980b9;
      border-color: #2980b9;
    }
    
    .btn-success {
      background-color: var(--secondary);
      border-color: var(--secondary);
    }
    
    .btn-success:hover {
      background-color: #27ae60;
      border-color: #27ae60;
    }
    
    .card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: none;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    
    
    .product-card {
      height: 100%;
    }
    
    .product-card img {
      height: 200px;
      object-fit: contain;
      padding: 1rem;
      background-color: white;
    }
    
    .product-card .card-title {
      font-weight: 600;
      font-size: 1rem;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    
    .price {
      color: var(--primary);
      font-weight: 700;
      font-size: 1.25rem;
    }
    
    .hero-section {
      background: linear-gradient(135deg, #3498db, #8e44ad);
      color: white;
      padding: 4rem 0;
      margin-bottom: 2rem;
      border-radius: 0 0 20px 20px;
    }
    
    .hero-title {
      font-weight: 700;
      font-size: 2.5rem;
      margin-bottom: 1rem;
    }
    
    .hero-subtitle {
      font-weight: 400;
      margin-bottom: 2rem;
      font-size: 1.2rem;
      opacity: 0.9;
    }
    
    footer {
      background-color: var(--dark);
      color: white;
      padding: 3rem 0;
      margin-top: 4rem;
    }
    
    footer a {
      color: rgba(255,255,255,0.8);
      text-decoration: none;
    }
    
    footer a:hover {
      color: white;
    }
    
    .footer-heading {
      font-weight: 600;
      margin-bottom: 1.5rem;
      font-size: 1.2rem;
    }
    
    .auth-form {
      max-width: 450px;
      margin: 3rem auto;
      padding: 2.5rem;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      background-color: white;
    }
    
    .auth-form h2 {
      font-weight: 700;
      margin-bottom: 1.5rem;
      text-align: center;
      color: var(--primary);
    }
    
    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
    }
    
    .form-label {
      font-weight: 500;
    }
    
    .auth-form .btn {
      padding: 0.6rem 0;
      font-weight: 600;
    }
    
    .auth-footer {
      text-align: center;
      margin-top: 1.5rem;
    }
    
    .auth-footer a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
    }
    
    .auth-footer a:hover {
      text-decoration: underline;
    }
    
    .auth-divider {
      display: flex;
      align-items: center;
      margin: 1.5rem 0;
    }
    
    .auth-divider::before, 
    .auth-divider::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid #dee2e6;
    }
    
    .auth-divider span {
      padding: 0 1rem;
      color: #6c757d;
      font-size: 0.9rem;
    }
    
    .social-login {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    
    .social-login .btn {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      transition: all 0.3s ease;
    }
    
    .social-login .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .social-login .btn-facebook {
      background-color: #3b5998;
    }
    
    .social-login .btn-google {
      background-color: #db4437;
    }
    
    .social-login .btn-twitter {
      background-color: #1da1f2;
    }
    
    .auth-logo {
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .auth-logo i {
      font-size: 2.5rem;
      color: var(--primary);
    }
    
    #toast-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1051;
    }
    
    .swiper {
      width: 100%;
      height: 400px;
      margin-bottom: 30px;
    }
    
    .swiper-slide img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 12px;
    }
    
    .breadcrumb {
      margin-bottom: 2rem;
      padding: 0.75rem 1rem;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .form-check-input:checked {
      background-color: var(--primary);
      border-color: var(--primary);
    }
    
    .badge-cart {
      position: absolute;
      top: -5px;
      right: -5px;
      font-size: 0.65rem;
      background-color: var(--danger);
    }
    
    .section-title {
      position: relative;
      font-weight: 700;
      margin-bottom: 2rem;
      padding-bottom: 0.5rem;
    }
    
    .section-title:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 50px;
      height: 3px;
      background-color: var(--primary);
    }
    
    /* Add these new styles for the About Us page */
    .about-section .card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      height: 100%;
    }
    
    .about-section .card:hover {
      transform: scale(1.05);
      box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    
    .about-section .card h2 {
      padding: 1.25rem 1rem 0.5rem;
      color: var(--primary);
    }
    
    .team-member img {
      border-radius: 50%;
      width: 180px;
      height: 180px;
      margin: 0 auto 1rem;
      display: block;
      object-fit: cover;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .team-member {
      text-align: center;
      margin-bottom: 2rem;
    }
    /* End of new styles */
    
    /* Animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fade-in {
      animation: fadeIn 0.6s ease forwards;
    }
    
    .page-content {
      min-height: 60vh;
    }
    
    .table thead th { border-bottom: 2px solid #dee2e6; }
    .badge { font-size: 0.85rem; }
  </style>
</head>
<body>
  <?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($error_message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h2 class="card-title text-center mb-4">Login</h2>
            <form method="POST" id="loginForm">
              <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
              </div>
              <div class="mb-3">
                <button type="submit" class="btn btn-primary w-100">Login</button>
              </div>
            </form>
            <hr>
            <div class="text-center">
              <p>Don't have an account? <a href="register.php" class="text-decoration-none">Register</a></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>