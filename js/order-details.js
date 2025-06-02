// order-details.js - extracted from order-details.php
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
      
      // Get the order ID from URL parameters
      function getOrderIdFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id');
      }
      
      // Initialize page
      function initPage() {
        updateCartCount();
        
        // In a real application, you would fetch the order data based on the ID
        const orderId = getOrderIdFromUrl();
        if (orderId) {
          // Here you would make an API call to get order details
          console.log(`Fetching details for order ID: ${orderId}`);
        }
      }
      
      // Initialize the page
      initPage();
    });
          

