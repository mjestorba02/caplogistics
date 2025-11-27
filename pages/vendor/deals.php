<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deals - TechShop</title>
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
        .btn-cart {
            position: relative;
        }
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1607082350899-7e105aa886ae?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
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
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .badge-discount {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.9rem;
            padding: 5px 10px;
            z-index: 10;
        }
        .original-price {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .countdown-timer {
            background: linear-gradient(45deg, #ff6b6b, #ff9e7d);
            color: white;
            padding: 8px 15px;
            border-radius: 30px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }
        .deal-card {
            border: 2px solid #0d6efd;
            border-radius: 10px;
            overflow: hidden;
        }
        .deal-card .card-header {
            background: linear-gradient(45deg, #0d6efd, #6f42c1);
            color: white;
            font-weight: 600;
        }
        .section-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(45deg, #0d6efd, #6f42c1);
            border-radius: 2px;
        }
        .progress {
            height: 8px;
            margin-bottom: 10px;
        }
        .deal-progress .progress-bar {
            background: linear-gradient(45deg, #0d6efd, #6f42c1);
        }
        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
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
            <a class="navbar-brand" href="#">
                <span class="logo-text">TechShop</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#products">Products</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="#">Categories</a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link active" href="deals.php">Deals</a>
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
                        <a href="#" class="btn btn-outline-primary position-relative btn-cart">
                            <i class="bi bi-cart3"></i>
                            <span class="cart-count z-1">3</span>
                        </a>
                        <a href="#" class="btn btn-outline-primary">
                            <i class="bi bi-heart"></i>
                             <span class="cart-count z-1">5</span>
                        </a>

                    </div>
                    <div class="dropdown">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://github.com/mdo.png" alt="mdo" width="32" height="32" class="rounded-circle">
                        </a>
                        <ul class="dropdown-menu text-small">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="profile.php">Orders</a></li>
                            <li><a class="dropdown-item" href="profile.php">Wishlist</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Special Deals & Offers</h1>
            <p class="lead">Limited-time discounts on the latest tech products. Don't miss out!</p>
            <div class="countdown-timer mt-3">
                <i class="bi bi-clock"></i> <span id="countdown">24:00:00</span> left for today's deals
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container">
        <!-- Flash Sale Section -->
        <section class="mb-5">
            <h2 class="section-title">Flash Sale <span class="badge bg-danger">Ending Soon</span></h2>
            <div class="row">
                <div class="col-md-8">
                    <div class="deal-card mb-4">
                        <div class="card-header text-center">Today's Featured Deal</div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <div class="card-img-container">
                                        <img src="https://images.unsplash.com/photo-1593642632823-8f785ba67e45?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1812&q=80" alt="Ultrabook Laptop">
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <h3>Premium Ultrabook</h3>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="original-price me-2">$1,299.99</span>
                                        <span class="fw-bold fs-4 text-danger">$999.99</span>
                                        <span class="badge bg-danger ms-2">23% OFF</span>
                                    </div>
                                    <p class="text-muted">Lightweight, powerful, with all-day battery life. Perfect for professionals on the go.</p>
                                    <div class="deal-progress mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Sold: 78%</span>
                                            <span>Limited stock</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: 78%" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2 d-md-flex">
                                        <button class="btn btn-warning btn-lg me-md-2"><i class="bi bi-cart3"></i> Add to Cart</button>
                                        <button class="btn btn-outline-primary btn-lg"><i class="bi bi-lightning"></i> Buy Now</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            <i class="bi bi-megaphone display-4 text-primary mb-3"></i>
                            <h3>Deal Alert</h3>
                            <p class="text-muted">Get notified when new deals are available</p>
                            <div class="mt-3">
                                <div class="input-group mb-3">
                                    <input type="email" class="form-control" placeholder="Your email">
                                    <button class="btn btn-primary" type="button">Notify Me</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Deals -->
        <section class="mb-5">
            <h2 class="section-title">Featured Deals</h2>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                <!-- Deal 1 -->
                <div class="col">
                    <div class="card h-100">
                        <span class="badge bg-danger badge-discount">30% OFF</span>
                        <div class="card-img-container">
                            <img src="https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80" alt="Gaming Console">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Gaming Console</h5>
                            <div class="d-flex align-items-center mb-2">
                                <span class="original-price me-2">$499.99</span>
                                <span class="fw-bold text-danger">$349.99</span>
                            </div>
                            <p class="card-text">Next-gen gaming console with 4K resolution and immersive gameplay experience.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-success">View</button>
                                    <button type="button" class="btn btn-sm btn-warning">Add to cart</button>
                                </div>
                                <small class="text-muted">12 left</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Deal 2 -->
                <div class="col">
                    <div class="card h-100">
                        <span class="badge bg-danger badge-discount">25% OFF</span>
                        <div class="card-img-container">
                            <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1764&q=80" alt="Smart Watch">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Smart Watch Series 5</h5>
                            <div class="d-flex align-items-center mb-2">
                                <span class="original-price me-2">$329.99</span>
                                <span class="fw-bold text-danger">$247.49</span>
                            </div>
                            <p class="card-text">Advanced health monitoring and smartphone connectivity in a sleek design.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-success">View</button>
                                    <button type="button" class="btn btn-sm btn-warning">Add to cart</button>
                                </div>
                                <small class="text-muted">20 left</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Deal 3 -->
                <div class="col">
                    <div class="card h-100">
                        <span class="badge bg-danger badge-discount">40% OFF</span>
                        <div class="card-img-container">
                            <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80" alt="Headphones">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Wireless Headphones</h5>
                            <div class="d-flex align-items-center mb-2">
                                <span class="original-price me-2">$199.99</span>
                                <span class="fw-bold text-danger">$119.99</span>
                            </div>
                            <p class="card-text">Premium noise cancellation and exceptional sound quality for music lovers.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-success">View</button>
                                    <button type="button" class="btn btn-sm btn-warning">Add to cart</button>
                                </div>
                                <small class="text-muted">8 left</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Daily Deals -->
        <section class="mb-5">
            <h2 class="section-title">Daily Deals</h2>
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img src="https://images.unsplash.com/photo-1581291518633-83b4ebd1d83e?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80" alt="Tablet" class="img-fluid rounded">
                                </div>
                                <div class="col-md-6">
                                    <h4>10.5" Tablet</h4>
                                    <p class="text-muted">High-resolution display, powerful processor, and all-day battery life.</p>
                                    <span class="badge bg-info">Starts in 3 hours</span>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="original-price d-block">$599.99</span>
                                    <span class="fw-bold text-danger fs-5">$499.99</span>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-outline-primary w-100">Notify Me</button>
                                </div>
                            </div>
                        </div>
                    </div>
                        
                    
                    
                    <!-- <div class="card mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img src="https://images.unsplash.com/photo-1541807084-5c52b6b3adef?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80" alt="Camera" class="img-fluid rounded">
                                </div>
                                <div class="col-md-6">
                                    <h4>DSLR Camera Bundle</h4>
                                    <p class="text-muted">Professional camera with 4K video and two lens kit.</p>
                                    <span class="badge bg-info">Starts tomorrow</span>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="original-price d-block">$899.99</span>
                                    <span class="fw-bold text-danger fs-5">$749.99</span>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-outline-primary w-100">Notify Me</button>
                                </div>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>
        </section>

        <!-- Category Deals -->
        <section class="mb-5">
            <h2 class="section-title">Category Deals</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-dark">
                        <img src="https://images.unsplash.com/photo-1593640408182-31c70c8268f5?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1772&q=80" class="card-img" alt="Computers" style="opacity: 0.7;">
                        <div class="card-img-overlay d-flex flex-column justify-content-center text-center">
                            <h3 class="card-title">Computers & Laptops</h3>
                            <p class="card-text">Up to 40% off on selected models</p>
                            <a href="#" class="btn btn-light align-self-center">Shop Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-dark">
                        <img src="https://images.unsplash.com/photo-1616348436168-de43ad0db179?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1772&q=80" class="card-img" alt="Smartphones" style="opacity: 0.7;">
                        <div class="card-img-overlay d-flex flex-column justify-content-center text-center">
                            <h3 class="card-title">Smartphones</h3>
                            <p class="card-text">Latest models with special discounts</p>
                            <a href="#" class="btn btn-light align-self-center">Shop Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-dark">
                        <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80" class="card-img" alt="Audio" style="opacity: 0.7;">
                        <div class="card-img-overlay d-flex flex-column justify-content-center text-center">
                            <h3 class="card-title">Audio & Headphones</h3>
                            <p class="card-text">Premium sound at discounted prices</p>
                            <a href="#" class="btn btn-light align-self-center">Shop Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Newsletter Section -->
        <section class="mb-5">
            <div class="bg-primary text-white rounded p-5">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2>Never Miss a Deal</h2>
                        <p>Subscribe to our newsletter and be the first to know about exclusive offers, limited-time discounts, and new product launches.</p>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-lg">
                            <input type="email" class="form-control" placeholder="Your email address">
                            <button class="btn btn-light" type="button">Subscribe</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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
                        <li><a href="#" class="text-white">Deals</a></li>
                        <li><a href="#" class="text-white">About Us</a></li>
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
                    <h5>Deal Alerts</h5>
                    <p>Get notified about flash sales and exclusive deals.</p>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Countdown timer
        function updateCountdown() {
            const now = new Date();
            const endOfDay = new Date();
            endOfDay.setHours(23, 59, 59, 999);
            
            const diff = endOfDay - now;
            
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            document.getElementById('countdown').innerHTML = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        setInterval(updateCountdown, 1000);
        updateCountdown();
    </script>
</body>
</html>