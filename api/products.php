<?php
require_once '../config.php';
header('Content-Type: application/json');

// Get products with filters
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;

    $whereClause = "";
    $params = [];

    // Add category filter
    if (!empty($category)) {
        $whereClause .= " AND category = :category";
        $params[':category'] = $category;
    }

    // Add search filter
    if (!empty($search)) {
        $whereClause .= " AND (name LIKE :search OR description LIKE :search)";
        $searchTerm = "%" . $search . "%";
        $params[':search'] = $searchTerm;
        $params[':search2'] = $searchTerm;
    }

    // Build order by clause
    $orderByClause = "";
    switch ($sort) {
        case 'latest':
            $orderByClause = "ORDER BY id DESC";
            break;
        case 'price-asc':
            $orderByClause = "ORDER BY price ASC";
            break;
        case 'price-desc':
            $orderByClause = "ORDER BY price DESC";
            break;
        case 'name-asc':
            $orderByClause = "ORDER BY name ASC";
            break;
        default:
            $orderByClause = "ORDER BY id DESC";
            break;
    }

    try {
        // Get total count for pagination
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE 1=1 $whereClause");
        $stmt->execute($params);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Calculate offset
        $offset = ($page - 1) * $limit;

        // Fetch products with filters and pagination
        $stmt = $conn->prepare("SELECT * FROM products WHERE 1=1 $whereClause $orderByClause LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::FETCH_ASSOC);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / $limit)
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching products'
        ]);
    }
}

// Get single product
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching product']);
    }
}
?>
