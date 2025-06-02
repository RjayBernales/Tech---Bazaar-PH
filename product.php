<?php
require_once 'config.php';

// Get product ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: category.php');
    exit;
}
$product_id = (int)$_GET['id'];

// Fetch product details
try {
    $stmt = $conn->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        header('Location: category.php');
        exit;
    }
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}

// Fetch related products (random 4, excluding current)
try {
    $relatedStmt = $conn->prepare('SELECT * FROM products WHERE id != ? ORDER BY RAND() LIMIT 4');
    $relatedStmt->execute([$product_id]);
    $relatedProducts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $relatedProducts = [];
}

// Handle add to cart via PHP POST
$cart_message = '';
if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'], $_POST['product_id'], $_POST['quantity'])) {
    $product_id_post = (int)$_POST['product_id'];
    $quantity = max(1, (int)$_POST['quantity']);
    try {
        // Check if item already exists in cart
        $stmt = $conn->prepare("SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $product_id_post]);
        $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing_item) {
            // Update quantity
            $new_quantity = $existing_item['quantity'] + $quantity;
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_quantity, $existing_item['id']]);
        } else {
            // Add new item
            $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $product_id_post, $quantity]);
        }
        $cart_message = '<div class="alert alert-success mt-3">Added to cart!</div>';
    } catch (PDOException $e) {
        $cart_message = '<div class="alert alert-danger mt-3">Error adding to cart.</div>';
    }
}

?>
<?php
$pageTitle = isset($product['name']) ? $product['name'] . ' - Product Details' : 'Product Details';
include 'header.php';
?>
    <div class="container py-5">
        <div class="row">
            <div class="col-md-5">
                <img src="assets/imgs/<?= htmlspecialchars($product['image_url']) ?>" class="img-fluid rounded shadow" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            <div class="col-md-7">
                <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="mb-0"><?= htmlspecialchars($product['name']) ?></h2>
            </div>
                <h2 class="text-muted fw-bold fs-4 ms-3 d-flex justify-content-start">₱<?= number_format($product['price'], 2) ?></h2>
                
                <?php if (!empty($cart_message)) echo $cart_message; ?>
<div class="mt-4">
    <form method="post" class="d-flex align-items-end gap-2 mb-2">
        <div>
            <input type="number" name="quantity" id="quantity" class="form-control form-control-sm" style="width: 70px; display:inline-block;" value="1" min="1" max="<?= isset($product['stock_quantity']) ? $product['stock_quantity'] : 0 ?>" required>
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        </div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <button type="submit" name="add_to_cart" class="btn btn-primary align-self-end" style="height:38px;"><i class="fas fa-cart-plus me-2"></i>Add to Cart</button>
        <?php else: ?>
            <button type="button" class="btn btn-primary align-self-end" data-bs-toggle="modal" data-bs-target="#loginModal" style="height:38px;"><i class="fas fa-cart-plus me-2"></i>Add to Cart</button>
        <?php endif; ?>
    </form>
    <div class="mb-2">
        <small class="text-muted d-flex justify-content-start">Available: <?= isset($product['stock_quantity']) ? $product['stock_quantity'] : 0 ?></small>
    </div>
</div>
    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                <?php if (isset($_GET['added'])): ?>
                    <div class="alert alert-success mt-3">Added to cart!</div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Related Products -->
        <div class="row mt-5">
            <div class="col-12">
                <h4>Related Products</h4>
            </div>
            <?php foreach ($relatedProducts as $rel): ?>
                <div class="col-6 col-md-3 mb-4">
                    <div class="card h-100 d-flex flex-column">
    <a href="product.php?id=<?= $rel['id'] ?>">
        <img src="assets/imgs/<?= htmlspecialchars($rel['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($rel['name']) ?>">
    </a>
    <div class="card-body d-flex flex-column flex-grow-1">
        <h6 class="card-title mb-1"><?= htmlspecialchars($rel['name']) ?></h6>
        <p class="price mb-2">₱<?= number_format($rel['price'], 2) ?></p>
        <div class="mt-auto">
            <a href="product.php?id=<?= $rel['id'] ?>" class="btn btn-outline-primary btn-sm w-100">View</a>
        </div>
    </div>
</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
