<?php
session_start();

if (!isset($_SESSION['vendor_id'])) {
    header('Location: http://localhost/caplog1/pages/vendor/login.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .logo-text {
            font-weight: 700;
            font-size: 1.8rem;
            background: linear-gradient(45deg, #0d6efd, #6f42c1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .card-img-container {
            height: 225px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        .card-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .card:hover .card-img-container img {
            transform: scale(1.05);
        }
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1498049794561-7780e7231661?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            margin-bottom: 40px;
        }
        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 40px 0;
            margin-top: 60px;
        }
        .social-links a {
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
            transition: color 0.3s;
        }
        .social-links a:hover {
            color: #0d6efd;
        }
        .btn-cart {
            position: relative;
        }
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }
        
        /* Custom styles for product cards */
        .product-description {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            min-height: 48px;
            margin-bottom: 10px;
        }
        .read-more-btn {
            background: none;
            border: none;
            color: #0d6efd;
            padding: 0;
            font-size: 0.9rem;
            cursor: pointer;
        }
        .card-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .card-text {
            margin-bottom: 8px;
        }
        
        /* Button group styling */
        .btn-group-sm {
            display: flex;
            gap: 4px;
        }
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        /* Quantity slider styles */
        .quantity-slider {
            width: 100%;
            margin: 10px 0;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-value {
            min-width: 30px;
            text-align: center;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 50px 0;
            }
            .display-4 {
                font-size: 2.2rem;
            }
            .cart-item-details {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
             <a class="" href="index.php">
               <img src="../../images/logoBG.PNG" width="70" alt="" srcset="">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#products" id="">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <form class="d-none d-md-block me-3">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search products..." aria-label="Search">
                            <button class="btn btn-outline-secondary" type="button"><i class="bi bi-search"></i></button>
                        </div>
                    </form>
                    <div class="btn-group me-3">
                     <a href="#" class="btn btn-outline-primary position-relative btn-cart" data-bs-toggle="modal" data-bs-target="#cartModal">
                        <i class="bi bi-cart3"></i>
                        <span id="cartCount" class="cart-count z-1">0</span>
                    </a>
                    <a href="#" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#wishlistModal">
                        <i class="bi bi-heart"></i>
                        <span id="wishlistCount" class="cart-count z-1">0</span>
                    </a>

                    </div>
                    <div class="dropdown">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <img id="navProfileImg" src="https://github.com/mdo.png" alt="profile" width="32" height="32" class="rounded-circle">
                        </a>
                        <ul class="dropdown-menu text-small">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="profile.php">Orders</a></li>
                            <li><a class="dropdown-item" href="profile.php">Wishlist</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <!-- Cart Modal -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cartModalLabel"><i class="bi bi-cart3"></i> My Cart</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="cartItemsContainer">
        <div class="text-center py-5 text-muted">Loading cart...</div>
      </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                     <button type="button" class="btn btn-primary" onclick="createShipmentFromCart();">Proceed to Orders</button>
            </div>
    </div>
  </div>
</div>

<!-- Wishlist Modal -->
<div class="modal fade" id="wishlistModal" tabindex="-1" aria-labelledby="wishlistModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="wishlistModalLabel"><i class="bi bi-heart"></i> My Wishlist</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="wishlistItemsContainer">
        <div class="text-center py-5 text-muted">Loading wishlist...</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Tech Deals of the Season</h1>
            <p class="lead">Up to 40% off on latest gadgets and electronics</p>
            <a href="#products" class="btn btn-primary btn-lg mt-3">Shop Now</a>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container" id="products">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-4">Featured Products</h2>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4" id="products-container">
            <!-- Products will be loaded dynamically here -->
            <div class="col-12">
                <div class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading products...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Promotional Banner -->
        <div class="row mt-5 mb-5">
            <div class="col-12">
                <div class="alert alert-info text-center py-4">
                    <h3>Free Shipping on Orders Over $50!</h3>
                    <p class="mb-0">Use code <strong>SHIPFREE</strong> at checkout</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                    <h5>TechShop</h5>
                    <p>Your one-stop shop for all electronics and gadgets. We offer the latest technology at competitive prices.</p>
                    <div class="social-links">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Home</a></li>
                        <li><a href="#" class="text-white">Products</a></li>
                        <li><a href="#" class="text-white">About Us</a></li>
                        <li><a href="#" class="text-white">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5>Contact Info</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-geo-alt"></i> 123 Tech Street, San Francisco, CA</li>
                        <li><i class="bi bi-telephone"></i> (123) 456-7890</li>
                        <li><i class="bi bi-envelope"></i> info@techshop.com</li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h5>Newsletter</h5>
                    <p>Subscribe to our newsletter for the latest updates and offers.</p>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Your email">
                        <button class="btn btn-primary" type="button">Subscribe</button>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="text-center">
                <p>&copy; 2023 TechShop. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Product Detail Modal -->
    <div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productDetailModalLabel">Product Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img id="modalProductImage" src="" class="img-fluid rounded" alt="Product Image">
                        </div>
                        <div class="col-md-6">
                            <h3 id="modalProductName"></h3>
                            <p class="text-muted" id="modalProductSKU"></p>
                            <h4 class="text-primary" id="modalProductPrice"></h4>
                            <div id="modalProductStock" class="mb-3"></div>
                            <p id="modalProductDescription" class="mb-3"></p>
                            <div class="d-grid gap-2 d-md-flex">
                                <button id="modalAddToCart" class="btn btn-primary me-md-2">Add to Cart</button>
                                <button id="modalAddToWishlist" class="btn btn-outline-secondary">Add to Wishlist</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Toast Notification -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
  <div id="liveToast" class="toast align-items-center text-bg-success border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastMessage">
        Product added successfully!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    
    <script>
        function showToast(message, type = 'success') {
    const toastEl = document.getElementById('liveToast');
    const toastBody = document.getElementById('toastMessage');

    // Change color based on type
    toastEl.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning');
    if (type === 'success') toastEl.classList.add('text-bg-success');
    if (type === 'error') toastEl.classList.add('text-bg-danger');
    if (type === 'warning') toastEl.classList.add('text-bg-warning');

    toastBody.textContent = message;
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

        // API endpoint - replace with your actual API URL
        const apiUrl = "http://localhost/caplog1/api/inventory.php";
        const cartApi = "http://localhost/caplog1/api/cart.php";
        const wishlistApi = "http://localhost/caplog1/api/wishlist.php";
        const profileApi = "http://localhost/caplog1/api/profile.php";

        // Store product data for later reference
        let allProducts = [];

        function buildUploadUrl(fileName) {
            return `http://localhost/caplog1/uploads/${fileName}`;
        }

        async function refreshCartCount() {
            try {
                const r = await fetch(cartApi + '?count=1', { credentials: 'same-origin' });
                if (!r.ok) return;
                const d = await r.json();
                if (d.status === 'success') {
                    const el = document.getElementById('cartCount');
                    if (el) el.textContent = d.data.total_quantity ?? 0;
                }
            } catch (e) { /* noop */ }
        }

        async function refreshWishlistCount() {
            try {
                const r = await fetch(wishlistApi + '?count=1', { credentials: 'same-origin' });
                if (!r.ok) return;
                const d = await r.json();
                if (d.status === 'success') {
                    const el = document.getElementById('wishlistCount');
                    if (el) el.textContent = d.data.total ?? 0;
                }
            } catch (e) { /* noop */ }
        }

        async function loadNavProfile() {
            try {
                const r = await fetch(profileApi, { credentials: 'same-origin' });
                if (!r.ok) return;
                const d = await r.json();
                if (d.status === 'success') {
                    const u = d.data;
                    const img = document.getElementById('navProfileImg');
                    if (img) {
                        if (u.profile_image) img.src = buildUploadUrl(u.profile_image);
                        img.title = `${u.first_name || ''} ${u.last_name || ''}`.trim();
                        img.alt = img.title || 'profile';
                    }
                }
            } catch (e) { /* noop */ }
        }

        async function addToCart(productId, quantity = 1) {
            try {
                const r = await fetch(cartApi, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ product_id: Number(productId), quantity: Number(quantity) })
                });
                const d = await r.json();
                if (!r.ok || d.status !== 'success') {
                    alert('Failed to add to cart: ' + (d.message || r.status));
                } else {
                    await refreshCartCount();
                    showToast('Product added to cart successfully!');
                }
            } catch (e) {
                alert('Failed to add to cart.');
            }
        }

        async function updateCartQuantity(productId, quantity) {
            try {
                const r = await fetch(cartApi, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ product_id: Number(productId), quantity: Number(quantity) })
                });
                const d = await r.json();
                if (!r.ok || d.status !== 'success') {
                    alert('Failed to update quantity: ' + (d.message || r.status));
                    return false;
                } else {
                    await refreshCartCount();
                    return true;
                }
            } catch (e) {
                alert('Failed to update quantity.');
                return false;
            }
        }

        async function addToWishlist(productId) {
            try {
                const r = await fetch(wishlistApi, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ product_id: Number(productId) })
                });
                const d = await r.json();
                if (!r.ok || d.status !== 'success') {
                    alert('Failed to add to wishlist: ' + (d.message || r.status));
                } else {
                    await refreshWishlistCount();
                    showToast('Product added to wishlist successfully!');
                }
            } catch (e) {
                alert('Failed to add to wishlist.');
            }
        }
        
        // Function to fetch products from the API
        async function fetchProducts() {
            try {
                const response = await fetch(apiUrl);
                if (!response.ok) {
                    throw new Error('Failed to fetch products');
                }
                
                const products = await response.json();
                allProducts = products; // Store for later use
                displayProducts(products);
            } catch (error) {
                console.error('Error fetching products:', error);
                document.getElementById('products-container').innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger text-center">
                            <h4>Error Loading Products</h4>
                            <p>Unable to load products at this time. Please try again later.</p>
                            <button class="btn btn-primary mt-2" onclick="fetchProducts()">Retry</button>
                        </div>
                    </div>
                `;
            }
        }
        
        // Function to display products
        function displayProducts(products) {
            const container = document.getElementById('products-container');
            
            if (!products || products.length === 0) {
                container.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <h4>No Products Available</h4>
                            <p>There are no products in the inventory at this time.</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            let html = '';
            
            products.forEach(product => {
                // Determine stock status
                let stockStatus = 'In Stock';
                let stockClass = 'success';
                
                if (product.stock_level <= 0) {
                    stockStatus = 'Out of Stock';
                    stockClass = 'danger';
                } else if (product.stock_level <= product.reorder_level) {
                    stockStatus = 'Low Stock';
                    stockClass = 'warning';
                }
                
                // Use placeholder image if no product photo is available
                const imageUrl = product.product_photo_url && product.product_photo_url !== 'http://localhost/caplog1/uploads/products/' 
                    ? product.product_photo_url 
                    : 'https://via.placeholder.com/300x200?text=Product+Image';
                
                // Shorten the description for card view
                const shortDescription = product.notes && product.notes.length > 100 
                    ? product.notes.substring(0, 100) + '...' 
                    : product.notes || 'High-quality product with great features.';
                
                html += `
                    <div class="col">
                        <div class="card shadow-sm h-100">
                            <div class="card-img-container position-relative">
                                <img src="${imageUrl}" alt="${product.item_name}">
                                <span class="stock-badge badge bg-${stockClass}">${stockStatus}</span>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">${product.item_name}</h5>
                                <p class="card-text text-muted small">${product.category} - SKU: ${product.sku}</p>
                                <p class="product-description">${shortDescription}</p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold text-primary">₱${parseFloat(product.price).toFixed(2)}</span>
                                        <span class="badge bg-light text-dark">Stock: ${product.stock_level}</span>
                                    </div>
                                    <div class="btn-group-sm d-flex justify-content-between">
                                        <button type="button" class="btn btn-success view-details" data-product-id="${product.id}">View</button>
                                        <button type="button" class="btn btn-warning add-to-cart" data-product-id="${product.id}" ${product.stock_level <= 0 ? 'disabled' : ''}>
                                            ${product.stock_level <= 0 ? 'Out of Stock' : 'Add to cart'}
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary add-to-wishlist" data-product-id="${product.id}">
                                            <i class="bi bi-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            
            // Add event listeners to view details buttons
            document.querySelectorAll('.view-details').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    showProductDetails(productId, products);
                });
            });

            // Add to cart buttons
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', async function() {
                    const pid = this.getAttribute('data-product-id');
                    await addToCart(pid, 1);
                });
            });

            // Add to wishlist buttons
            document.querySelectorAll('.add-to-wishlist').forEach(button => {
                button.addEventListener('click', async function() {
                    const pid = this.getAttribute('data-product-id');
                    await addToWishlist(pid);
                });
            });
        }
        
        // Function to show product details in modal
        function showProductDetails(productId, products) {
            const product = products.find(p => p.id == productId);
            if (!product) return;
            
            // Set modal content
            document.getElementById('modalProductName').textContent = product.item_name;
            document.getElementById('modalProductSKU').textContent = `SKU: ${product.sku} | Category: ${product.category}`;
            document.getElementById('modalProductPrice').textContent = `₱${parseFloat(product.price).toFixed(2)}`;
            
            // Set stock status
            let stockStatus = 'In Stock';
            let stockClass = 'success';
            if (product.stock_level <= 0) {
                stockStatus = 'Out of Stock';
                stockClass = 'danger';
            } else if (product.stock_level <= product.reorder_level) {
                stockStatus = 'Low Stock';
                stockClass = 'warning';
            }
            document.getElementById('modalProductStock').innerHTML = 
                `<span class="badge bg-${stockClass}">${stockStatus}</span> <span class="ms-2">${product.stock_level} units available</span>`;
            
            // Set description
            document.getElementById('modalProductDescription').textContent = 
                product.notes || 'No detailed description available for this product.';
            
            const imageUrl =
    product.product_photo_url &&
    !product.product_photo_url.endsWith('/uploads/products/')
        ? product.product_photo_url
        : 'https://via.placeholder.com/500x400?text=Product+Image';

            
            // Hook modal buttons
            const addBtn = document.getElementById('modalAddToCart');
            const wishBtn = document.getElementById('modalAddToWishlist');
            if (addBtn) {
                addBtn.onclick = async () => { await addToCart(product.id, 1); };
                addBtn.disabled = product.stock_level <= 0;
            }
            if (wishBtn) {
                wishBtn.onclick = async () => { await addToWishlist(product.id); };
            }

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('productDetailModal'));
            modal.show();
        }
        
        // Function to get product name by ID
        function getProductNameById(productId) {
            const product = allProducts.find(p => p.id == productId);
            return product ? product.item_name : `Product #${productId}`;
        }
        
        // Function to get product price by ID
        function getProductPriceById(productId) {
            const product = allProducts.find(p => p.id == productId);
            return product ? parseFloat(product.price).toFixed(2) : '0.00';
        }
        
        // Function to get product stock by ID
        function getProductStockById(productId) {
            const product = allProducts.find(p => p.id == productId);
            return product ? product.stock_level : 0;
        }
        
        // Fetch products and navbar info when the page loads
        document.addEventListener('DOMContentLoaded', async () => {
            await Promise.all([
                fetchProducts(),
                refreshCartCount(),
                refreshWishlistCount(),
                loadNavProfile()
            ]);
        });

        async function loadCartItems() {
            const container = document.getElementById('cartItemsContainer');
            container.innerHTML = `<div class="text-center py-5 text-muted">Loading...</div>`;
            try {
                const r = await fetch(cartApi, { credentials: 'same-origin' });
                const d = await r.json();
                if (d.status !== 'success') throw new Error(d.message);
                
                if (!d.data.length) {
                    container.innerHTML = `<div class="text-center py-5 text-muted">Your cart is empty.</div>`;
                    return;
                }

                let html = `<table class="table align-middle">
                    <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th></th></tr></thead><tbody>`;
                
                let cartTotal = 0;
                
                d.data.forEach(item => {
                    let productName = getProductNameById(item.product_id);
                    if (!productName || productName.startsWith('Product #')) productName = 'Product removed';
                    const productPrice = getProductPriceById(item.product_id);
                    const maxStock = getProductStockById(item.product_id);
                    const itemTotal = (parseFloat(productPrice) * parseInt(item.quantity)).toFixed(2);
                    cartTotal += parseFloat(itemTotal);
                    
                    html += `
                        <tr>
                            <td>${productName}</td>
                            <td>₱${productPrice}</td>
                            <td>
                                <div class="quantity-controls">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="changeQuantity(${item.product_id}, ${item.quantity}-1, ${maxStock})">-</button>
                                    <span class="quantity-value" id="quantity-${item.product_id}">${item.quantity}</span>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="changeQuantity(${item.product_id}, ${item.quantity}+1, ${maxStock})">+</button>
                                </div>
                                <input type="range" class="form-range quantity-slider" id="slider-${item.product_id}" 
                                    min="1" max="${maxStock}" value="${item.quantity}" 
                                    oninput="updateQuantityFromSlider(${item.product_id}, this.value, ${maxStock})">
                            </td>
                            <td id="total-${item.product_id}">₱${itemTotal}</td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="removeFromCart(${item.product_id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                });

                html += `<tr class="fw-bold">
                    <td colspan="3" class="text-end">Total:</td>
                    <td id="cart-total">₱${cartTotal.toFixed(2)}</td>
                    <td></td>
                </tr>`;
                
                html += `</tbody></table>`;
                container.innerHTML = html;
            } catch (e) {
                container.innerHTML = `<div class="alert alert-danger">Failed to load cart.</div>`;
            }
        }

        async function changeQuantity(productId, newQuantity, maxStock) {
            // Validate the new quantity
            newQuantity = parseInt(newQuantity);
            if (newQuantity < 1) newQuantity = 1;
            if (newQuantity > maxStock) newQuantity = maxStock;
            
            // Update the UI immediately for better responsiveness
            document.getElementById(`quantity-${productId}`).textContent = newQuantity;
            document.getElementById(`slider-${productId}`).value = newQuantity;
            
            // Update the total for this item
            const productPrice = getProductPriceById(productId);
            const itemTotal = (parseFloat(productPrice) * newQuantity).toFixed(2);
            document.getElementById(`total-${productId}`).textContent = `₱${itemTotal}`;
            
            // Update the cart total
            updateCartTotal();
            
            // Send the update to the server
            const success = await updateCartQuantity(productId, newQuantity);
            
            if (!success) {
                // If the update failed, reload the cart to get the correct values
                loadCartItems();
            }
        }
        
        function updateQuantityFromSlider(productId, newQuantity, maxStock) {
            newQuantity = parseInt(newQuantity);
            document.getElementById(`quantity-${productId}`).textContent = newQuantity;
            
            // Update the total for this item
            const productPrice = getProductPriceById(productId);
            const itemTotal = (parseFloat(productPrice) * newQuantity).toFixed(2);
            document.getElementById(`total-${productId}`).textContent = `₱${itemTotal}`;
            
            // Update the cart total
            updateCartTotal();
            
            // Debounce the API call to avoid too many requests
            clearTimeout(window.sliderTimeout);
            window.sliderTimeout = setTimeout(async () => {
                const success = await updateCartQuantity(productId, newQuantity);
                if (!success) {
                    loadCartItems();
                }
            }, 500);
        }
        
        function updateCartTotal() {
            let cartTotal = 0;
            const rows = document.querySelectorAll('#cartItemsContainer tbody tr');
            
            rows.forEach(row => {
                if (!row.classList.contains('fw-bold')) { // Skip the total row
                    const totalCell = row.querySelector('td:nth-child(4)');
                    if (totalCell) {
                        const totalText = totalCell.textContent.replace('₱', '');
                        cartTotal += parseFloat(totalText);
                    }
                }
            });
            
            document.getElementById('cart-total').textContent = `₱${cartTotal.toFixed(2)}`;
        }

        async function loadWishlistItems() {
            const container = document.getElementById('wishlistItemsContainer');
            container.innerHTML = `<div class="text-center py-5 text-muted">Loading...</div>`;
            try {
                const r = await fetch(wishlistApi, { credentials: 'same-origin' });
                const d = await r.json();
                if (d.status !== 'success') throw new Error(d.message);
                
                if (!d.data.length) {
                    container.innerHTML = `<div class="text-center py-5 text-muted">Your wishlist is empty.</div>`;
                    return;
                }

                let html = `<ul class="list-group">`;
                d.data.forEach(item => {
                    let productName = getProductNameById(item.product_id);
                    if (!productName || productName.startsWith('Product #')) productName = 'Product removed';
                    let productPrice = getProductPriceById(item.product_id);
                    if (!productPrice || productPrice === '0.00') productPrice = '0.00';
                    html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">${productName}</div>
                                <div class="text-muted small">₱${productPrice}</div>
                            </div>
                            <button class="btn btn-sm btn-outline-danger" onclick="removeFromWishlist(${item.product_id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </li>`;
                });
                html += `</ul>`;
                container.innerHTML = html;
            } catch (e) {
                container.innerHTML = `<div class="alert alert-danger">Failed to load wishlist.</div>`;
            }
        }

        async function removeFromCart(productId) {
            if (!confirm("Remove this item from cart?")) return;
            await fetch(cartApi + "?product_id=" + productId, {
                method: "DELETE", credentials: 'same-origin'
            });
            await refreshCartCount();
            await loadCartItems();
            showToast('Product removed from cart!');
        }

        async function removeFromWishlist(productId) {
            if (!confirm("Remove this item from wishlist?")) return;
            await fetch(wishlistApi + "?product_id=" + productId, {
                method: "DELETE", credentials: 'same-origin'
            });
            await refreshWishlistCount();
            await loadWishlistItems();
            showToast('Product removed from wishlist!');
        }

        // Auto load when modal opens
        document.getElementById('cartModal').addEventListener('show.bs.modal', loadCartItems);
        document.getElementById('wishlistModal').addEventListener('show.bs.modal', loadWishlistItems);

    // Create shipment from cart - origin is Warehouse A, destination is user's address
    window.createShipmentFromCart = async function() {
            try {
                // load cart
                const r = await fetch(cartApi, { credentials: 'same-origin' });
                const d = await r.json();
                if (d.status !== 'success' || !Array.isArray(d.data) || d.data.length === 0) {
                    alert('Your cart is empty');
                    return;
                }

                // load user profile to get address
                const pr = await fetch(profileApi, { credentials: 'same-origin' });
                const pu = await pr.json();
                if (pu.status !== 'success') {
                    alert('Unable to determine shipping address. Please login and set your address.');
                    return;
                }

               // helper to format date into MySQL DATETIME
function formatDateToMySQL(date) {
    const pad = n => String(n).padStart(2, '0');
    return (
        date.getFullYear() + '-' +
        pad(date.getMonth() + 1) + '-' +
        pad(date.getDate()) + ' ' +
        pad(date.getHours()) + ':' +
        pad(date.getMinutes()) + ':' +
        pad(date.getSeconds())
    );
}

const itemsQuantity = d.data.reduce((s, it) => s + (Number(it.quantity) || 0), 0);
const destination = pu.data.address || `${pu.data.first_name || ''} ${pu.data.last_name || ''}`.trim();

// read payment method if present on page (fallback to COD)
const paymentMethodEl = document.getElementById && document.getElementById('paymentMethodSelect');
const paymentMethod = paymentMethodEl ? paymentMethodEl.value : 'cod';
const shipmentNumber = 'SHP-' + Date.now();

// format dispatch date for MySQL
const dispatchDate = formatDateToMySQL(new Date());

// create shipment
const sr = await fetch('http://localhost/caplog1/api/shipments.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: JSON.stringify({
        shipment_number: shipmentNumber,
        origin: 'Warehouse A',
        destination: destination,
        items_quantity: itemsQuantity,
        dispatch_date: dispatchDate, // ✅ FIXED format
        status: 'Pending',
        notes: 'Created from cart checkout (payment: ' + paymentMethod + ')',
        payment_method: paymentMethod,
        user_id: pu.data && pu.data.id ? pu.data.id : null
    })
});

                const sdata = await sr.json();
                if (!sr.ok) throw new Error(sdata.error || 'Failed to create shipment');

                // also create a delivery record linked to the shipment
                const deliveryNumber = 'DLV-' + Date.now();
                const dr = await fetch('http://localhost/caplog1/api/deliveries.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        delivery_number: deliveryNumber,
                        shipment_id: sdata.id || null,
                        origin: 'Warehouse A',
                        destination: destination,
                        items_quantity: itemsQuantity,
                        delivery_date: new Date().toISOString(),
                        status: 'Pending',
                        notes: 'Auto-created delivery for shipment ' + (sdata.id || '') + ' (payment: ' + paymentMethod + ')',
                        payment_method: paymentMethod,
                        user_id: pu.data && pu.data.id ? pu.data.id : null
                    })
                });
                const ddata = await dr.json();
                if (!dr.ok) throw new Error(ddata.error || 'Failed to create delivery');

                // clear cart
                try {
                    await fetch(cartApi, { method: 'DELETE', credentials: 'same-origin' });
                } catch (e) { console.warn('Failed to clear cart', e); }
                try { await refreshCartCount(); } catch (e) { /* noop */ }

                alert('Shipment and delivery created successfully');
                // refresh recent activity if available, then redirect to profile orders
                try { if (typeof loadRecentActivity === 'function') await loadRecentActivity(); } catch (e) { /* noop */ }
                window.location.href = 'profile.php?tab=orders';
            } catch (e) {
                console.error(e);
                alert('Failed to create shipment: ' + (e.message || e));
            }
        }

    </script>
</body>
</html>