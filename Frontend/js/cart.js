document.addEventListener('DOMContentLoaded', function() {
    /**
     * ==============================================
     * DOM ELEMENTS INITIALIZATION
     * ==============================================
     */
    // Cart quantity elements
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const increaseButtons = document.querySelectorAll('.increase, .quantity-btn.increase');
    const decreaseButtons = document.querySelectorAll('.decrease, .quantity-btn.decrease');
    const removeButtons = document.querySelectorAll('.remove-item');
    
    // Additional elements
    const checkoutButton = document.querySelector('.checkout-btn');
    const promoApplyButton = document.querySelector('.promo-apply');
    const promoInput = document.querySelector('.promo-input');

    /**
     * ==============================================
     * EVENT LISTENERS (NORMAL JS)
     * ==============================================
     */
    // Initialize quantity input change events
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.getAttribute('data-id') || this.dataset.id;
            let quantity = parseInt(this.value);
            
            // Ensure quantity is at least 1
            if (quantity < 1) {
                quantity = 1;
                this.value = 1;
            }
            
            // Update cart via AJAX
            updateCartItem(productId, quantity);
        });
    });
    
    // Initialize increase button click events
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
    
    // Initialize decrease button click events
    decreaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id') || this.dataset.id;
            const inputSelector = `.quantity-input[data-id="${productId}"]`;
            const inputElement = this.parentElement ? 
                this.parentElement.querySelector('.quantity-input') : 
                document.querySelector(inputSelector);
                
            if (inputElement) {
                let quantity = parseInt(inputElement.value) - 1;
                
                // Ensure quantity doesn't go below 1
                if (quantity >= 1) {
                    inputElement.value = quantity;
                    updateCartItem(productId, quantity);
                }
            }
        });
    });
    
    // Initialize remove item button click events
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id') || this.dataset.id;
            removeCartItem(productId);
        });
    });
    
    // Initialize checkout button (if it exists)
    if (checkoutButton) {
        checkoutButton.addEventListener('click', function() {
            window.location.href = 'checkout.php';
        });
    }
    
    // Initialize promo code application (if elements exist)
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

    /**
     * ==============================================
     * AJAX FUNCTIONS
     * ==============================================
     */
    /**
     * Update cart item quantity via AJAX
     * @param {string|number} productId - The ID of the product to update
     * @param {number} quantity - The new quantity value
     */
    function updateCartItem(productId, quantity) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // If server returns cart data, update display
                if (data.cart) {
                    updateCartDisplay(data.cart);
                } else {
                    // Otherwise reload the page to reflect changes
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
    
    /**
     * Remove cart item via AJAX
     * @param {string|number} productId - The ID of the product to remove
     */
    function removeCartItem(productId) {
        // Determine which endpoint to use based on the code samples
        const endpoint = 'remove_cart.php';
        
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${productId}`
        })
        .then(response => response.ok ? response.json() : Promise.reject('Failed to remove item'))
        .then(data => {
            if (data.success) {
                // Remove the item from the display
                const itemElement = document.querySelector(`.cart-item[data-id="${productId}"]`);
                if (itemElement) {
                    itemElement.remove();
                }
                
                // If server returns cart data, update display
                if (data.cart) {
                    updateCartDisplay(data.cart);
                    
                    // Reload page if cart is empty
                    if (Object.keys(data.cart).length === 0) {
                        window.location.reload();
                    }
                } else {
                    // Otherwise reload the page to reflect changes
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
    
    /**
     * Apply promo code via AJAX
     * @param {string} promoCode - The promo code to apply
     */
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
    
    /**
     * Update the cart display with new cart data
     * @param {Object} cart - The updated cart data from server
     */
    function updateCartDisplay(cart) {
        // This function would update prices, totals, and other cart display elements
        // The implementation would depend on the specific structure of your cart data
        // and HTML layout
        
        // Example implementation (commented out as it's dependent on your HTML structure):
        /*
        Object.keys(cart).forEach(productId => {
            const item = cart[productId];
            const priceElement = document.querySelector(`.item-price[data-id="${productId}"]`);
            const subtotalElement = document.querySelector(`.item-subtotal[data-id="${productId}"]`);
            
            if (priceElement) priceElement.textContent = `$${item.price.toFixed(2)}`;
            if (subtotalElement) subtotalElement.textContent = `$${(item.price * item.quantity).toFixed(2)}`;
        });
        
        // Update cart total
        const totalElement = document.querySelector('.cart-total');
        if (totalElement && cart.total) {
            totalElement.textContent = `$${cart.total.toFixed(2)}`;
        }
        */
    }
});