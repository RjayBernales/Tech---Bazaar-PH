<?php
session_start();
require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}
$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid order ID');
}
$order_id = (int)$_GET['id'];

// Fetch order info
$stmt = $conn->prepare("SELECT o.*, u.fullname, u.address, u.contact FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    die('Order not found or access denied');
}
// Fetch order items
$stmt = $conn->prepare("SELECT oi.*, p.name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build HTML for invoice
$html = "<!DOCTYPE html>\n";
$html .= "<html lang='en'>\n";
$html .= "<head>\n";
$html .= "    <meta charset='UTF-8'>\n";
$html .= "    <title>Invoice for Order #" . htmlspecialchars($order_id) . "</title>\n";
$html .= "    <style>\n";
$html .= "        body { font-family: 'DejaVu Sans', Arial, sans-serif; margin: 40px; }\n";
$html .= "        h2 { margin-bottom: 0; }\n";
$html .= "        .info, .items { margin-bottom: 20px; }\n";
$html .= "        table { border-collapse: collapse; width: 100%; }\n";
$html .= "        th, td { border: 1px solid #ccc; padding: 8px; }\n";
$html .= "        th { background: #eee; }\n";
$html .= "        .right { text-align: right; }\n";
$html .= "    </style>\n";
$html .= "</head>\n";
$html .= "<body>\n";
$html .= "    <h2>Invoice for Order #" . htmlspecialchars($order_id) . "</h2>\n";
$html .= "    <div class='info'>\n";
$html .= "        <strong>Name:</strong> " . htmlspecialchars($order['fullname']) . "<br>\n";
$html .= "        <strong>Address:</strong> " . htmlspecialchars($order['address']) . "<br>\n";
$html .= "        <strong>Contact:</strong> " . htmlspecialchars($order['contact']) . "<br>\n";
$html .= "        <strong>Order Date:</strong> " . date('M d, Y H:i', strtotime($order['order_date'])) . "<br>\n";
$html .= "        <strong>Status:</strong> " . htmlspecialchars($order['status']) . "<br>\n";
$html .= "        <strong>Payment Mode:</strong> " . htmlspecialchars($order['payment_mode']) . "<br>\n";
$html .= "    </div>\n";
$html .= "    <div class='items'>\n";
$html .= "        <table>\n";
$html .= "            <thead>\n";
$html .= "                <tr>\n";
$html .= "                    <th>Name</th>\n";
$html .= "                    <th>Unit Price</th>\n";
$html .= "                    <th>Quantity</th>\n";
$html .= "                    <th>Subtotal</th>\n";
$html .= "                </tr>\n";
$html .= "            </thead>\n";
$html .= "            <tbody>\n";
            
foreach ($order_items as $item) {
    $html .= '<tr>
        <td>' . htmlspecialchars($item['name']) . '</td>
        <td class="right">₱' . number_format($item['price'], 2) . '</td>
        <td class="right">' . (int)$item['quantity'] . '</td>
        <td class="right">₱' . number_format($item['price'] * $item['quantity'], 2) . '</td>
    </tr>';
}
$html .= '</tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="right"><strong>Total:</strong></td>
                    <td class="right"><strong>₱' . number_format($order['total'], 2) . '</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <p>Thank you for shopping with Tech Bazaar PH!</p>
</body>
</html>';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('invoice_order_' . $order_id . '.pdf', ['Attachment' => 1]);
exit;
