<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];



try {
    // Fetch user details
    $stmt = $conn->prepare("SELECT username, email, fullname, address, contact, profile_pic FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: login.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Profile fetch error: " . $e->getMessage());
    header('Location: error.php');
    exit();
}
?>

<?php
$pageTitle = 'Profile - Tech Bazaar PH';
include 'header.php';
?>
  <div class="container">
    <div class="profile-container">
      <div class="text-center mb-4">
  <?php
    $profilePic = (!empty($user['profile_pic']) && file_exists($user['profile_pic'])) ? $user['profile_pic'] : 'https://via.placeholder.com/150';
  ?>
  <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture" id="profile-image" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
</div>

      <!-- Profile Information -->
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Contact Number</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($user['contact']) ?>" readonly>
          </div>
        </div>
      </div>

      <!-- Address -->
      <div class="mb-4">
        <label class="form-label">Address</label>
        <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($user['address']) ?></textarea>
      </div>

      <!-- Profile Actions -->
      <div class="d-flex justify-content-between align-items-center">
        <a href="edit-profile.php" class="btn btn-primary">
          <i class="fas fa-edit me-1"></i>Edit Profile
        </a>
        <a href="orders.php" class="btn btn-secondary">
          <i class="fas fa-box me-1"></i>My Orders
        </a>
      </div>
    </div>
  </div>

  <?php 
  include 'footer.php'; 
  ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
</body>
</html>
