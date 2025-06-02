<?php
require_once '../config.php';
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

// Get cart items
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare("
            SELECT c.*, p.name, p.price, p.image_url 
            FROM cart_items c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total = 0;
        foreach ($cart_items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        echo json_encode([
            'success' => true, 
            'cart_items' => $cart_items, 
            'total' => $total
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching cart']);
    }
}

// Add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 1;
    
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
        
        echo json_encode(['success' => true, 'message' => 'Added to cart']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error adding to cart']);
    }
}

// Update cart item quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $cart_item_id = $_POST['cart_item_id'];
    $quantity = $_POST['quantity'];
    
    try {
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $stmt->execute([$quantity, $cart_item_id]);
        echo json_encode(['success' => true, 'message' => 'Quantity updated']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating quantity']);
    }
}

// Remove cart item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    $cart_item_id = $_POST['cart_item_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ?");
        $stmt->execute([$cart_item_id]);
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error removing item']);
    }
}
?>
