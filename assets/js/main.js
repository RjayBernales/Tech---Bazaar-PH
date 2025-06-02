// Cart functionality and logout handler
document.addEventListener('DOMContentLoaded', function() {
    // Logout button click handler
    document.getElementById('logout-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
        }
    });

    // Cart functionality
    // Add to cart button click handler
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            let quantity = 1;
            // If on product.php, get quantity from input
            const qtyInput = document.getElementById('quantity');
            if (qtyInput) {
                quantity = parseInt(qtyInput.value) || 1;
            }
            // Send request to add to cart
            fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&product_id=${encodeURIComponent(productId)}&quantity=${encodeURIComponent(quantity)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Product added to cart!', 'success');
                    updateCartCount();
                } else {
                    showToast(data.message || 'Failed to add to cart', 'error');
                }
            })
            .catch(error => {
                showToast('Error adding to cart', 'error');
            });
        });
    });

    // Update cart count
    function updateCartCount() {
        fetch('api/cart.php?action=count')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('cart-count').textContent = data.count;
                }
            });
    }

    // Show toast notifications
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-header">
                <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        `;
        
        document.getElementById('toast-container').appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }

    // Initialize cart count
    updateCartCount();
});
