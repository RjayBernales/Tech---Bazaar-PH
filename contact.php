<?php
require_once 'config.php';

// Handle form submission
$message_sent = false;
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $subject && $message) {
        try {
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            if ($user_id) {
                $stmt = $conn->prepare("INSERT INTO message (user_id, name, email, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$user_id, $name, $email, $subject, $message]);
            } else {
                $stmt = $conn->prepare("INSERT INTO message (user_id, name, email, subject, message, created_at) VALUES (NULL, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $email, $subject, $message]);
            }
            $message_sent = true;
        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error_message = 'Please fill in all fields.';
    }
}
?>
<?php
$pageTitle = 'Contact - Tech Bazaar PH';
include 'header.php';
?>

  <!-- Toast container for notifications -->
  <div id="toast-container"></div>

  <!-- Breadcrumb navigation -->
  <div class="container mt-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php ">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Contact</li>
      </ol>
    </nav>
  </div>

  <!-- Page Header -->
  <header class="about-header">
    <div class="container">
      <h1>Contact Us</h1>
      <p class="lead">Have questions or feedback? We'd love to hear from you!</p>
    </div>
  </header>

  <!-- Main Content -->
  <div class="container page-content">
    <div class="row">
      <div class="col-12">
        <?php if ($message_sent): ?>
  <div class="alert alert-success">Thank you for contacting us! Your message has been sent.</div>
<?php elseif ($error_message): ?>
  <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<form class="contact-form" id="contact-form" method="post" action="">

          <div class="mb-3">
            <label for="name" class="form-label">Your Name</label>
            <input type="text" class="form-control" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
          </div>
          <div class="mb-3">
            <label for="subject" class="form-label">Subject</label>
            <input type="text" class="form-control" id="subject" name="subject" required value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
          </div>
          <div class="mb-3">
            <label for="message" class="form-label">Your Message</label>
            <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-lg w-100">Send Message</button>
        </form>
      </div>
    </div>
  </div>

  <?php
  include 'footer.php';
  ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <script src="js/contact.js"></script>

</body>
</html>