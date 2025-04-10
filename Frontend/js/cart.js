// Import dummy data
import { dummyProducts } from './dummy-data.js';

// Global variables
let cart = [];
let promoCode = null;
const TAX_RATE = 0.05; // 5% tax
const SHIPPING_FLAT_RATE = 50; // ₹50 flat shipping rate
const FREE_SHIPPING_THRESHOLD = 500; // Free shipping for orders over ₹500

// DOM Elements
document.addEventListener('DOMContentLoaded', function() {
    console.log('Cart JS loaded');
    
    // Load cart from localStorage and server
    loadCart();
    
    // Update cart UI
    updateCartUI();
    
    // Setup event listeners
    setupEventListeners();
});

// Load cart data
function loadCart() {
    // First try to load from localStorage
    const localCart = localStorage.getItem('farm_fresh_market_cart');
    if (localCart) {
        cart = JSON.parse(localCart);
    }
    
    // Then fetch from server to ensure data is in sync
    fetchCartFromServer();
}

// Fetch cart data from server
function fetchCartFromServer() {
    fetch('../../Backend/php/cart.php?action=get')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.cart) {
                // Merge server cart with local cart
                mergeServerAndLocalCart(data.cart);
                updateCartUI();
            }
        })
        .catch(error => {
            console.error('Error fetching cart:', error);
        });
}

// Merge server cart with local cart
function mergeServerAndLocalCart(serverCart) {
    // If server cart is empty but local cart has items, use local cart
    if (serverCart.length === 0 && cart.length > 0) {
        // Update server with local cart
        syncCartToServer();
        return;
    }
    
    // If local cart is empty but server has items, use server cart
    if (cart.length === 0 && serverCart.length > 0) {
        cart = serverCart;
        saveCartToLocalStorage();
        return;
    }
    
    // If both have items, merge them
    const mergedCart = [];
    const allItems = [...cart, ...serverCart];
    
    // Group by product ID and sum quantities
    const productMap = {};
    
    allItems.forEach(item => {
        if (!productMap[item.id]) {
            // Get full product details from dummy data
            const productDetails = dummyProducts.find(p => p.id === item.id);
            
            productMap[item.id] = {
                id: item.id,
                quantity: 0,
                name: productDetails ? productDetails.name : item.name,
                price: productDetails ? productDetails.price : item.price,
                image: productDetails ? productDetails.image : item.image
            };
        }
        
        productMap[item.id].quantity += item.quantity;
    });
    
    // Convert map back to array
    for (const id in productMap) {
        mergedCart.push(productMap[id]);
    }
    
    cart = mergedCart;
    saveCartToLocalStorage();
    syncCartToServer();
}

// Save cart to localStorage
function saveCartToLocalStorage() {
    localStorage.setItem('farm_fresh_market_cart', JSON.stringify(cart));
}

// Sync cart to server
function syncCartToServer() {
    fetch('../../Backend/php/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=sync&cart=${encodeURIComponent(JSON.stringify(cart))}`
    })
    .then(response => response.json())
    .catch(error => {
        console.error('Error syncing cart:', error);
    });
}

// Update cart UI
function updateCartUI() {
    const cartItemsContainer = document.getElementById('cart-items');
    const cartSummaryContainer = document.getElementById('cart-summary');
    const cartEmptyContainer = document.getElementById('cart-empty');
    const cartContainer = document.getElementById('cart-container');
    const cartCountElement = document.getElementById('cart-count');
    
    // Update cart count in header
    if (cartCountElement) {
        cartCountElement.textContent = cart.reduce((total, item) => total + item.quantity, 0);
    }
    
    // If we're not on the cart page, return
    if (!cartItemsContainer) return;
    
    // Check if cart is empty
    if (cart.length === 0) {
        if (cartEmptyContainer) cartEmptyContainer.style.display = 'block';
        if (cartContainer) cartContainer.style.display = 'none';
        return;
    }
    
    // Cart has items
    if (cartEmptyContainer) cartEmptyContainer.style.display = 'none';
    if (cartContainer) cartContainer.style.display = 'flex';
    
    // Render cart items
    renderCartItems(cartItemsContainer);
    
    // Render cart summary
    renderCartSummary(cartSummaryContainer);
}

// Render cart items
function renderCartItems(container) {
    if (!container) return;
    
    container.innerHTML = '';
    
    cart.forEach(item => {
        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';
        cartItem.dataset.productId = item.id;
        
        cartItem.innerHTML = `
            <div class="cart-item-image">
                <img src="${item.image}" alt="${item.name}">
            </div>
            <div class="cart-item-details">
                <h3 class="cart-item-name">${item.name}</h3>
                <div class="cart-item-price">₹${item.price.toFixed(2)}</div>
                <div class="cart-item-actions">
                    <div class="cart-quantity">
                        <button class="decrease-quantity" data-product-id="${item.id}">-</button>
                        <input type="number" class="quantity-input" value="${item.quantity}" min="1" readonly>
                        <button class="increase-quantity" data-product-id="${item.id}">+</button>
                    </div>
                    <button class="remove-item" data-product-id="${item.id}">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                </div>
            </div>
        `;
        
        container.appendChild(cartItem);
    });
    
    // Add event listeners to the newly created elements
    addCartItemEventListeners();
}

// Render cart summary
function renderCartSummary(container) {
    if (!container) return;
    
    const { subtotal, tax, shipping, discount, total } = calculateCartTotals();
    
    container.innerHTML = `
        <h2>Order Summary</h2>
        <div class="summary-row">
            <span>Subtotal</span>
            <span>₹${subtotal.toFixed(2)}</span>
        </div>
        <div class="summary-row">
            <span>Tax (5%)</span>
            <span>₹${tax.toFixed(2)}</span>
        </div>
        <div class="summary-row">
            <span>Shipping</span>
            <span>${shipping > 0 ? `₹${shipping.toFixed(2)}` : 'Free'}</span>
        </div>
        ${discount > 0 ? `
        <div class="summary-row">
            <span>Discount</span>
            <span>-₹${discount.toFixed(2)}</span>
        </div>
        ` : ''}
        <div class="summary-row total">
            <span>Total</span>
            <span>₹${total.toFixed(2)}</span>
        </div>
        
        <div class="promo-code">
            <h3>Promo Code</h3>
            <div class="promo-form">
                <input type="text" id="promo-input" placeholder="Enter promo code">
                <button id="apply-promo">Apply</button>
            </div>
        </div>
        
        <button id="checkout-btn" class="checkout-btn">Proceed to Checkout</button>
        <a href="products.html" class="continue-shopping-link">Continue Shopping</a>
    `;
    
    // Add event listener for promo code
    const applyPromoButton = document.getElementById('apply-promo');
    if (applyPromoButton) {
        applyPromoButton.addEventListener('click', applyPromoCode);
    }
    
    // Add event listener for checkout button
    const checkoutButton = document.getElementById('checkout-btn');
    if (checkoutButton) {
        checkoutButton.addEventListener('click', proceedToCheckout);
    }
}

// Calculate cart totals
function calculateCartTotals() {
    const subtotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    const tax = subtotal * TAX_RATE;
    
    // Calculate shipping (free if subtotal is above threshold)
    const shipping = subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_FLAT_RATE;
    
    // Calculate discount if promo code is applied
    let discount = 0;
    if (promoCode) {
        if (promoCode.type === 'percentage') {
            discount = subtotal * (promoCode.value / 100);
        } else if (promoCode.type === 'fixed') {
            discount = promoCode.value;
        }
    }
    
    const total = subtotal + tax + shipping - discount;
    
    return { subtotal, tax, shipping, discount, total };
}

// Add event listeners to cart items
function addCartItemEventListeners() {
    // Increase quantity buttons
    document.querySelectorAll('.increase-quantity').forEach(button => {
        button.addEventListener('click', function() {
            const productId = parseInt(this.dataset.productId);
            updateCartItemQuantity(productId, 1);
        });
    });
    
    // Decrease quantity buttons
    document.querySelectorAll('.decrease-quantity').forEach(button => {
        button.addEventListener('click', function() {
            const productId = parseInt(this.dataset.productId);
            updateCartItemQuantity(productId, -1);
        });
    });
    
    // Remove item buttons
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            const productId = parseInt(this.dataset.productId);
            removeCartItem(productId);
        });
    });
}

// Setup event listeners
function setupEventListeners() {
    // Continue shopping button in empty cart
    const continueShoppingBtn = document.querySelector('.continue-shopping');
    if (continueShoppingBtn) {
        continueShoppingBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'products.html';
        });
    }
}

// Update cart item quantity
function updateCartItemQuantity(productId, change) {
    const cartItem = cart.find(item => item.id === productId);
    if (!cartItem) return;
    
    const newQuantity = cartItem.quantity + change;
    if (newQuantity < 1) {
        // If quantity would be less than 1, remove the item
        removeCartItem(productId);
        return;
    }
    
    // Update quantity
    cartItem.quantity = newQuantity;
    
    // Save changes
    saveCartToLocalStorage();
    syncCartToServer();
    
    // Update UI
    updateCartUI();
    
    // Show notification
    showNotification('Cart updated', 'success');
}

// Remove cart item
function removeCartItem(productId) {
    // Filter out the item to remove
    cart = cart.filter(item => item.id !== productId);
    
    // Save changes
    saveCartToLocalStorage();
    syncCartToServer();
    
    // Update UI
    updateCartUI();
    
    // Show notification
    showNotification('Item removed from cart', 'success');
}

// Apply promo code
function applyPromoCode() {
    const promoInput = document.getElementById('promo-input');
    if (!promoInput || !promoInput.value.trim()) return;
    
    const code = promoInput.value.trim().toUpperCase();
    
    // Check if code is valid (in a real app, this would be a server request)
    const validPromoCodes = {
        'WELCOME10': { type: 'percentage', value: 10 },
        'FRESH20': { type: 'percentage', value: 20 },
        'FLAT50': { type: 'fixed', value: 50 }
    };
    
    if (validPromoCodes[code]) {
        promoCode = validPromoCodes[code];
        showNotification(`Promo code applied: ${code}`, 'success');
        
        // Update server
        fetch('../../Backend/php/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=promo&code=${code}`
        })
        .then(response => response.json())
        .catch(error => {
            console.error('Error applying promo code:', error);
        });
        
        // Update UI
        updateCartUI();
    } else {
        showNotification('Invalid promo code', 'error');
    }
}

// Proceed to checkout
function proceedToCheckout() {
    // Show notification
    showNotification('Proceeding to checkout...', 'success');
    
    // Redirect to payment.php
    setTimeout(() => {
        window.location.href = '../../Backend/php/payment.php';
    }, 1500);
}

// Show notification
function showNotification(message, type) {
    // Remove any existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => {
        notification.remove();
    });
    
    // Create new notification
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Add to DOM
    document.body.appendChild(notification);
    
    // Remove after animation completes
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Add to cart function (used by product.js)
export function addToCart(productId, quantity = 1) {
    const product = dummyProducts.find(p => p.id === parseInt(productId));
    if (!product) {
        console.error('Product not found:', productId);
        showNotification('Product not found', 'error');
        return;
    }
    
    // Update cart
    const existingItem = cart.find(item => item.id === parseInt(productId));
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({ 
            id: parseInt(productId), 
            quantity,
            name: product.name,
            price: product.price,
            image: product.image
        });
    }
    
    // Save changes
    saveCartToLocalStorage();
    syncCartToServer();
    
    // Update UI
    updateCartUI();
    
    // Show notification
    showNotification('Product added to cart!', 'success');
}

// Export functions and variables for use in other modules
export { cart, updateCartUI, removeCartItem, updateCartItemQuantity };