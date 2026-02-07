<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Imarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        html{
            scroll-behavior: smooth;
        }
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
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1497215728101-856f4ea42174?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 60px;
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
        .team-member {
            transition: transform 0.3s;
        }
        .team-member:hover {
            transform: translateY(-5px);
        }
        .value-card {
            border: none;
            border-radius: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .stats-number {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(45deg, #0d6efd, #6f42c1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .timeline {
            position: relative;
            padding-left: 3rem;
            margin-left: 1rem;
        }
        .timeline:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #0d6efd, #6f42c1);
            border-radius: 10px;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 2.5rem;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -3.4rem;
            top: 0.5rem;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #0d6efd;
            border: 4px solid white;
            box-shadow: 0 0 0 3px #0d6efd;
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
            <a class="" href="index.php">
               <img src="../../images/logoBG.PNG" width="70" alt="" srcset="">
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
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="deals.html">Deals</a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About Us</a>
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
                         <a href="profile.php" id="navCartBtn" class="btn btn-outline-primary position-relative btn-cart">
                            <i class="bi bi-cart3"></i>
                            <span id="navCartCount" class="cart-count z-1">0</span>
                        </a>
                        <a href="profile.php" id="navWishlistBtn" class="btn btn-outline-primary position-relative">
                            <i class="bi bi-heart"></i>
                             <span id="navWishlistCount" class="cart-count z-1">0</span>
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

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">About Imarket</h1>
            <p class="lead">Discover our story, mission, and the team behind your favorite tech store</p>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container">
        <!-- Our Story Section -->
        <section class="mb-5">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="mb-4">Our Story</h2>
                    <p class="lead">Founded in 2010, Imarket began as a small startup with a big vision: to make cutting-edge technology accessible to everyone.</p>
                    <p>What started as a modest online store has grown into one of the leading technology retailers, serving customers across the country. Our journey began when our founder, Sarah Johnson, noticed how difficult it was for everyday consumers to find quality tech products at reasonable prices.</p>
                    <p>Today, we pride ourselves on offering a carefully curated selection of gadgets, electronics, and tech accessories from leading brands around the world. Our team of tech enthusiasts tests every product to ensure it meets our high standards before we offer it to you.</p>
                </div>
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80" alt="Our Story" class="img-fluid rounded shadow">
                </div>
            </div>
        </section>

        <!-- Mission & Vision Section -->
        <section class="mb-5 py-4 bg-light rounded">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="card value-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-4">
                                <i class="bi bi-bullseye display-4 text-primary"></i>
                            </div>
                            <h3 class="card-title">Our Mission</h3>
                            <p class="card-text">To democratize technology by making high-quality gadgets and electronics accessible and affordable to everyone, while providing exceptional customer service and expert guidance.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card value-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-4">
                                <i class="bi bi-eye display-4 text-primary"></i>
                            </div>
                            <h3 class="card-title">Our Vision</h3>
                            <p class="card-text">To become the most trusted destination for tech enthusiasts and everyday consumers alike, revolutionizing how people discover, learn about, and purchase technology products.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="mb-5 text-center">
            <h2 class="mb-5">Imarket By The Numbers</h2>
            <div class="row">
                <div class="col-md-3 col-6 mb-4">
                    <div class="stats-number">50,000+</div>
                    <p>Happy Customers</p>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stats-number">2,500+</div>
                    <p>Products Offered</p>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stats-number">13</div>
                    <p>Years in Business</p>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stats-number">98%</div>
                    <p>Customer Satisfaction</p>
                </div>
            </div>
        </section>

        <!-- Our Values Section -->
        <section class="mb-5">
            <h2 class="mb-4 text-center">Our Values</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card value-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-shield-check display-4 text-primary"></i>
                            </div>
                            <h4 class="card-title">Quality Assurance</h4>
                            <p class="card-text">Every product in our catalog undergoes rigorous testing to ensure it meets our high standards for performance and reliability.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card value-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-people display-4 text-primary"></i>
                            </div>
                            <h4 class="card-title">Customer First</h4>
                            <p class="card-text">Our customers are at the heart of everything we do. We're committed to providing exceptional service and support.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card value-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-lightbulb display-4 text-primary"></i>
                            </div>
                            <h4 class="card-title">Innovation</h4>
                            <p class="card-text">We continuously seek out the latest technological advancements to bring you cutting-edge products that enhance your life.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Timeline Section -->
        <section class="mb-5">
            <h2 class="mb-4 text-center">Our Journey</h2>
            <div class="timeline">
                <div class="timeline-item">
                    <h4>2010</h4>
                    <p>Imarket founded by Sarah Johnson with a small online storefront</p>
                </div>
                <div class="timeline-item">
                    <h4>2013</h4>
                    <p>Opened our first physical store in San Francisco</p>
                </div>
                <div class="timeline-item">
                    <h4>2015</h4>
                    <p>Reached 10,000 customers and expanded product catalog to 500+ items</p>
                </div>
                <div class="timeline-item">
                    <h4>2018</h4>
                    <p>Launched our mobile app for iOS and Android devices</p>
                </div>
                <div class="timeline-item">
                    <h4>2020</h4>
                    <p>Partnered with over 100 brands and expanded internationally</p>
                </div>
                <div class="timeline-item">
                    <h4>2023</h4>
                    <p>Celebrated 50,000+ satisfied customers and 2,500+ products</p>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="mb-5">
            <h2 class="mb-4 text-center">Meet Our Team</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card team-member h-100">
                        <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=774&q=80" class="card-img-top" alt="Sarah Johnson">
                        <div class="card-body text-center">
                            <h5 class="card-title">Sarah Johnson</h5>
                            <p class="text-muted">Founder & CEO</p>
                            <p class="card-text">With over 15 years in the tech industry, Sarah's vision continues to drive Imarket forward.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card team-member h-100">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=776&q=80" class="card-img-top" alt="Michael Chen">
                        <div class="card-body text-center">
                            <h5 class="card-title">Michael Chen</h5>
                            <p class="text-muted">Head of Product</p>
                            <p class="card-text">Michael ensures every product we carry meets our rigorous quality standards.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card team-member h-100">
                        <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=761&q=80" class="card-img-top" alt="Jessica Williams">
                        <div class="card-body text-center">
                            <h5 class="card-title">Jessica Williams</h5>
                            <p class="text-muted">Customer Experience Director</p>
                            <p class="card-text">Jessica leads our customer service team with a passion for creating exceptional experiences.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="mb-5 text-center py-5 bg-primary text-white rounded">
            <h2 class="mb-3">Ready to Explore Our Products?</h2>
            <p class="lead mb-4">Discover the latest tech gadgets at amazing prices</p>
            <a href="index.php" class="btn btn-light btn-lg">Shop Now</a>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                    <h5>Imarket</h5>
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
                        <li><i class="bi bi-envelope"></i> info@Imarket.com</li>
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
                <p>&copy; 2023 Imarket. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // simple navbar wiring: load cart/wishlist counts and profile image
        (function(){
            const cartCountEl = document.getElementById('navCartCount');
            const wishCountEl = document.getElementById('navWishlistCount');
            const profileImg = document.getElementById('navProfileImg');

            const cartApi = 'https://log1.imarketph.com/api/cart.php';
            const wishlistApi = 'https://log1.imarketph.com/api/wishlist.php';
            const profileApi = 'https://log1.imarketph.com/api/profile.php';

            async function refreshCounts(){
                try{
                    const [cr, wr] = await Promise.all([
                        fetch(cartApi + '?count=1', { credentials: 'same-origin' }),
                        fetch(wishlistApi + '?count=1', { credentials: 'same-origin' })
                    ]);
                    if (cr.ok){ const d = await cr.json(); if (d.status === 'success' && typeof d.count !== 'undefined') cartCountEl.textContent = d.count; }
                    if (wr.ok){ const d = await wr.json(); if (d.status === 'success' && typeof d.count !== 'undefined') wishCountEl.textContent = d.count; }
                }catch(e){ /* noop */ }
            }

            async function loadProfile(){
                try{
                    const r = await fetch(profileApi, { credentials: 'same-origin' });
                    if (!r.ok) return;
                    const d = await r.json();
                    if (d.status === 'success' && d.data && d.data.profile_image){
                        profileImg.src = 'https://log1.imarketph.com/uploads/' + d.data.profile_image;
                    }
                }catch(e){ /* noop */ }
            }

            document.addEventListener('DOMContentLoaded', ()=>{
                refreshCounts();
                loadProfile();
            });
        })();
    </script>
</body>
</html>