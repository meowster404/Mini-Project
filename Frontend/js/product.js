import { dummyProducts } from './dummyProducts.js';
/**
 * EcoKart Product Page JavaScript
 * This file handles all the interactive functionality of the product page
 * including AJAX requests, filtering, sorting, and cart operations
 */

// Global variables
let allProducts = [];
let filteredProducts = [];
let cart = [];
let currentView = 'grid';

// DOM Elements
const productsContainer = document.getElementById('products-container');
const categoryLinks = document.querySelectorAll('.category-list a');
const sortSelects = document.querySelectorAll('.sort-select');
const viewButtons = document.querySelectorAll('.view-option');
const cartCountElement = document.getElementById('cart-count');
const applyFiltersButton = document.getElementById('apply-filters');
const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
const farmingMethodCheckboxes = document.querySelectorAll('.farming-method-checkbox');

/**
 * Initialize the page
 */
document.addEventListener('DOMContentLoaded', function() {
    // Load cart from localStorage
    loadCart();
    
    // Update cart count display
    updateCartCount();
    
    // Fetch all products on page load
    fetchProducts();
    
    // Set up event listeners
    setupEventListeners();
});

/**
 * Set up all event listeners
 */
function setupEventListeners() {
    // Category navigation
    categoryLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links
            categoryLinks.forEach(l => l.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            const category = this.getAttribute('data-category');
            if (category === 'all') {
                filterProducts({});
            } else {
                filterProducts({ category: category });
            }
        });
    });
    
    // Sorting
    sortSelects.forEach(select => {
        select.addEventListener('change', function() {
            const sortBy = this.value;
            sortProducts(sortBy);
        });
    });
    
    // View options (grid/list)
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            viewButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            currentView = this.getAttribute('data-view');
            renderProducts(filteredProducts);
        });
    });
    
    // Apply filters button
    if (applyFiltersButton) {
        applyFiltersButton.addEventListener('click', function() {
            const filters = buildFiltersObject();
            filterProducts(filters);
        });
    }
    
    // Search form
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');
    
    if (searchForm && searchInput) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = searchInput.value.toLowerCase().trim();
            const filters = buildFiltersObject();
            filters.search = searchTerm;
            fetchProducts(filters);
        });

        // Real-time search
        searchInput.addEventListener('input', debounce(function() {
            const searchTerm = this.value.toLowerCase().trim();
            if (searchTerm.length >= 2 || searchTerm.length === 0) {
                const filters = buildFiltersObject();
                filters.search = searchTerm;
                fetchProducts(filters);
            }
        }, 300));
    }

    // Category checkboxes
    document.querySelectorAll('.category-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const filters = buildFiltersObject();
            fetchProducts(filters);
        });
    });

    // Farming method checkboxes
    document.querySelectorAll('.farming-method-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const filters = buildFiltersObject();
            fetchProducts(filters);
        });
    });

    // Sorting
    const sortSelect = document.querySelector('.sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const filters = buildFiltersObject();
            fetchProducts(filters);
        });
    }
}

// Add debounce function to prevent too many requests
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Update buildFiltersObject function
function buildFiltersObject() {
    const filters = {};
    
    // Get search term
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        const searchTerm = searchInput.value.toLowerCase().trim();
        if (searchTerm) {
            filters.search = searchTerm;
        }
    }
    
    // Get selected categories
    const selectedCategories = Array.from(document.querySelectorAll('.category-checkbox:checked'))
        .map(checkbox => checkbox.value);
    if (selectedCategories.length > 0) {
        filters.categories = selectedCategories;
    }
    
    // Get selected farming methods
    const selectedMethods = Array.from(document.querySelectorAll('.farming-method-checkbox:checked'))
        .map(checkbox => checkbox.value);
    if (selectedMethods.length > 0) {
        filters.farmingMethods = selectedMethods;
    }
    
    // Get sorting option
    const sortSelect = document.querySelector('.sort-select');
    if (sortSelect) {
        filters.sortBy = sortSelect.value;
    }
    
    return filters;
}

/**
 * Fetch products from the server using AJAX
 */
function fetchProducts(filters = {}) {
    // Show loading indicator
    productsContainer.innerHTML = `
        <div class="loading">
            <div class="loading-spinner"></div>
            <p>Loading products...</p>
        </div>
    `;

    // Use the dummyProducts data
    setTimeout(() => {
        filteredProducts = [...dummyProducts];
        
        // Apply filters
        if (Object.keys(filters).length > 0) {
            if (filters.search) {
                const searchTerm = filters.search.toLowerCase();
                filteredProducts = filteredProducts.filter(product => 
                    product.name.toLowerCase().includes(searchTerm) ||
                    product.description.toLowerCase().includes(searchTerm) ||
                    product.category.toLowerCase().includes(searchTerm) ||
                    product.farm_name.toLowerCase().includes(searchTerm)
                );
            }
            
            if (filters.categories && filters.categories.length > 0) {
                filteredProducts = filteredProducts.filter(product =>
                    filters.categories.includes(product.category)
                );
            }
            
            if (filters.farmingMethods && filters.farmingMethods.length > 0) {
                filteredProducts = filteredProducts.filter(product =>
                    filters.farmingMethods.includes(product.farming_method)
                );
            }
        }

        // Sort products
        const sortSelect = document.querySelector('.sort-select');
        const currentSort = sortSelect ? sortSelect.value : 'popularity';
        sortProducts(currentSort);
        
        // Update results count
        updateResultsCount(filteredProducts.length);
        
        // Render products
        renderProducts(filteredProducts);
    }, 500);
}

/**
 * Filter products based on selected filters
 */
function filterProducts(filters) {
    // If we have server-side filtering, fetch filtered products
    if (Object.keys(filters).length > 0) {
        fetchProducts(filters);
        return;
    }
    
    // Otherwise, filter client-side
    filteredProducts = allProducts.filter(product => {
        let matchesCategory = true;
        let matchesFarmingMethod = true;
        
        // Category filter
        if (filters.categories && filters.categories.length > 0) {
            matchesCategory = filters.categories.includes(product.category);
        }
        
        // Farming method filter
        if (filters.farmingMethods && filters.farmingMethods.length > 0) {
            matchesFarmingMethod = filters.farmingMethods.includes(product.farming_method);
        }
        
        return matchesCategory && matchesFarmingMethod;
    });
    
    // Sort products by current sorting option
    const sortSelect = document.querySelector('.sort-select');
    const currentSort = sortSelect ? sortSelect.value : 'popularity';
    sortProducts(currentSort);
    
    // Render filtered products
    renderProducts(filteredProducts);

    // Update results count
    updateResultsCount(filteredProducts.length);
}

/**
 * Update results count display
 */
function updateResultsCount(count) {
    const productsTitle = document.querySelector('.products-title');
    if (productsTitle) {
        productsTitle.textContent = count === 0 
            ? 'No products found' 
            : `${count} Product${count !== 1 ? 's' : ''} found`;
    }
}

/**
 * Sort products based on selected option
 */
function sortProducts(sortBy) {
    switch (sortBy) {
        case 'price-low':
            filteredProducts.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
            break;
        case 'price-high':
            filteredProducts.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
            break;
        case 'newest':
            filteredProducts.sort((a, b) => new Date(b.date_added) - new Date(a.date_added));
            break;
        case 'popularity':
        default:
            filteredProducts.sort((a, b) => b.popularity - a.popularity);
            break;
    }
    
    renderProducts(filteredProducts);
}

/**
 * Render products in the container
 */
function renderProducts(products) {
    if (products.length === 0) {
        productsContainer.innerHTML = `
            <div class="no-products">
                <p>No products found matching your criteria.</p>
            </div>
        `;
        return;
    }
    
    // Clear container
    productsContainer.innerHTML = '';
    
    // Create container based on view
    const container = document.createElement('div');
    container.className = currentView === 'grid' ? 'products-grid' : 'products-list';
    
    // Add products to container
    products.forEach(product => {
        const productCard = createProductCard(product);
        container.appendChild(productCard);
    });
    
    // Add container to DOM
    productsContainer.appendChild(container);
    
    // Add event listeners to product cards
    addProductCardEventListeners();
}

/**
 * Create a product card element
 */
function createProductCard(product) {
    const isInCart = cart.some(item => item.id === product.id);
    
    const productCard = document.createElement('div');
    productCard.className = 'product-card';
    productCard.dataset.productId = product.id;
    
    // Create card content based on cart status
    if (isInCart) {
        // Product is in cart - show quantity controls and view cart button
        const cartItem = cart.find(item => item.id === product.id);
        
        productCard.innerHTML = `
            <div class="product-image">
                ${product.is_organic ? '<span class="badge organic-badge">Organic</span>' : ''}
                ${product.discount > 0 ? `<span class="badge discount-badge">${product.discount}% OFF</span>` : ''}
                <img src="${product.image || '/placeholder.svg'}" alt="${product.name}">
            </div>
            <div class="product-info">
                <h3 class="product-name">${product.name}</h3>
                <p class="product-farm">${product.farm_name}</p>
                <div class="product-price">₹${parseFloat(product.price).toFixed(2)}</div>
                <p class="product-description">${product.description}</p>
                <div class="product-actions">
                    <div class="quantity-control">
                        <button class="quantity-btn decrease-quantity" data-product-id="${product.id}">-</button>
                        <input type="text" class="quantity-input" value="${cartItem.quantity}" readonly>
                        <button class="quantity-btn increase-quantity" data-product-id="${product.id}">+</button>
                    </div>
                    <div class="cart-actions">
                        <a href="/cart.php" class="view-cart-btn">View Cart</a>
                    </div>
                </div>
            </div>
        `;
    } else {
        // Product is not in cart - show add to cart button
        productCard.innerHTML = `
            <div class="product-image">
                ${product.is_organic ? '<span class="badge organic-badge">Organic</span>' : ''}
                ${product.discount > 0 ? `<span class="badge discount-badge">${product.discount}% OFF</span>` : ''}
                <img src="${product.image || '/placeholder.svg'}" alt="${product.name}">
            </div>
            <div class="product-info">
                <h3 class="product-name">${product.name}</h3>
                <p class="product-farm">${product.farm_name}</p>
                <div class="product-price">₹${parseFloat(product.price).toFixed(2)}</div>
                <p class="product-description">${product.description}</p>
                <div class="product-actions">
                    <button class="add-to-cart-btn" data-product-id="${product.id}">Add to Cart</button>
                </div>
            </div>
        `;
    }
    
    return productCard;
}

/**
 * Add event listeners to product card buttons
 */
function addProductCardEventListeners() {
    // Add to cart buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            addToCart(productId);
        });
    });
    
    // Increase quantity buttons
    const increaseButtons = document.querySelectorAll('.increase-quantity');
    increaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            updateCartItemQuantity(productId, 1);
        });
    });
    
    // Decrease quantity buttons
    const decreaseButtons = document.querySelectorAll('.decrease-quantity');
    decreaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            updateCartItemQuantity(productId, -1);
        });
    });
    
    // View cart buttons
    const viewCartButtons = document.querySelectorAll('.view-cart-btn');
    viewCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/cart.php';
        });
    });
}

/**
 * Add product to cart
 */
function addToCart(productId) {
    if (!productId) {
        console.error('Invalid product ID');
        return;
    }
    
    // Find the product in our products array
    const product = filteredProducts.find(p => p.id == productId);
    if (!product) {
        console.error('Product not found:', productId);
        return;
    }
    
    // Check if product is already in cart
    const existingItem = cart.find(item => item.id == productId);
    
    if (existingItem) {
        // Update quantity if already in cart
        updateCartItemQuantity(productId, 1);
    } else {
        // Add new item to cart
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            quantity: 1
        });
        
        // Save cart and update UI
        saveCart();
        updateCartCount();
        renderProducts(filteredProducts);
        showNotification('Product added to cart successfully!', 'success');
    }
    
    // Optional: Send to server
    const formData = new URLSearchParams();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', 1);
    
    fetch('/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Failed to add product to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Continue with local cart regardless of server response
    });
}

/**
 * Update cart item quantity
 */
function updateCartItemQuantity(productId, change) {
    const cartItem = cart.find(item => item.id == productId);
    
    if (!cartItem) {
        console.error('Cart item not found:', productId);
        return;
    }
    
    const newQuantity = cartItem.quantity + change;
    
    // Remove item if quantity is less than 1
    if (newQuantity < 1) {
        cart = cart.filter(item => item.id != productId);
        saveCart();
        updateCartCount();
        renderProducts(filteredProducts);
        showNotification('Product removed from cart', 'info');
        return;
    }
    
    // Update quantity
    cartItem.quantity = newQuantity;
    saveCart();
    updateCartCount();
    renderProducts(filteredProducts);
    
    // Optional: Send to server
    fetch('/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=update&product_id=${productId}&quantity=${newQuantity}`
    })
    .catch(error => {
        console.error('Error updating cart on server:', error);
        // Continue with local cart regardless of server response
    });
}

/**
 * Update the cart count display
 */
function updateCartCount() {
    if (cartCountElement) {
        const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
        cartCountElement.textContent = totalItems;
    }
}

/**
 * Show notification message
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

/**
 * Save cart to localStorage
 */
function saveCart() {
    localStorage.setItem('ecokart_cart', JSON.stringify(cart));
}

/**
 * Load cart from localStorage
 */
function loadCart() {
    const savedCart = localStorage.getItem('ecokart_cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
    }
}