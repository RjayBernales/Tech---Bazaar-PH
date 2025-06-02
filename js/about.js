// about.js - extracted from about.php
// Initialize form submission
// (from about.php)
document.addEventListener('DOMContentLoaded', function() {
  const contactForm = document.getElementById('contact-form');
  if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
      // ... (rest of the JS from about.php)
    });
  }
});
e.preventDefault();
          
// Get form values
const name = document.getElementById('name').value;
const email = document.getElementById('email').value;
const subject = document.getElementById('subject').value;
const message = document.getElementById('message').value;

// In a real app, this would send the data to a server
console.log('Form submitted:', { name, email, subject, message });

// Show success message
showToast('Your message has been sent successfully. We will get back to you soon!', 'success');

// Reset form
contactForm.reset();
  

// Update cart count on page load
updateCartCount();
     

// Function to show toast notifications (reused from main app)
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

// Update cart count (simulated for prototype - reused from main app)
function updateCartCount() {
const cartCount = document.getElementById('cart-count');
// For prototype, we'll just show a random small number
cartCount.textContent = Math.floor(Math.random() * 5) + 1;
}