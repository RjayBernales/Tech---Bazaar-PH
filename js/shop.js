// shop.js - extracted from shop.php
document.addEventListener('DOMContentLoaded', function() {
  // Handle search input changes
  const searchInput = document.querySelector('#search-input');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const searchValue = this.value.trim();
      const category = document.querySelector('#category-filter').value;
      const sort = document.querySelector('#sort-filter').value;
      window.location.href = `?category=${category}&search=${encodeURIComponent(searchValue)}&sort=${sort}`;
    });
  }

// Add to Cart functionality
const addToCartButtons = document.querySelectorAll('.add-to-cart');
addToCartButtons.forEach(button => {
button.addEventListener('click', function() {
  const productId = this.getAttribute('data-product-id');
  showToast('Product added to cart', 'success');
  updateCartCount();
});
});

// Logout button
const logoutBtn = document.getElementById('logout-btn');
if (logoutBtn) {
logoutBtn.addEventListener('click', function() {
  showToast('You have been logged out', 'info');
  // In a real app, this would redirect to login page after logout
// Initialize cart count
updateCartCount();
});
}
      
// Sort products functionality
const sortSelect = document.getElementById('sort-products');
if (sortSelect) {
  sortSelect.addEventListener('change', function() {
    // Store sort order in localStorage but don't apply yet
    localStorage.setItem('sort', this.value);
  });
}

// Category filter functionality
const categoryFilters = document.getElementById('category-filters');
if (categoryFilters) {
  categoryFilters.addEventListener('change', function(e) {
    // Store category in localStorage but don't apply yet
    const selected = Array.from(this.querySelectorAll('input:checked'));
    if (selected.length > 0) {
      localStorage.setItem('category', selected[0].value);
    } else {
      localStorage.removeItem('category');
    }
  });
}

// Search form submission
const searchForm = document.getElementById('product-search-form');
if (searchForm) {
  searchForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const searchTerm = document.getElementById('product-search').value;
    window.location.href = `category.php?search=${encodeURIComponent(searchTerm)}`;
  });
}

// Apply filters button
const applyFiltersBtn = document.getElementById('apply-filters');
if (applyFiltersBtn) {
  applyFiltersBtn.addEventListener('click', function() {
    // Get category and sort from localStorage
    const category = localStorage.getItem('category') || '';
    const sort = localStorage.getItem('sort') || 'latest';
    
    // Build URL parameters
    const params = new URLSearchParams();
    if (category) {
      params.append('category', category);
    }
    if (sort !== 'latest') {
      params.append('sort', sort);
    }
    
    // Update URL and reload
    const newUrl = `category.php?${params.toString()}`;
    window.location.href = newUrl;
  });
}

// Format numbers with commas
function numberFormat(value) {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value);
}
});

// Function to show toast notifications
function showToast(message, type = 'info') {
const toastContainer = document.getElementById('toast-container');

const toast = document.createElement('div');
toast.className = `toast show align-items-center text-white bg-${type} border-0 mb-2`;
toast.setAttribute('role', 'alert');
toast.setAttribute('aria-live', 'assertive');
toast.setAttribute('aria-atomic', 'true');

const toastContent = document.createElement('div');
toastContent.className = 'd-flex';

const toastBody = document.createElement('div');
toastBody.className = 'toast-body';
toastBody.textContent = message;

const closeButton = document.createElement('button');
closeButton.type = 'button';
closeButton.className = 'btn-close btn-close-white me-2 m-auto';
closeButton.setAttribute('data-bs-dismiss', 'toast');
closeButton.setAttribute('aria-label', 'Close');

closeButton.addEventListener('click', function() {
  toast.remove();
});


toastContent.appendChild(toastBody);
toastContent.appendChild(closeButton);
toast.appendChild(toastContent);

toastContainer.appendChild(toast);

// Auto remove after 3 seconds
setTimeout(() => {
  toast.remove();
}, 3000);
}

// Update cart count (simulated for prototype)
function updateCartCount() {
  const cartCount = document.getElementById('cart-count');
  // For prototype, we'll just show a random small number
  cartCount.textContent = Math.floor(Math.random() * 5) + 1;
}
