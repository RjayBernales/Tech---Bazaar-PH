<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'], $_POST['cart_item_id'], $_POST['quantity'])) {
    $cart_item_id = (int)$_POST['cart_item_id'];
    $quantity = max(1, (int)$_POST['quantity']);
    try {
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_item_id, $user_id]);
        $message = '<div class="alert alert-success mt-2">Quantity updated.</div>';
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger mt-2">Error updating quantity.</div>';
    }
}

// Handle item removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'], $_POST['cart_item_id'])) {
    $cart_item_id = (int)$_POST['cart_item_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_item_id, $user_id]);
        $message = '<div class="alert alert-success mt-2">Item removed from cart.</div>';
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger mt-2">Error removing item.</div>';
    }
}

// Fetch cart items for the logged-in user (after update/remove)
try {
    $stmt = $conn->prepare("
        SELECT c.id as cart_item_id, c.quantity, p.id as product_id, p.name, p.price, p.image_url
        FROM cart_items c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Cart fetch error: " . $e->getMessage());
    $cart_items = [];
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<?php
$pageTitle = 'Shopping Cart - Tech Bazaar PH';
include 'header.php';
?>

<!-- Breadcrumb navigation -->
<div class="container mt-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">My Orders</li>
      </ol>
    </nav>
  </div>
<div class="container mt-4 mb-5">
    <h2 class="mb-4">Shopping Cart</h2>
    <?php if (!empty($message)) echo $message; ?>
    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Image</th>
                            <th scope="col">Product</th>
                            <th scope="col">Price</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Subtotal</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($cart_items)): ?>
                        <tr><td colspan="6" class="text-center">Your cart is empty.</td></tr>
                    <?php else: ?>
                        <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td style="width:90px"><img src="assets/imgs/<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid rounded" style="max-width:70px;"></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>₱<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <form method="post" class="d-flex align-items-center" style="gap:5px;">
                                    <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo (int)$item['quantity']; ?>" min="1" class="form-control form-control-sm" style="width:70px;">
                                    <button type="submit" name="update_quantity" class="btn btn-sm btn-outline-primary">Update</button>
                                </form>
                            </td>
                            <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Remove this item from cart?');">
                                    <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                    <button type="submit" name="remove_item" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-12 d-flex justify-content-lg-end">
            <div class="card shadow-sm" style="min-width:320px;max-width:380px;width:100%;">
                <div class="card-body">
                    <h5 class="card-title">Cart Total</h5>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total</span>
                        <span class="fw-bold fs-5">₱<?php echo number_format($total, 2); ?></span>
                    </div>
                    <form action="place-order.php" method="post">
                        <button type="submit" class="btn btn-primary w-100" <?php if (empty($cart_items)) echo 'disabled'; ?>>Place Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
