<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Get order_id from query string
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger">Invalid order ID.</div>';
    exit();
}
$order_id = (int)$_GET['id'];

// Fetch order info (ensure it belongs to the user)
try {
    $stmt = $conn->prepare("
        SELECT o.id, o.order_date, o.status, o.total, o.payment_mode, u.fullname, u.address, u.contact
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $order = false;
}

// Fetch order items
$order_items = [];
if ($order) {
    try {
        $stmt = $conn->prepare("
            SELECT oi.quantity, oi.price, p.name AS product_name, p.image_url, p.description
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $order_items = [];
    }
}

if (!$order) {
    echo '<div class="alert alert-danger">Order does not exist or does not belong to you.</div>';
    exit();
}

$pageTitle = 'Order Details - Tech Bazaar PH';
include 'header.php';
?>



  <!-- Toast container for notifications -->
  <div id="toast-container"></div>

  <!-- Breadcrumb navigation -->
  <div class="container mt-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="orders.php">My Orders</a></li>
        <li class="breadcrumb-item active" aria-current="page">Order Details</li>
      </ol>
    </nav>
  </div>

  <!-- Main Content -->
  <div class="container page-content">
    <div class="order-summary">
      <h2>Order #<?= htmlspecialchars($order['id']) ?></h2>
      <div class="row">
        <div class="col-md-6">
          <p><strong>Date:</strong> <?= date('M d, Y', strtotime($order['order_date'])) ?></p>
          <p><strong>Status:</strong> <span class="badge bg-<?php
            $status = strtolower($order['status']);
            echo $status === 'delivered' ? 'success' : ($status === 'shipped' ? 'warning text-dark' : ($status === 'processing' ? 'info' : 'secondary'));
          ?>"><?= htmlspecialchars($order['status']) ?></span></p>
        </div>
        <div class="col-md-6">
          <p><strong>Shipping Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
          <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_mode']) ?></p>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header bg-white">
        <h4 class="mb-0">Order Items</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover order-detail-table">
            <thead class="table-light">
              <tr>
                <th>Product</th>
                <th>Name</th>
                <th>Unit Price</th>
                <th>Quantity</th>
                <th class="text-end">Subtotal</th>
              </tr>
            </thead>
            <tbody>
            <?php if (empty($order_items)): ?>
              <tr><td colspan="5" class="text-center">No products found for this order.</td></tr>
            <?php else: ?>
              <?php foreach ($order_items as $item): ?>
                <tr>
                  <td><img src="assets/imgs/<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" style="width: 80px; height: 80px; object-fit: contain;"></td>
                  <td><?= htmlspecialchars($item['product_name']) ?></td>
                  <td>₱<?= number_format($item['price'], 2) ?></td>
                  <td><?= (int)$item['quantity'] ?></td>
                  <td class="text-end">₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
            <tfoot class="table-light">
              <tr>
                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                <td class="text-end fw-bold">₱<?= number_format($order['total'], 2) ?></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header bg-white">
        <h4 class="mb-0">Order Timeline</h4>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush">
<?php
// Timeline steps and logic
$order_date = strtotime($order['order_date']);
$timeline = [
  [
    'label' => 'Order Placed',
    'icon' => 'fa-shopping-cart',
    'badge' => 'secondary',
    'date' => $order_date,
    'show' => true,
  ],
  [
    'label' => 'Payment Confirmed',
    'icon' => 'fa-credit-card',
    'badge' => 'warning text-dark',
    'date' => $order_date, // Same as placed for demo
    'show' => true,
  ],
  [
    'label' => 'Order Shipped',
    'icon' => 'fa-box',
    'badge' => 'info',
    'date' => ($order['status'] === 'Shipped' || $order['status'] === 'Out for Delivery' || $order['status'] === 'Delivered') ? $order_date + 86400 : null, // +1 day
    'show' => in_array($order['status'], ['Shipped', 'Out for Delivery', 'Delivered']),
  ],
  [
    'label' => 'Out for Delivery',
    'icon' => 'fa-truck',
    'badge' => 'primary',
    'date' => ($order['status'] === 'Out for Delivery' || $order['status'] === 'Delivered') ? $order_date + 2 * 86400 : null, // +2 days
    'show' => in_array($order['status'], ['Out for Delivery', 'Delivered']),
  ],
  [
    'label' => 'Order Delivered',
    'icon' => 'fa-check',
    'badge' => 'success',
    'date' => ($order['status'] === 'Delivered') ? $order_date + 3 * 86400 : null, // +3 days
    'show' => ($order['status'] === 'Delivered'),
  ],
];
foreach (array_reverse($timeline) as $step) {
  if ($step['show']) {
    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
    echo '<div><span class="badge bg-' . $step['badge'] . ' rounded-circle p-2 me-2"><i class="fas ' . $step['icon'] . '"></i></span>';
    echo $step['label'] . '</div>';
    echo '<small class="text-muted">' . ($step['date'] ? date('M d, Y - h:i A', $step['date']) : '-') . '</small>';
    echo '</li>';
  }
}
?>
        </ul>
      </div>
    </div>

    <div class="text-end mb-4">
      <a href="download-invoice.php?id=<?= urlencode($order['id']) ?>" class="btn btn-outline-secondary me-2">
        <i class="fas fa-file-pdf me-1"></i> Download Invoice
      </a>
      <a href="orders.php" class="btn btn-primary">
        <i class="fas fa-arrow-left me-1"></i> Back to Orders
      </a>
    </div>
  </div>

  <!-- Footer -->
  <?php 
  include 'footer.php'; 
  ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <script src="js/order-details.js"></script>
</body>
</html>