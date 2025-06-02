<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}


// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Handle add to cart via POST
$cart_message = '';
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'], $_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    try {
        // Check if item already exists in cart
        $stmt = $conn->prepare("SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
        $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing_item) {
            // Update quantity
            $new_quantity = $existing_item['quantity'] + $quantity;
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_quantity, $existing_item['id']]);
        } else {
            // Add new item
            $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
        }
        $cart_message = '<div class="alert alert-success mt-3">Added to cart!</div>';
    } catch (PDOException $e) {
        $cart_message = '<div class="alert alert-danger mt-3">Error adding to cart.</div>';
    }
}

// Fetch latest products
try {
    $stmt = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 8");
    $featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $featuredProducts = [];
}

// Fetch categories
try {
    $stmt = $conn->query("SELECT DISTINCT category FROM products");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = [];
}
?>

<?php
$pageTitle = 'Home - Tech Bazaar PH';
include 'header.php';
?>

  <!-- Toast container for notifications -->
  <div id="toast-container"></div>

  <!-- Page content will change based on active view -->
  <div id="app-container">
    <!-- Home page content is shown by default -->
    <section class="hero-section">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6 animate-fade-in">
            <h1 class="hero-title">New Arrivals are Here!</h1>
            <p class="hero-subtitle">Affordable Tech You Can Trust</p>
            <a href="category.html" class="btn btn-light btn-lg">Shop Now <i class="fas fa-arrow-right ms-2"></i></a>
          </div>
          <div class="col-lg-6 d-none d-lg-block animate-fade-in">
            <img src="https://cdn.pixabay.com/photo/2017/12/11/18/13/phone-3012895_1280.png" alt="Latest Tech Gadgets" class="img-fluid">
          </div>
        </div>
      </div>
    </section>

    <div class="container page-content">
      <div class="row">
        <div class="col-12">
          <h2 class="section-title">Featured Products</h2>
        </div>
      </div>
      
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php if (!empty($cart_message)) echo $cart_message; ?>
<?php foreach ($featuredProducts as $product): ?>
    <div class="col animate-fade-in" style="animation-delay: <?= ($product['id'] - 1) * 100 ?>ms;">
      <div class="card product-card h-100">
        <a href="product.php?id=<?= $product['id'] ?>">
          <img src="assets/imgs/<?= htmlspecialchars($product['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
        </a>
        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
          <p class="price">₱<?= number_format($product['price'], 2) ?></p>
          <?php if (!isset($_SESSION['user_id'])): ?>
          <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#loginModal">
            <i class="fas fa-cart-plus me-2"></i>Add to Cart
          </button>
          <?php else: ?>
          <form method="post" class="d-inline">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" name="add_to_cart" class="btn btn-primary w-100">
              <i class="fas fa-cart-plus me-2"></i>Add to Cart
            </button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
<?php endforeach; ?>
      </div>

      <div class="row mt-5">
        <div class="col-12">
          <h2 class="section-title">Latest Offers</h2>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="swiper">
            <div class="swiper-wrapper">
              <div class="swiper-slide">
                <img src="assets/imgs/latest1.jpg" alt="Tech Promo">
              </div>
              <div class="swiper-slide">
                <img src="assets/imgs/latest2.jpg" alt="Smartphone Sale">
              </div>
              <div class="swiper-slide">
                <img src="assets/imgs/latest3.webp" alt="Gadget Discount">
              </div>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
          </div>
        </div>
      </div>
      
      <div class="row mt-5">
        <div class="col-md-4 animate-fade-in">
          <div class="card mb-4">
            <div class="card-body text-center">
              <i class="fas fa-truck-fast text-primary mb-3" style="font-size: 2rem;"></i>
              <h5 class="card-title">Fast Delivery</h5>
              <p class="card-text">Get your tech gadgets delivered within 1-3 business days nationwide.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 animate-fade-in" style="animation-delay: 0.1s;">
          <div class="card mb-4">
            <div class="card-body text-center">
              <i class="fas fa-shield-halved text-primary mb-3" style="font-size: 2rem;"></i>
              <h5 class="card-title">Warranty Guaranteed</h5>
              <p class="card-text">All products come with official Philippine warranty coverage.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 animate-fade-in" style="animation-delay: 0.2s;">
          <div class="card mb-4">
            <div class="card-body text-center">
              <i class="fas fa-credit-card text-primary mb-3" style="font-size: 2rem;"></i>
              <h5 class="card-title">Secure Payment</h5>
              <p class="card-text">Multiple payment options including GCash, PayPal, and Cash on Delivery.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Customer Reviews -->
  <div class="container page-content">
      <div class="row mt-5">
        <div class="col-12">
          <h2 class="section-title">What Our Customers Say</h2>
        </div>
      </div>
    
      <div class="row">
        <div class="col-12">
          <div class="swiper reviews-swiper">
            <div class="swiper-wrapper">
              <?php
              try {
                  $stmt = $conn->query("
                      SELECT id, name, designation, avatar_url, review_text, rating 
                      FROM reviews 
                      ORDER BY rating DESC, created_at DESC 
                      LIMIT 4
                  ");
                  $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

                  foreach ($reviews as $review):
              ?>
              <div class="swiper-slide">
                <div class="card text-center review-card">
                  <div class="card-body">
                    <div class="mb-3">
                      <i class="fas fa-quote-left fa-2x text-primary"></i>
                    </div>
                    <p class="card-text mb-4">"<?= htmlspecialchars($review['review_text']) ?>"</p>
                    <div class="d-flex align-items-center justify-content-center gap-3">
                      <img src="<?= htmlspecialchars($review['avatar_url']) ?>" class="rounded-circle" alt="<?= htmlspecialchars($review['name']) ?>" style="width: 60px; height: 60px; object-fit: cover;">
                      <div>
                        <h5 class="mb-1"><?= htmlspecialchars($review['name']) ?></h5>
                        <p class="mb-0"><?= htmlspecialchars($review['designation']) ?></p>
                      </div>
                    </div>
                    <div class="mt-3">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                      <?php endfor; ?>
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
              <?php 
                  } catch (PDOException $e) {
                      echo "<div class='alert alert-danger'>Error loading reviews: " . $e->getMessage() . "</div>";
                  }
              ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
          </div>
        </div>
      </div>
  </div>


  <!-- Login Modal -->
  <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="loginModalLabel">Please Login</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>You need to be logged in to add items to your cart.</p>
          <div class="d-flex justify-content-center">
            <a href="login.php" class="btn btn-primary me-2">Login</a>
            <a href="register.php" class="btn btn-outline-primary">Register</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php 
  include 'footer.php'; 
  ?>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <script src="js/index.js"></script>      
</body>
</html>