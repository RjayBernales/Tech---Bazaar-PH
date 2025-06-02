<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize variables
$success_msg = '';
$error_msg = '';

// Fetch current user data
try {
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contact'] ?? '');

    // Handle profile picture upload
    $profile_pic_sql = '';
    $profile_pic_val = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
        $img = $_FILES['profile_pic'];
        if ($img['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($img['type'], $allowed)) {
                $ext = pathinfo($img['name'], PATHINFO_EXTENSION);
                $newName = 'profile_' . $user_id . '_' . time() . '.' . $ext;
                $dest = 'uploads/' . $newName;
                if (move_uploaded_file($img['tmp_name'], $dest)) {
                    $profile_pic_sql = ', profile_pic=?';
                    $profile_pic_val = $dest;
                } else {
                    $error_msg = 'Failed to upload image.';
                }
            } else {
                $error_msg = 'Invalid file type. Only JPG, PNG, GIF allowed.';
            }
        } else {
            $error_msg = 'Image upload error.';
        }
    }

    // Basic validation
    if (empty($username) || empty($email) || empty($fullname) || empty($address) || empty($contact)) {
        $error_msg = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Please enter a valid email address.';
    } elseif (empty($error_msg)) {
        try {
            // Check for duplicate email (exclude current user)
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                $error_msg = 'This email address is already used by another account.';
            } else {
                $sql = "UPDATE users SET username=?, email=?, fullname=?, address=?, contact=?$profile_pic_sql WHERE id=?";
                $params = [$username, $email, $fullname, $address, $contact];
                if ($profile_pic_sql) $params[] = $profile_pic_val;
                $params[] = $user_id;
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                header('Location: profile.php');
                exit();
            }
        } catch (PDOException $e) {
            $error_msg = 'Update failed: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Edit Profile - Tech Bazaar PH';
include 'header.php';
?>
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <h2 class="mb-4 text-center">Edit Profile</h2>
          <?php if ($success_msg): ?>
            <div class="alert alert-success"> <?= htmlspecialchars($success_msg) ?> </div>
          <?php endif; ?>
          <?php if ($error_msg): ?>
            <div class="alert alert-danger"> <?= htmlspecialchars($error_msg) ?> </div>
          <?php endif; ?>
          <form method="POST" enctype="multipart/form-data" autocomplete="off">
          <div class="mb-3 text-center">
            <?php
              $profilePic = (!empty($user['profile_pic']) && file_exists($user['profile_pic'])) ? $user['profile_pic'] : 'https://via.placeholder.com/150';
            ?>
            <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture" id="profile-image" class="rounded-circle mb-2" style="width: 120px; height: 120px; object-fit: cover;">
            <div>
              <label for="profile-upload" class="btn btn-outline-primary btn-sm mb-0">
                <i class="fas fa-upload me-1"></i>Change Photo
              </label>
              <input type="file" name="profile_pic" id="profile-upload" class="d-none" accept="image/*" onchange="previewProfileImage(this)">
            </div>
          </div>
          <script src="js/edit-profile.js"></script>
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mb-3">
              <label for="fullname" class="form-label">Full Name</label>
              <input type="text" class="form-control" id="fullname" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
            </div>
            <div class="mb-3">
              <label for="address" class="form-label">Address</label>
              <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($user['address']) ?>" required>
            </div>
            <div class="mb-3">
              <label for="contact" class="form-label">Contact Number</label>
              <input type="text" class="form-control" id="contact" name="contact" value="<?= htmlspecialchars($user['contact']) ?>" required>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <a href="profile.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
              <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php 
  include 'footer.php'; 
  ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
