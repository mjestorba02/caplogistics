<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechShop - Electronics Store</title>
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
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 50px 0;
            }
            .display-4 {
                font-size: 2.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
            <a class="" href="#">
               <img src="images/logoBG.PNG" width="70" alt="" srcset="">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index_vendor.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#products" id="">Products</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="#">Categories</a>
                    </li> -->
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="deals.php">Deals</a>
                    </li> -->
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
                
                    <div class="dropdown">
                        <a href="https://log1.imarketph.com/pages/vendor/login.php" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" aria-expanded="false">
                          login
                        </a>
                      
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Tech Deals of the Season</h1>
            <p class="lead">Up to 40% off on latest gadgets and electronics</p>
            <a href="https://log1.imarketph.com/pages/vendor/login.php" class="btn btn-primary btn-lg mt-3">Shop Now</a>
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
                                <a class="btn btn-primary me-md-2" href="https://log1.imarketph.com/pages/vendor/login.php">Add to Cart</a>
                                <a class="btn btn-outline-secondary" href="https://log1.imarketph.com/pages/vendor/login.php">Add to Wishlist</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // API endpoint - replace with your actual API URL
        const apiUrl = "https://log1.imarketph.com/api/inventory.php";
        
        // Function to fetch products from the API
        async function fetchProducts() {
            try {
                const response = await fetch(apiUrl);
                if (!response.ok) {
                    throw new Error('Failed to fetch products');
                }
                
                const products = await response.json();
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
                const imageUrl = product.product_photo_url && product.product_photo_url !== 'https://log1.imarketph.com/uploads/products/' 
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
                                        <a class="btn btn-success" href="https://log1.imarketph.com/pages/vendor/login.php">View</a>
                                        <a class="btn btn-warning" href="https://log1.imarketph.com/pages/vendor/login.php">${product.stock_level <= 0 ? 'Out of Stock' : 'Add to cart'}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            
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
            
            // Set image
            const imageUrl = product.product_photo_url && product.product_photo_url !== 'https://log1.imarketph.com/uploads/products/' 
                ? product.product_photo_url 
                : 'https://via.placeholder.com/500x400?text=Product+Image';
            document.getElementById('modalProductImage').src = imageUrl;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('productDetailModal'));
            modal.show();
        }
        
        // Fetch products when the page loads
        document.addEventListener('DOMContentLoaded', fetchProducts);
        
            // Create shipment from cart - origin is Warehouse A
            window.createShipmentFromCart = async function() {
                try {
                    const r = await fetch('/logistics1_ecommerce/api/cart.php', { credentials: 'same-origin' });
                    const d = await r.json();
                    if (d.status !== 'success' || !Array.isArray(d.data) || d.data.length === 0) {
                        alert('Your cart is empty');
                        return;
                    }

                    const pr = await fetch('/logistics1_ecommerce/api/profile.php', { credentials: 'same-origin' });
                    const pu = await pr.json();
                    if (pu.status !== 'success') {
                        alert('Unable to determine shipping address. Please login and set your address.');
                        return;
                    }

                    const itemsQuantity = d.data.reduce((s, it) => s + (Number(it.quantity) || 0), 0);
                    const destination = pu.data.address || `${pu.data.first_name || ''} ${pu.data.last_name || ''}`.trim();
                    // try to read payment method from page (fallback to COD)
                    const paymentMethodEl = document.getElementById && document.getElementById('paymentMethodSelect');
                    const paymentMethod = paymentMethodEl ? paymentMethodEl.value : 'cod';
                    const shipmentNumber = 'SHP-' + Date.now();

                    const sr = await fetch('/logistics1_ecommerce/api/shipments.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            shipment_number: shipmentNumber,
                            origin: 'Warehouse A',
                            destination: destination,
                            items_quantity: itemsQuantity,
                            dispatch_date: new Date().toISOString(),
                            status: 'Pending',
                            notes: 'Created from cart checkout (payment: ' + paymentMethod + ')',
                            payment_method: paymentMethod,
                            user_id: pu.data && pu.data.id ? pu.data.id : null
                        })
                    });
                    const sdata = await sr.json();
                    if (!sr.ok) throw new Error(sdata.error || 'Failed to create shipment');

                    const deliveryNumber = 'DLV-' + Date.now();
                    const dr = await fetch('/logistics1_ecommerce/api/deliveries.php', {
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
                        await fetch('/logistics1_ecommerce/api/cart.php', { method: 'DELETE', credentials: 'same-origin' });
                    } catch (e) { console.warn('Failed to clear cart', e); }
                    try { if (typeof refreshCartCount === 'function') await refreshCartCount(); } catch (e) { /* noop */ }

                    alert('Shipment and delivery created successfully');
                    try { if (typeof loadRecentActivity === 'function') await loadRecentActivity(); } catch (e) { /* noop */ }
                    window.location.href = 'https://log1.imarketph.com/pages/vendor/login.php';
                } catch (e) {
                    console.error(e);
                    alert('Failed to create shipment: ' + (e.message || e));
                }
            }
    </script>
</body>
</html>