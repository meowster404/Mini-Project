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
}

/**
 * Build filters object from form inputs
 */
function buildFiltersObject() {
    const filters = {};
    
    // Categories
    const selectedCategories = [];
    categoryCheckboxes.forEach(checkbox => {
        if (checkbox.checked) {
            selectedCategories.push(checkbox.value);
        }
    });
    
    if (selectedCategories.length > 0) {
        filters.categories = selectedCategories;
    }
    
    // Farming methods
    const selectedMethods = [];
    farmingMethodCheckboxes.forEach(checkbox => {
        if (checkbox.checked) {
            selectedMethods.push(checkbox.value);
        }
    });
    
    if (selectedMethods.length > 0) {
        filters.farmingMethods = selectedMethods;
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
    
    // Build query string from filters
    let queryString = '?action=get_all_products';
    
    if (filters.category) {
        queryString = `?action=get_products_by_category&category=${filters.category}`;
    } else if (Object.keys(filters).length > 0) {
        queryString = '?action=get_filtered_products';
        
        if (filters.categories && filters.categories.length > 0) {
            queryString += `&category=${filters.categories.join(',')}`;
        }
        
        if (filters.farmingMethods && filters.farmingMethods.length > 0) {
            queryString += `&farming_method=${filters.farmingMethods.join(',')}`;
        }
    }
    
    // Make AJAX request
    fetch(`../php/product.php${queryString}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            allProducts = data;
            filteredProducts = [...allProducts];
            
            // Sort products by default sorting option
            const defaultSort = document.querySelector('.sort-select').value;
            sortProducts(defaultSort);
            
            // Render products
            renderProducts(filteredProducts);
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            productsContainer.innerHTML = `
                <div class="no-products">
                    <p>Error loading products. Please try again later.</p>
                </div>
            `;
        });
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
    const currentSort = document.querySelector('.sort-select').value;
    sortProducts(currentSort);
    
    // Render filtered products
    renderProducts(filteredProducts);
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
                        <a href="../../Backend/php/cart.php" class="view-cart-btn">View Cart</a>
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
    // In addProductCardEventListeners function
    viewCartButtons.forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = '../../Backend/php/cart.php';
    });
    });
    
    // In addToCart function
    fetch('../../Backend/php/cart.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'  // Change content type
    },
    body: `action=add&product_id=${productId}&quantity=1`    // Change body format
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update local cart data
            const existingItem = cart.find(item => item.id == productId);
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image,
                    quantity: 1
                });
            }
            
            saveCart();
            updateCartCount();
            renderProducts(filteredProducts);
            
            // Optional: Redirect to cart page
            window.location.href = '../../Backend/php/cart.php';
        } else {
            alert('Failed to add product to cart. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        alert('An error occurred while adding to cart. Please try again.');
    });
}

// Also update updateCartItemQuantity function
function updateCartItemQuantity(productId, change) {
    const cartItem = cart.find(item => item.id == productId);
    
    if (!cartItem) {
        console.error('Cart item not found:', productId);
        return;
    }
    
    const newQuantity = cartItem.quantity + change;
    if (newQuantity < 1) return;
    
    fetch('../../Backend/php/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=update&product_id=${productId}&quantity=${newQuantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update local cart data
            cartItem.quantity = newQuantity;
            
            // Save cart to localStorage
            saveCart();
            
            // Update cart count
            updateCartCount();
            
            // Re-render products to update UI
            renderProducts(filteredProducts);
        } else {
            alert('Failed to update cart. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error updating cart:', error);
        alert('An error occurred while updating cart. Please try again.');
    });
}

/**
 * Update the cart count display
 */
function updateCartCount() {
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    cartCountElement.textContent = totalItems;
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

/**
 * Generate sample product data (for testing without database)
 * This function is used only for development and should be removed in production
 */

function generateSampleProducts() {
    return [
        {
            id: 1,
            name: 'Fresh Tomatoes',
            description: 'Juicy, ripe tomatoes picked fresh from the vine.',
            price: 40,
            weight: 1,
            image: '/placeholder.svg',
            category: 'fruits',
            is_organic: true,
            discount: 0,
            farming_method: 'organic',
            image: '/placeholder.svg',
            category: 'vegetables',
            is_organic: true,
            discount: 0,
            farming_method: 'organic'
        },
        {
            id: 2,
            name: "Golden Bananas",
            description: "Sweet and delicious bananas, rich in potassium.",
            price: 50,
            weight: 1,
            image: "/placeholder.svg",
            category: "fruits",
            is_organic: true,
            discount: 10,
            farming_method: "organic"
        },
        {
            id: 3,
            name: "Fresh Carrots",
            description: "Crunchy and nutritious carrots, perfect for salads.",
            price: 35,
            weight: 1,
            image: "/placeholder.svg",
            category: "vegetables",
            is_organic: false,
            discount: 5,
            farming_method: "conventional"
        },
        {
            id: 4,
            name: "Free-Range Eggs",
            description: "Healthy and protein-rich eggs from free-range hens.",
            price: 120,
            weight: 12,
            image: "/placeholder.svg",
            category: "eggs",
            is_organic: true,
            discount: 0,
            farming_method: "free-range"
        },
        {
            id: 5,
            name: "Fresh Chicken Meat",
            description: "Tender and high-quality farm-raised chicken.",
            price: 250,
            weight: 1,
            image: "/placeholder.svg",
            category: "meat",
            is_organic: false,
            discount: 15,
            farming_method: "conventional"
        }
    ]
}

// Load sample products
function fetchProducts(filters = {}) {
    // Show loading indicator
    productsContainer.innerHTML = `
        <div class="loading">
            <div class="loading-spinner"></div>
            <p>Loading products...</p>
        </div>
    `;

    // Load sample data instead of fetching from the server
    setTimeout(() => {
        allProducts = generateSampleProducts();
        filteredProducts = [...allProducts];

        // Sort products by default sorting option
        const defaultSort = document.querySelector('.sort-select')?.value || 'popularity';
        sortProducts(defaultSort);

        // Render products
        renderProducts(filteredProducts);
    }, 500); // Simulating delay for better UX
}
