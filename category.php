<?php
require_once 'config.php';

// Get filter parameters from URL
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$productsPerPage = 12; // Changed from 15 to match the API limit

// Add JavaScript to handle search and filter changes
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle search input changes
    const searchInput = document.querySelector('#search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchValue = this.value.trim();
            const category = document.querySelector('#category-filter').value;
            const sort = document.querySelector('#sort-filter').value;
            
            // Redirect with new parameters
            window.location.href = `?category=${category}&search=${encodeURIComponent(searchValue)}&sort=${sort}`;
        });
    }

    // Handle category filter changes
    const categoryFilter = document.querySelector('#category-filter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            const category = this.value;
            const search = document.querySelector('#search-input').value.trim();
            const sort = document.querySelector('#sort-filter').value;
            
            // Redirect with new parameters
            window.location.href = `?category=${category}&search=${encodeURIComponent(search)}&sort=${sort}`;
        });
    }

    // Handle sort filter changes
    const sortFilter = document.querySelector('#sort-filter');
    if (sortFilter) {
        sortFilter.addEventListener('change', function() {
            const sort = this.value;
            const category = document.querySelector('#category-filter').value;
            const search = document.querySelector('#search-input').value.trim();
            
            // Redirect with new parameters
            window.location.href = `?category=${category}&search=${encodeURIComponent(search)}&sort=${sort}`;
        });
    }
});
</script>
<?php

// Build SQL query based on filters
$whereClause = "";
$params = [];

// Add search filter first
if (!empty($search)) {
    $whereClause .= " AND (name LIKE :search OR description LIKE :search)";
    $searchTerm = "%" . $search . "%";
    $params[':search'] = $searchTerm;
    $params[':search2'] = $searchTerm;
}

// Add category filter if specified
if (!empty($category)) {
    $whereClause .= " AND category = :category";
    $params[':category'] = $category;
}

// Build SQL query based on filters
$whereClause = "";
$params = [];

// Add category filter if specified
if (!empty($category)) {
    $whereClause .= " AND category = :category";
    $params[':category'] = $category;
}

// Add search filter if specified
if (!empty($search)) {
    $whereClause .= " AND (name LIKE :search OR description LIKE :search)";
    $searchTerm = "%" . $search . "%";
    $params[':search'] = $searchTerm;
    $params[':search2'] = $searchTerm;
}

// Calculate pagination
$offset = ($page - 1) * $productsPerPage;

// Initialize pagination variables
$totalProducts = 0;
$totalPages = 1;

try {
    // Get total count for pagination
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE 1=1 $whereClause");
    $stmt->execute($params);
    $totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalProducts / $productsPerPage);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $totalProducts = 0;
    $totalPages = 1;
}

// Build order by clause based on sort parameter
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

// Fetch products from the database with pagination
try {
    $stmt = $conn->prepare("SELECT * FROM products WHERE 1=1 $whereClause $orderByClause LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $productsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    // Bind all other parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $products = [];
}

// Fetch all categories for the filter
try {
    $stmt = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $categories = [];
}
?>

<?php
$pageTitle = 'Products - Tech Bazaar PH';
include 'header.php';
?>
    
  <!-- Toast container for notifications -->
  <div id="toast-container"></div>

  <!-- Page content will change based on active view -->
  <div id="app-container">
    <!-- Add breadcrumb navigation -->
    <!-- Breadcrumb navigation -->
  <div class="container mt-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Shop</li>
      </ol>
    </nav>
  </div>

    <div class="container page-content">
      <!-- Search bar for the products page -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
            <form class="d-flex me-3" action="category.php" method="GET">
          <div class="input-group">
            <input class="form-control" type="search" name="search" placeholder="Search products..." aria-label="Search">
            <button class="btn btn-outline-primary" type="submit">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </form>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- New sidebar filter column -->
        <div class="col-lg-3 mb-4">
          <div class="filter-sidebar">
            <h4 class="filter-title">Filter Products</h4>
            
            <div class="mb-4">
              <h5 class="mb-3">Categories</h5>
              <form id="category-filters">
                <?php foreach ($categories as $cat): ?>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" 
                           value="<?php echo htmlspecialchars($cat); ?>" 
                           id="category-<?php echo strtolower(str_replace(' ', '-', $cat)); ?>"
                           name="categories[]"
                           <?php echo !empty($category) && $category === $cat ? 'checked' : ''; ?>>
                    <label class="form-check-label" 
                           for="category-<?php echo strtolower(str_replace(' ', '-', $cat)); ?>">
                      <?php echo htmlspecialchars($cat); ?>
                    </label>
                  </div>
                <?php endforeach; ?>
              </form>
            </div>

            <div class="mb-4">
              <h5 class="mb-3">Sort By</h5>
              <select class="form-select" id="sort-products">
                <option value="latest" <?php echo empty($_GET['sort']) ? 'selected' : ''; ?>>Latest Products</option>
                <option value="price-asc" <?php echo isset($_GET['sort']) && $_GET['sort'] === 'price-asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price-desc" <?php echo isset($_GET['sort']) && $_GET['sort'] === 'price-desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                <option value="name-asc" <?php echo isset($_GET['sort']) && $_GET['sort'] === 'name-asc' ? 'selected' : ''; ?>>Name: A to Z</option>
              </select>
            </div>

            <div class="mb-4">
              <button class="btn btn-primary w-100" id="apply-filters">
                <i class="fas fa-filter me-2"></i>Apply Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Main content column -->
        <div class="col-lg-9">
          <!-- Product listing grid -->
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (empty($products)): ?>
              <div class="col-12  text-center py-5 mx-auto">
                <div class="d-flex flex-column align-items-center justify-content-center">
                  <h4 class="text-muted mb-3">No products available<?php echo !empty($search) ? " matching your search" : ""; ?></h4>
                  <p class="text-muted mb-4">Try adjusting your search terms or filters to find what you're looking for.</p>
                  
                </div>
              </div>
            <?php else: ?>
              <?php foreach ($products as $product): ?>
              <div class="col">
              <div class="card product-card h-100">
            <a href="product.php?id=<?= $product['id'] ?>">
              <img src="assets/imgs/<?= htmlspecialchars($product['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
            </a>
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
              <p class="price">₱<?= number_format($product['price'], 2) ?></p>
              <?php if (!isset($_SESSION['user_id'])): ?>
              <a class="btn btn-primary w-100" href="product.php?id=<?= $product['id'] ?>">
                <i class="fas fa-cart-plus me-2"></i>Add to Cart
              </a>
              <?php else: ?>
              <a class="btn btn-primary w-100" href="product.php?id=<?= $product['id'] ?>">
                <i class="fas fa-cart-plus me-2"></i>Add to Cart
              </a>
              <?php endif; ?>
            </div>
                </div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          
          <!-- Pagination controls -->
          <div class="row mt-4">
            <div class="col-12">
              <nav aria-label="Product pagination" class="shop-pagination-fixed">
                <ul class="pagination justify-content-center">
                  <!-- Previous button -->
                  <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo $page > 1 ? "category.php?page=" . ($page - 1) . "&category=" . $category . "&sort=" . $sort : "javascript:void(0)"; ?>" 
                       tabindex="<?php echo $page <= 1 ? '-1' : '0'; ?>" 
                       aria-disabled="<?php echo $page <= 1 ? 'true' : 'false'; ?>">Previous</a>
                  </li>

                  <!-- Page numbers -->
                  <?php
                  $startPage = max(1, $page - 2);
                  $endPage = min($totalPages, $page + 2);
                  
                  if ($startPage > 1) {
                    echo '<li class="page-item"><a class="page-link" href="category.php?page=1&category=' . $category . '&sort=' . $sort . '">1</a></li>';
                    if ($startPage > 2) {
                      echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                  }
                  
                  for ($i = $startPage; $i <= $endPage; $i++) {
                    $active = $i == $page ? 'active' : '';
                    echo '<li class="page-item ' . $active . '">
                          <a class="page-link" href="category.php?page=' . $i . '&category=' . $category . '&sort=' . $sort . '">' . $i . '</a>
                        </li>';
                  }
                  
                  if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                      echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="category.php?page=' . $totalPages . '&category=' . $category . '&sort=' . $sort . '">' . $totalPages . '</a></li>';
                  }
                  ?>

                  <!-- Next button -->
                  <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo $page < $totalPages ? "category.php?page=" . ($page + 1) . "&category=" . $category . "&sort=" . $sort : "javascript:void(0)"; ?>" 
                       tabindex="<?php echo $page >= $totalPages ? '-1' : '0'; ?>" 
                       aria-disabled="<?php echo $page >= $totalPages ? 'true' : 'false'; ?>">Next</a>
                  </li>
                </ul>
              </nav>
            </div>
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
  <script src="js/shop.js"></script>         
</body>
</html>