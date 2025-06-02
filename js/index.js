// index.js - extracted from index.php
document.addEventListener('DOMContentLoaded', function() {
  const swiper = new Swiper('.swiper', {
    loop: true,
    autoplay: {
      delay: 5000,
    },
    pagination: {
      el: '.swiper-pagination',
      clickable: true,
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
  });
      
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
        });
      }
      
      // Initialize cart count
      updateCartCount();
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


