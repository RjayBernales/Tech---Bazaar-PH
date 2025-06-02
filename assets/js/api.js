// Base URL for API endpoints
const API_URL = '/api';

// Authentication API
async function login(email, password) {
    try {
        const response = await fetch(`${API_URL}/auth.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=login&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        });
        return await response.json();
    } catch (error) {
        console.error('Login error:', error);
        return { success: false, message: 'Network error' };
    }
}

async function register(username, email, password) {
    try {
        const response = await fetch(`${API_URL}/auth.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=register&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        });
        return await response.json();
    } catch (error) {
        console.error('Registration error:', error);
        return { success: false, message: 'Network error' };
    }
}

async function logout() {
    try {
        const response = await fetch(`${API_URL}/auth.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=logout'
        });
        return await response.json();
    } catch (error) {
        console.error('Logout error:', error);
        return { success: false, message: 'Network error' };
    }
}

// Products API
async function getProducts(category = null) {
    try {
        let url = `${API_URL}/products.php`;
        if (category) {
            url += `?category=${encodeURIComponent(category)}`;
        }
        const response = await fetch(url);
        return await response.json();
    } catch (error) {
        console.error('Products error:', error);
        return { success: false, message: 'Network error' };
    }
}

async function getProduct(id) {
    try {
        const response = await fetch(`${API_URL}/products.php?id=${encodeURIComponent(id)}`);
        return await response.json();
    } catch (error) {
        console.error('Product error:', error);
        return { success: false, message: 'Network error' };
    }
}

// Cart API
async function getCart() {
    try {
        const response = await fetch(`${API_URL}/cart.php`);
        return await response.json();
    } catch (error) {
        console.error('Cart error:', error);
        return { success: false, message: 'Network error' };
    }
}

async function addToCart(productId, quantity = 1) {
    try {
        const response = await fetch(`${API_URL}/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add&product_id=${encodeURIComponent(productId)}&quantity=${encodeURIComponent(quantity)}`
        });
        return await response.json();
    } catch (error) {
        console.error('Add to cart error:', error);
        return { success: false, message: 'Network error' };
    }
}

async function updateCartItem(cartItemId, quantity) {
    try {
        const response = await fetch(`${API_URL}/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&cart_item_id=${encodeURIComponent(cartItemId)}&quantity=${encodeURIComponent(quantity)}`
        });
        return await response.json();
    } catch (error) {
        console.error('Update cart error:', error);
        return { success: false, message: 'Network error' };
    }
}

async function removeCartItem(cartItemId) {
    try {
        const response = await fetch(`${API_URL}/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&cart_item_id=${encodeURIComponent(cartItemId)}`
        });
        return await response.json();
    } catch (error) {
        console.error('Remove cart error:', error);
        return { success: false, message: 'Network error' };
    }
}
