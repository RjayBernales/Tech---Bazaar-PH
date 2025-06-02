// orders.js - extracted from orders.php
document.addEventListener('DOMContentLoaded', function() {
  // Update cart count
  function updateCartCount() {
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
      cartCount.textContent = Math.floor(Math.random() * 5) + 1;
    }
  }
  // Function to show toast notifications
  function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast show align-items-center text-white bg-${type} border-0 mb-2`;
    // ... (rest of the JS from orders.php)
  }
  // ... (rest of the JS from orders.php)
});
