// contact.js - extracted from contact.php
document.addEventListener('DOMContentLoaded', function() {
  // Get references to DOM elements
  const cartItemsContainer = document.getElementById('cart-items');
  if (!cartItemsContainer) {
    console.error('Cart items container not found!');
    return;
  }
  const emptyCartMessage = document.getElementById('empty-cart');
  const cartSummary = document.getElementById('cart-summary');
  // ... (rest of the JS from contact.php)
});
