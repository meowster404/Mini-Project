// Import dummy data
import { dummyProducts } from './dummy-data.js';

// Global variables
let allProducts = [];
let filteredProducts = [];
let cart = JSON.parse(localStorage.getItem('farm_fresh_market_cart')) || [];
let currentView = 'grid';

// DOM Elements
const productsContainer = document.getElementById('products-container');
const categoryLinks = document.querySelectorAll('.category-list a');
const sortSelects = document.querySelectorAll('.sort-select');
const viewButtons = document.querySelectorAll('.view-option');
const cartCountElement = document.getElementById('cart-count');
const applyFiltersButton = document.getElementById('apply-filters');

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    loadCart();
    updateCartCount();
    allProducts = [...dummyProducts];
    filteredProducts = [...dummyProducts];
    renderProducts(filteredProducts);
    setupEventListeners();
});

// Set up event listeners
function setupEventListeners() {
    // Category navigation
    categoryLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            categoryLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            const category = this.getAttribute('data-category');
            filterProducts(category === 'all' ? {} : { category });
        });
    });
    
    // Sorting
    sortSelects.forEach(select => {
        select.addEventListener('change', function() {
            sortProducts(this.value);
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
    
    // Apply filters
    if (applyFiltersButton) {
        applyFiltersButton.addEventListener('click', function() {
            const filters = buildFiltersObject();
            filterProducts(filters);
        });
    }
    
    // Search functionality
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');
    
    if (searchForm && searchInput) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const filters = buildFiltersObject();
            filterProducts(filters);
        });

        searchInput.addEventListener('input', debounce(function() {
            const filters = buildFiltersObject();
            filterProducts(filters);
        }, 300));
    }
}

function buildFiltersObject() {
    const filters = {};
    
    const searchInput = document.getElementById('search-input');
    if (searchInput && searchInput.value.trim()) {
        filters.search = searchInput.value.toLowerCase().trim();
    }
    
    const selectedCategories = Array.from(document.querySelectorAll('.category-checkbox:checked'))
        .map(checkbox => checkbox.value);
    if (selectedCategories.length > 0) {
        filters.categories = selectedCategories;
    }
    
    const selectedMethods = Array.from(document.querySelectorAll('.farming-method-checkbox:checked'))
        .map(checkbox => checkbox.value);
    if (selectedMethods.length > 0) {
        filters.farmingMethods = selectedMethods;
    }
    
    return filters;
}

function filterProducts(filters) {
    filteredProducts = [...allProducts];
    
    if (filters.search) {
        const searchTerm = filters.search.toLowerCase();
        filteredProducts = filteredProducts.filter(product => 
            product.name.toLowerCase().includes(searchTerm) ||
            product.description.toLowerCase().includes(searchTerm) ||
            product.category.toLowerCase().includes(searchTerm)
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
    
    const sortSelect = document.querySelector('.sort-select');
    sortProducts(sortSelect ? sortSelect.value : 'popularity');
    renderProducts(filteredProducts);
}

function sortProducts(sortBy) {
    switch (sortBy) {
        case 'price-low':
            filteredProducts.sort((a, b) => a.price - b.price);
            break;
        case 'price-high':
            filteredProducts.sort((a, b) => b.price - a.price);
            break;
        case 'newest':
            filteredProducts.sort((a, b) => new Date(b.date_added) - new Date(a.date_added));
            break;
        default:
            filteredProducts.sort((a, b) => b.popularity - a.popularity);
    }
    renderProducts(filteredProducts);
}

function renderProducts(products) {
    productsContainer.innerHTML = '';
    
    if (products.length === 0) {
        productsContainer.innerHTML = '<div class="no-products"><p>No products found</p></div>';
        return;
    }
    
    const container = document.createElement('div');
    container.className = currentView === 'grid' ? 'products-grid' : 'products-list';
    
    products.forEach(product => {
        container.appendChild(createProductCard(product));
    });
    
    productsContainer.appendChild(container);
    addProductCardEventListeners();
}

// In the createProductCard function:
function createProductCard(product) {
    const isInCart = cart.some(item => item.id === product.id);
    const productCard = document.createElement('div');
    productCard.className = 'product-card';
    productCard.dataset.productId = product.id;
    
    productCard.innerHTML = `
        <div class="product-image">
            ${product.is_organic ? '<span class="badge organic-badge">Organic</span>' : ''}
            ${product.discount > 0 ? `<span class="badge discount-badge">${product.discount}% OFF</span>` : ''}
            <img src="${product.image}" alt="${product.name}" onerror="this.onerror=null;">
        </div>
        <div class="product-info">
            <h3 class="product-name">${product.name}</h3>
            <p class="product-farm">${product.farm_name}</p>
            <div class="product-price">₹${product.price.toFixed(2)}</div>
            <p class="product-description">${product.description}</p>
            <div class="product-actions">
                ${isInCart ? `
                    <div class="cart-controls">
                        <div class="quantity-control">
                            <button class="quantity-btn decrease-quantity" data-product-id="${product.id}">-</button>
                            <input type="number" class="quantity-input" value="${cart.find(item => item.id === product.id).quantity}" readonly>
                            <button class="quantity-btn increase-quantity" data-product-id="${product.id}">+</button>
                        </div>
                        <div class="cart-actions">
                            <a class="view-cart-btn">View Cart</a>
                        </div>
                    </div>
                ` : `
                    <button class="add-to-cart-btn" data-product-id="${product.id}">Add to Cart</button>
                `}
            </div>
        </div>
    `;
    
    return productCard;
}

function addProductCardEventListeners() {
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            addToCart(productId);
        });
    });
    
    document.querySelectorAll('.increase-quantity').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            updateCartItemQuantity(productId, 1);
        });
    });
    
    document.querySelectorAll('.decrease-quantity').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            updateCartItemQuantity(productId, -1);
        });
    });

    document.querySelectorAll('.view-cart-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'cart.html'; // Updated link
        });
    });
}

function addToCart(productId, quantity = 1) {
    const product = allProducts.find(p => p.id === parseInt(productId));
    if (!product) {
        console.error('Product not found:', productId);
        showNotification('Product not found', 'error');
        return;
    }
    
    // Check if item already exists in cart
    const existingItem = cart.find(item => item.id === parseInt(productId));
    if (existingItem) {
        // Item already in cart, just show notification
        showNotification('Item already in cart', 'info');
        return;
    }
    
    // Add new item to cart
    cart.push({ 
        id: parseInt(productId), 
        quantity,
        name: product.name,
        price: product.price,
        image: product.image
    });
    
    saveCart();
    updateCartCount();
    
    // Send to server
    fetch('../../Backend/php/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}&product_data=${encodeURIComponent(JSON.stringify(product))}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart!', 'success');
        } else {
            showNotification('Failed to add product to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
    
    renderProducts(filteredProducts);
}

function updateCartItemQuantity(productId, change) {
    const cartItem = cart.find(item => item.id === parseInt(productId));
    if (!cartItem) return;
    
    const newQuantity = cartItem.quantity + change;
    if (newQuantity < 1) {
        cart = cart.filter(item => item.id !== parseInt(productId));
    } else {
        cartItem.quantity = newQuantity;
    }
    
    // Update localStorage
    saveCart();
    updateCartCount();
    
    // Update server
    fetch('../../Backend/php/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=update&product_id=${productId}&quantity=${newQuantity}`
    })
    .then(response => response.json())
    .catch(error => {
        console.error('Error updating cart:', error);
    });
    
    renderProducts(filteredProducts);
}

function updateCartCount() {
    if (cartCountElement) {
        cartCountElement.textContent = cart.reduce((total, item) => total + item.quantity, 0);
    }
}

function saveCart() {
    localStorage.setItem('farm_fresh_market_cart', JSON.stringify(cart));
}

function loadCart() {
    const savedCart = localStorage.getItem('farm_fresh_market_cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}