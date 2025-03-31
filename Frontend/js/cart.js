document.addEventListener('DOMContentLoaded', function() {
    // Initialize quantity elements
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const increaseButtons = document.querySelectorAll('.increase, .quantity-btn.increase');
    const decreaseButtons = document.querySelectorAll('.decrease, .quantity-btn.decrease');
    const removeButtons = document.querySelectorAll('.remove-item');
    
    // Additional elements
    const checkoutButton = document.querySelector('.checkout-btn');
    const promoApplyButton = document.querySelector('.promo-apply');
    const promoInput = document.querySelector('.promo-input');

    // Quantity input change events
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.getAttribute('data-id') || this.dataset.id;
            let quantity = parseInt(this.value);
            
            if (quantity < 1) {
                quantity = 1;
                this.value = 1;
            }
            
            updateCartItem(productId, quantity);
        });
    });
    
    // Increase button events
    increaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id') || this.dataset.id;
            const inputSelector = `.quantity-input[data-id="${productId}"]`;
            const inputElement = this.parentElement ? 
                this.parentElement.querySelector('.quantity-input') : 
                document.querySelector(inputSelector);
                
            if (inputElement) {
                let quantity = parseInt(inputElement.value) + 1;
                inputElement.value = quantity;
                updateCartItem(productId, quantity);
            }
        });
    });
    
    // Decrease button events
    decreaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id') || this.dataset.id;
            const inputSelector = `.quantity-input[data-id="${productId}"]`;
            const inputElement = this.parentElement ? 
                this.parentElement.querySelector('.quantity-input') : 
                document.querySelector(inputSelector);
                
            if (inputElement) {
                let quantity = parseInt(inputElement.value) - 1;
                
                if (quantity >= 1) {
                    inputElement.value = quantity;
                    updateCartItem(productId, quantity);
                }
            }
        });
    });
    
    // Remove item events
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id') || this.dataset.id;
            removeCartItem(productId);
        });
    });
    
    // Checkout button
    if (checkoutButton) {
        checkoutButton.addEventListener('click', function() {
            window.location.href = 'checkout.php';
        });
    }
    
    // Promo code application
    if (promoApplyButton && promoInput) {
        promoApplyButton.addEventListener('click', function() {
            const promoCode = promoInput.value.trim();
            if (promoCode) {
                applyPromoCode(promoCode);
            } else {
                alert('Please enter a promo code');
            }
        });
    }

    // Update cart item via AJAX
    function updateCartItem(productId, quantity) {
        fetch('../../Backend/php/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.cart) {
                    updateCartDisplay(data.cart);
                } else {
                    location.reload();
                }
            } else {
                alert('Failed to update cart. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error updating cart:', error);
            alert('An error occurred. Please try again.');
        });
    }
    
    // Remove cart item via AJAX
    function removeCartItem(productId) {
        fetch('../../Backend/php/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&product_id=${productId}`
        })
        .then(response => response.ok ? response.json() : Promise.reject('Failed to remove item'))
        .then(data => {
            if (data.success) {
                const itemElement = document.querySelector(`.cart-item[data-id="${productId}"]`);
                if (itemElement) {
                    itemElement.remove();
                }
                
                if (data.cart) {
                    updateCartDisplay(data.cart);
                    
                    if (Object.keys(data.cart).length === 0) {
                        window.location.reload();
                    }
                } else {
                    location.reload();
                }
            } else {
                alert('Failed to remove item. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error removing item from cart:', error);
            alert('An error occurred. Please try again.');
        });
    }
    
    // Apply promo code via AJAX
    function applyPromoCode(promoCode) {
        fetch('apply_promo.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/x-www-form-urlencoded' 
            },
            body: `promo_code=${promoCode}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Promo code applied successfully!');
                location.reload();
            } else {
                alert(data.message || 'Invalid promo code');
            }
        })
        .catch(error => {
            console.error('Error applying promo code:', error);
            alert('An error occurred while applying promo code.');
        });
    }
    
    // Update cart display with new data
    function updateCartDisplay(cart) {
        const cartContainer = document.querySelector('.cart-container');
        
        // Get cart products from localStorage
        const cartProducts = JSON.parse(localStorage.getItem('farm_fresh_market_cart')) || [];
        
        if (!cart || Object.keys(cart).length === 0) {
            cartContainer.innerHTML = `
                <div class="empty-cart-section">
                    <div class="empty-cart-illustration">
                        <i class="fas fa-shopping-cart fa-4x"></i>
                    </div>
                    <h2 class="empty-cart-message">Your cart feels a bit lonely right now.</h2>
                    <p class="empty-cart-subtext">Looks like you haven't added any items yet.</p>
                    <a href="../../Frontend/Html/products.html" class="continue-shopping-btn">Continue Shopping</a>
                </div>`;
            return;
        }
    
        // Update quantities and prices
        cartProducts.forEach(product => {
            const priceElement = document.querySelector(`.item-price[data-id="${product.id}"]`);
            const subtotalElement = document.querySelector(`.item-subtotal[data-id="${product.id}"]`);
            const quantityInput = document.querySelector(`.quantity-input[data-id="${product.id}"]`);
            
            if (quantityInput) quantityInput.value = cart[product.id] || 0;
            if (priceElement) priceElement.textContent = `₹${product.price.toFixed(2)}`;
            if (subtotalElement) subtotalElement.textContent = `₹${(product.price * (cart[product.id] || 0)).toFixed(2)}`;
        });
        
        // Update total
        const totalElement = document.querySelector('.cart-total');
        if (totalElement) {
            const total = cartProducts.reduce((sum, product) => {
                return sum + (product.price * (cart[product.id] || 0));
            }, 0);
            totalElement.textContent = `₹${total.toFixed(2)}`;
        }
    }
});

// Add to cart function
function addToCart(productId, quantity = 1) {
    fetch('../../Backend/php/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartDisplay(data.cart);
            showNotification('Product added to cart!', 'success');
            updateCartCount(data.cart);
        } else {
            showNotification('Failed to add product to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

// Update cart count
function updateCartCount(cart) {
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        const totalItems = Object.values(cart).reduce((sum, item) => sum + parseInt(item.quantity || 1), 0);
        cartCount.textContent = totalItems;
    }
}

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Event listeners for quantity buttons
document.querySelectorAll('.quantity-btn').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.id;
        const input = document.querySelector(`.quantity-input[data-id="${productId}"]`);
        let quantity = parseInt(input.value);

        if (this.classList.contains('decrease')) {
            quantity = Math.max(1, quantity - 1);
        } else {
            quantity++;
        }
        
        input.value = quantity;
        updateCartItem(productId, quantity);
    });
});

// Event listeners for remove buttons
document.querySelectorAll('.remove-item').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.id;
        removeFromCart(productId);
    });
});

// Update cart item
function updateCartItem(productId, quantity) {
    fetch('../../Backend/php/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=update&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// Remove from cart
function removeFromCart(productId) {
    fetch('../../Backend/php/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=remove&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}