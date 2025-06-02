<?php
session_start();
require_once 'config.php';
$message_sent = false;
$error_message = '';
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])
) {
    if (!isset($_SESSION['user_id'])) {
        $error_message = 'You must be logged in to send a message.';
    } else {
        $user_id = $_SESSION['user_id'];
        $message = trim($_POST['message']);
        if ($message === '') {
            $error_message = 'Message cannot be empty.';
        } else {
            try {
                $stmt = $conn->prepare('INSERT INTO message (user_id, message, created_at) VALUES (?, ?, NOW())');
                $stmt->execute([$user_id, $message]);
                $message_sent = true;
            } catch (PDOException $e) {
                $error_message = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>
<?php
$pageTitle = 'About Us - Tech Bazaar PH';
include 'header.php';
?>

  <!-- Toast container for notifications -->
  <div id="toast-container"></div>

  <!-- Breadcrumb navigation -->
  <div class="container mt-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">About Us</li>
      </ol>
    </nav>
  </div>

  <!-- Page Header -->
  <header class="about-header">
    <div class="container">
      <h1>About Tech Bazaar PH</h1>
      <p class="lead">Your trusted partner for affordable and quality tech gadgets in the Philippines</p>
    </div>
  </header>

  <!-- Main Content -->
  <div class="container page-content">
    <!-- Mission & History Section -->
    <section class="about-section">
      <div class="row">
        <div class="col-lg-6 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h2>Our Mission</h2>
              <p>At Tech Bazaar PH, we believe that everyone deserves access to quality technology at fair prices. Our mission is to bridge the digital divide by offering affordable tech gadgets without compromising on quality or customer service.</p>
              <p>We carefully curate our product selection to ensure that every item we sell meets our standards for performance, durability, and value. Our team rigorously tests each product to ensure it delivers the experience our customers deserve.</p>
            </div>
          </div>
        </div>
        <div class="col-lg-6 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h2>Our History</h2>
              <p>Tech Bazaar PH was founded in 2018 by a group of tech enthusiasts who saw a gap in the Philippine market for affordable yet reliable tech products. What started as a small online store operating out of a garage has grown into one of the country's most trusted e-commerce platforms for technology products.</p>
              <p>Over the years, we've expanded our product range and improved our services based on customer feedback. Today, we serve thousands of customers across the Philippines, helping them access the technology they need to work, learn, and connect.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Values Section -->
    <section class="about-section">
      <div class="row">
        <div class="col-12 text-center mb-4">
          <h2 class="text-center">Our Core Values</h2>
          <p class="lead">The principles that guide everything we do</p>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="fas fa-medal fa-3x text-primary mb-3"></i>
              <h4>Quality</h4>
              <p>We never compromise on the quality of our products and services. Every item in our inventory is selected for its reliability and performance.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="fas fa-hand-holding-usd fa-3x text-primary mb-3"></i>
              <h4>Affordability</h4>
              <p>We believe that great technology shouldn't break the bank. We work hard to offer competitive prices without cutting corners.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="fas fa-users fa-3x text-primary mb-3"></i>
              <h4>Customer Focus</h4>
              <p>Our customers are at the heart of everything we do. We're committed to providing exceptional service and support.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Team Section -->
    <section class="about-section">
      <div class="row">
        <div class="col-12 text-center mb-4">
          <h2 class="text-center">Meet Our Team</h2>
          <p class="lead">The passionate people behind Tech Bazaar PH</p>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="team-member">
            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Miguel Santos" class="img-fluid">
            <h4>Miguel Santos</h4>
            <p>Founder & CEO</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="team-member">
            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sofia Reyes" class="img-fluid">
            <h4>Sofia Reyes</h4>
            <p>Operations Director</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="team-member">
            <img src="https://randomuser.me/api/portraits/men/68.jpg" alt="David Lim" class="img-fluid">
            <h4>David Lim</h4>
            <p>Tech Specialist</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="team-member">
            <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Anna Cruz" class="img-fluid">
            <h4>Anna Cruz</h4>
            <p>Customer Service Lead</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Form Section -->
    <section class="about-section">
      <div class="row">
        <div class="col-12 text-center mb-4">
          <h2 class="text-center">Get In Touch</h2>
          <p class="lead">Have questions or feedback? We'd love to hear from you!</p>
        </div>
        <div class="col-lg-8 offset-lg-2">
          <?php if (
    isset($message_sent) && $message_sent
): ?>
  <div class="alert alert-success">Your message has been sent. Thank you!</div>
<?php elseif (isset($error_message) && $error_message): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>
<form class="contact-form" id="contact-form" method="post">
  <div class="mb-3">
    <label for="message" class="form-label">Your Message</label>
    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
  </div>
  <button type="submit" class="btn btn-primary btn-lg w-100">Send Message</button>
</form>
        </div>
      </div>
    </section>
  </div>

  <?php include 'footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <script src="js/about.js"></script>
      
</body>
</html>