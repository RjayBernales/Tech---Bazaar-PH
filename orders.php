<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch orders for the logged-in user
try {
    $stmt = $conn->prepare("SELECT id, order_number, order_date, status, total FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Orders fetch error: " . $e->getMessage());
    $orders = [];
}
?>
<?php
$pageTitle = 'My Orders - Tech Bazaar PH';
include 'header.php';
?>
  <!-- Toast container for notifications -->
  <div id="toast-container"></div>

  <!-- Breadcrumb navigation -->
  <div class="container mt-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">My Orders</li>
      </ol>
    </nav>
  </div>

  <!-- Main Content -->
  <div class="container page-content">
    <h2 class="mb-4">My Orders</h2>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Date</th>
            <th>Status</th>
            <th>Total</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
<?php if (empty($orders)): ?>
          <tr><td colspan="5" class="text-center">No orders found.</td></tr>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td>#ORD-<?php echo date('Y', strtotime($order['order_date'])); ?>-<?php echo htmlspecialchars($order['order_number']); ?></td>
              <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
              <td>
                <?php
                  $status = htmlspecialchars($order['status']);
                  $badge = 'secondary';
                  if ($status === 'Delivered') $badge = 'success';
                  elseif ($status === 'Shipped') $badge = 'warning text-dark';
                  elseif ($status === 'Processing') $badge = 'info';
                ?>
                <span class="badge bg-<?php echo $badge; ?>"><?php echo $status; ?></span>
              </td>
              <td>₱<?php echo number_format($order['total'], 2); ?></td>
              <td><a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">View</a></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Footer -->
<?php
include 'footer.php';
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script src="js/orders.js"></script>        
        
</body>
</html>