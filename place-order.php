<?php
session_start();
// Check if user visited cart first
if (!isset($_SESSION['visited_cart']) || $_SESSION['visited_cart'] !== true) {
    header('Location: cart.php');
    exit();
}
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch user info
try {
    $stmt = $conn->prepare("SELECT username, fullname, address, contact FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $user = [];
}

// Fetch cart items
try {
    $stmt = $conn->prepare("
        SELECT c.id as cart_item_id, c.quantity, p.id as product_id, p.name, p.price
        FROM cart_items c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $cart_items = [];
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$order_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_mode'])) {
    $payment_mode = $_POST['payment_mode'];
    if (empty($cart_items)) {
        $order_message = '<div class="alert alert-danger">Your cart is empty.</div>';
    } else {
        try {
            // Insert order
            $conn->beginTransaction();
            $stmt = $conn->prepare("INSERT INTO orders (user_id, order_date, status, total, payment_mode) VALUES (?, NOW(), 'Processing', ?, ?)");
            $stmt->execute([$user_id, $total, $payment_mode]);
            $order_id = $conn->lastInsertId();
            // Insert order_items
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cart_items as $item) {
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
            }
            // Clear user's cart
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $conn->commit();
            header('Location: index.php');
            exit();
        } catch (PDOException $e) {
            $conn->rollBack();
            $order_message = '<div class="alert alert-danger">Error placing order. Please try again.</div>';
        }
    }
}

$pageTitle = 'Place Order - Tech Bazaar PH';
include 'header.php';
?>
<div class="container mt-4 mb-5">
    <h2 class="mb-4">Checkout</h2>
    <?php if (!empty($order_message)) echo $order_message; ?>
    <form method="post">
        <div class="row g-4">
            <!-- Order Info (User, Address, etc.) -->
            <div class="col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Order Information</h5>
                        <hr>
                        <p><strong>Username:</strong> <?= htmlspecialchars($user['username'] ?? '') ?></p>
                        <p><strong>Full Name:</strong> <?= htmlspecialchars($user['fullname'] ?? '') ?></p>
                        <p><strong>Address:</strong> <?= htmlspecialchars($user['address'] ?? '') ?></p>
                        <p><strong>Contact:</strong> <?= htmlspecialchars($user['contact'] ?? '') ?></p>
                        <hr>
                        <h6>Products:</h6>
                        <ul class="list-group mb-2">
                            <?php foreach ($cart_items as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($item['name']) ?> <span>x<?= (int)$item['quantity'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total Amount:</span>
                            <span class="fw-bold">₱<?= number_format($total, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Payment & Cart (Mode of Payment) -->
            <div class="col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Payment Method</h5>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Select Payment Mode</label>
                            <select class="form-select" name="payment_mode" required>
                                <option value="">Choose...</option>
                                <option value="Gcash">Gcash</option>
                                <option value="PayPal">PayPal</option>
                                <option value="COD">Cash on Delivery (COD)</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-success btn-lg">Buy Now</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
