<?php
session_start();
if (!isset($_SESSION['vendor_id'])) {
    header('Location: https://log1.imarketph.com/pages/vendor/login.php');
    exit();
}
// If vendor is viewing a specific user's profile, a user_id can be provided via GET
$profile_user_id = isset($_GET['user_id']) && $_GET['user_id'] !== '' ? intval($_GET['user_id']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Imarket</title>
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
        .profile-hero {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.9) 0%, rgba(111, 66, 193, 0.85) 100%), 
                        url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0 80px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .profile-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.5;
        }
        
        .profile-img-container {
            position: relative;
            display: inline-block;
            margin-bottom: 25px;
        }
        
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .profile-img:hover {
            transform: scale(1.05);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        .profile-status {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #198754;
            border: 3px solid white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .profile-name {
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        .profile-bio {
            max-width: 600px;
            margin: 0 auto 25px;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .profile-badges {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .profile-badge {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 8px 18px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .profile-badge:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }
        
        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
            padding: 0 20px;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
            background: linear-gradient(45deg, #ffffff, #e0e0e0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .profile-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn-gradient {
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: white;
            border-radius: 50px;
            padding: 10px 25px;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.2));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: white;
        }
        
        .btn-primary-solid {
            background: linear-gradient(45deg, #0d6efd, #6f42c1);
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            transition: all 0.3s ease;
        }
        
        .btn-primary-solid:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
        }
        
        @media (max-width: 768px) {
            .profile-hero {
                padding: 80px 0 60px;
            }
            
            .profile-img {
                width: 120px;
                height: 120px;
            }
            
            .profile-stats {
                gap: 15px;
            }
            
            .stat-item {
                padding: 0 10px;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .profile-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .profile-actions .btn {
                width: 200px;
            }
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
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .profile-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
            border: none;
        }
        .profile-card:hover {
            transform: translateY(-5px);
        }
        .sidebar-nav .nav-link {
            color: #495057;
            font-weight: 500;
            border-radius: 8px;
            margin-bottom: 8px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        .sidebar-nav .nav-link:hover {
            background-color: #f8f9fa;
        }
        .sidebar-nav .nav-link.active {
            background: linear-gradient(45deg, #0d6efd, #6f42c1);
            color: white;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
        }
        .sidebar-nav .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 10px;
        }
        .stats-number {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(45deg, #0d6efd, #6f42c1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .stats-item {
            text-align: center;
            padding: 15px 0;
        }
        .stats-item small {
            color: #6c757d;
            font-weight: 500;
        }
        .upload-btn {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        .upload-btn input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 20px;
        }
        .card-header h5 {
            margin: 0;
            font-weight: 600;
        }
        .card-body {
            padding: 25px;
        }
        .activity-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .modal-content {
            border-radius: 15px;
            overflow: hidden;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .modal-header {
            background: linear-gradient(45deg, #0d6efd, #6f42c1);
            color: white;
            padding: 20px;
        }
        .modal-body {
            padding: 25px;
        }
        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e9ecef;
        }
        @media (max-width: 768px) {
            .profile-hero {
                padding: 60px 0;
            }
            .display-4 {
                font-size: 2.2rem;
            }
            .profile-img {
                width: 120px;
                height: 120px;
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
                        <a class="nav-link" href="index.php#products.php">Products</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="deals.php">Deals</a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link active" href="profile.html">Profile</a>
                    </li> -->
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
                        <a href="#" class="btn btn-outline-primary position-relative" data-bs-toggle="modal" data-bs-target="#wishlistModal">
                            <i class="bi bi-heart"></i>
                            <span id="wishlistCount" class="cart-count z-1">0</span>
                        </a>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <img id="navProfileImg" src="https://github.com/mdo.png" alt="profile" width="32" height="32" class="rounded-circle ">
                        </a>
                        <ul class="dropdown-menu text-small">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-bag me-2"></i>Orders</a></li>
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-heart me-2"></i>Wishlist</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
 <div class="profile-hero" <?php if ($profile_user_id) echo 'data-profile-user-id="' . htmlspecialchars($profile_user_id) . '"'; ?>>
        <div class="container text-center">
            <div class="profile-img-container">
                <img src="https://github.com/mdo.png" alt="Profile" class="profile-img">
                <div class="profile-status" title="Online now"></div>
            </div>
            
            <h1 class="profile-name display-5 fw-bold">John Doe</h1>
            
            <p class="profile-bio">Tech enthusiast and gadget collector. Love exploring new technologies and sharing discoveries with the community.</p>
            
            <div class="profile-badges">
                <span class="profile-badge">
                    <i class="bi bi-geo-alt "></i> <span id="profileLocation">San Francisco, CA</span>
                </span>
                <span class="profile-badge">
                    <i class="bi bi-award text-warning"></i> Gold Member
                </span>
                <span class="profile-badge">
                    <i class="bi bi-patch-check text-primary "></i> Verified
                </span>
            </div>
            
            <div class="profile-stats">
                <div class="stat-item">
                    <div class="stat-number">12</div>
                    <div class="stat-label">Orders</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">7</div>
                    <div class="stat-label">Wishlist</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">3</div>
                    <div class="stat-label">Reviews</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">2</div>
                    <div class="stat-label">Coupons</div>
                </div>
            </div>
<!--             
            <div class="profile-actions">
                <button class="btn btn-gradient">
                    <i class="bi bi-chat me-2"></i> Send Message
                </button>
                <button class="btn btn-primary-solid">
                    <i class="bi bi-pencil me-2"></i> Edit Profile
                </button>
                <button class="btn btn-gradient">
                    <i class="bi bi-share me-2"></i> Share Profile
                </button>
            </div>
            
            <div class="mt-4">
                <small class="opacity-75">Premium Member since January 2020</small>
            </div> -->
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mb-5">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3">
                <div class="profile-card">
                    <div class="card-body p-3">
                        <div class="d-flex flex-column sidebar-nav nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <button class="nav-link active text-start" id="v-pills-home-tab" data-bs-toggle="pill" data-bs-target="#v-pills-home" type="button" role="tab" aria-controls="v-pills-home" aria-selected="true">
                                <i class="bi bi-person"></i> Profile Overview
                            </button>
                            <button class="nav-link text-start" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                                <i class="bi bi-pencil"></i> Edit Profile
                            </button>
                            <button class="nav-link text-start" id="v-pills-orders-tab" data-bs-toggle="pill" data-bs-target="#v-pills-orders" type="button" role="tab" aria-controls="v-pills-orders" aria-selected="false">
                                <i class="bi bi-bag"></i> Orders
                            </button>
                            <button class="nav-link text-start" id="v-pills-wishlist-tab" data-bs-toggle="pill" data-bs-target="#v-pills-wishlist" type="button" role="tab" aria-controls="v-pills-wishlist" aria-selected="false">
                                <i class="bi bi-heart"></i> Wishlist
                            </button>
                            <button class="nav-link text-start" id="v-pills-settings-tab" data-bs-toggle="pill" data-bs-target="#v-pills-settings" type="button" role="tab" aria-controls="v-pills-settings" aria-selected="false">
                                <i class="bi bi-gear"></i> Account Settings
                            </button>
                            <button class="nav-link text-start" id="v-pills-payment-tab" data-bs-toggle="pill" data-bs-target="#v-pills-payment" type="button" role="tab" aria-controls="v-pills-payment" aria-selected="false">
                                <i class="bi bi-credit-card"></i> Payment Methods
                            </button>
                            <hr class="my-2">
                            <button class="nav-link text-start text-danger" id="v-pills-logout-tab">
                               <i class="bi bi-box-arrow-right"></i>  <a href="logout.php">Logout</a>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Card -->
                <div class="profile-card">
                    <div class="card-body p-4 text-center">
                        <h5 class="card-title mb-4">Your Activity</h5>
                        <div class="row">
                            <div class="col-6 stats-item">
                                <div class="stats-number">12</div>
                                <small>Orders</small>
                            </div>
                            <div class="col-6 stats-item">
                                <div class="stats-number">7</div>
                                <small>Wishlist</small>
                            </div>
                            <div class="col-6 stats-item">
                                <div class="stats-number">3</div>
                                <small>Reviews</small>
                            </div>
                            <div class="col-6 stats-item">
                                <div class="stats-number">2</div>
                                <small>Coupons</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-lg-9">
                <div class="tab-content" id="v-pills-tabContent">
                    <!-- Profile Overview -->
                    <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                        <div class="profile-card">
                            <div class="card-header">
                                <h5 class="mb-0">Profile Overview</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mb-4">Personal Information</h6>
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="120">Name</th>
                                                <td>John Doe</td>
                                            </tr>
                                            <tr>
                                                <th>Email</th>
                                                <td>john.doe@example.com</td>
                                            </tr>
                                            <tr>
                                                <th>Phone</th>
                                                <td>(555) 123-4567</td>
                                            </tr>
                                            <tr>
                                                <th>Member Since</th>
                                                <td>January 12, 2020</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-4">Shipping Address</h6>
                                        <address class="mb-4">
                                            <strong>John Doe</strong><br>
                                            123 Tech Street<br>
                                            San Francisco, CA 94103<br>
                                            United States<br>
                                            <i class="bi bi-telephone"></i> (555) 123-4567
                                        </address>
                                        <button class="btn btn-outline-primary btn-sm">Edit Address</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="profile-card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Activity</h5>
                            </div>
                            <div class="card-body">
                                <div class="activity-item">
                                    <div class="activity-badge bg-success">
                                        <i class="bi bi-bag text-white"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <span>You placed order #TS-4892</span>
                                            <small class="text-muted">2 hours ago</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-badge bg-info">
                                        <i class="bi bi-chat-text text-white"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <span>You reviewed "Wireless Headphones"</span>
                                            <small class="text-muted">1 day ago</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-badge bg-warning">
                                        <i class="bi bi-heart text-white"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <span>You added "Smart Watch" to your wishlist</span>
                                            <small class="text-muted">3 days ago</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Profile -->
                    <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                        <div class="profile-card">
                            <div class="card-header">
                                <h5 class="mb-0">Edit Profile</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-column align-items-center mb-4">
                                    <img src="https://github.com/mdo.png" alt="Profile" class="profile-img mb-3" id="profile-preview">
                                    <div class="upload-btn-wrapper btn btn-primary">
                                        <i class="bi bi-camera me-2"></i> Upload Photo
                                        <input type="file" id="profile-upload" accept="image/*" />
                                    </div>
                                    <small class="text-muted mt-2">Recommended: Square JPG, PNG, max 2MB</small>
                                </div>

                                <form>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="firstName" class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="firstName" value="John">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="lastName" class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="lastName" value="Doe">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" id="address" rows="2" placeholder="Street, City, State, ZIP">123 Tech Street, San Francisco, CA 94103</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" value="john.doe@example.com" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" value="(555) 123-4567">
                                    </div>
                                    <div class="mb-3">
                                        <label for="bio" class="form-label">Bio</label>
                                        <textarea class="form-control" id="bio" rows="3">Tech enthusiast and gadget collector. Love exploring new technologies!</textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Other tab panes would go here with similar structure -->
                    <div class="tab-pane fade" id="v-pills-orders" role="tabpanel" aria-labelledby="v-pills-orders-tab">
                        <div class="profile-card">
                            <div class="card-header">
                                <h5 class="mb-0">Your Orders</h5>
                            </div>
                            <div class="card-body">
                                <div id="ordersContainer">
                                                    <div class="text-center text-muted">You haven't placed any orders yet.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="v-pills-wishlist" role="tabpanel" aria-labelledby="v-pills-wishlist-tab">
                        <div class="profile-card">
                            <div class="card-header">
                                <h5 class="mb-0">Your Wishlist</h5>
                            </div>
                            <div class="card-body">
                                <div id="profileWishlistContainer">
                                    <div class="text-center text-muted">Your wishlist is empty.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                        <div class="profile-card">
                            <div class="card-header">
                                <h5 class="mb-0">Account Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <h6>Change Password</h6>
                                    <form>
                                        <div class="mb-3">
                                            <label for="currentPassword" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="currentPassword">
                                        </div>
                                        <div class="mb-3">
                                            <label for="newPassword" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="newPassword">
                                        </div>
                                        <div class="mb-3">
                                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirmPassword">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Update Password</button>
                                    </form>
                                </div>

                                <hr>

                                <div class="mb-4">
                                    <h6>Email Preferences</h6>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="promotionalEmails" checked>
                                        <label class="form-check-label" for="promotionalEmails">Receive promotional emails</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="productUpdates" checked>
                                        <label class="form-check-label" for="productUpdates">Receive product updates</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="newsletter" checked>
                                        <label class="form-check-label" for="newsletter">Subscribe to newsletter</label>
                                    </div>
                                </div>

                                <hr>

                                <div>
                                    <h6 class="text-danger">Danger Zone</h6>
                                    <p class="text-muted">Once you delete your account, there is no going back. Please be certain.</p>
                                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">Delete Account</button>
                                </div>
                                
                                <hr>

                                <div>
                                    <h6>Your Wishlist</h6>
                                    <p class="text-muted">Manage items you saved for later.</p>
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#wishlistModal">
                                        View Wishlist (<span id="settingsWishlistCount">0</span>)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="v-pills-payment" role="tabpanel" aria-labelledby="v-pills-payment-tab">
                        <div class="profile-card">
                            <div class="card-header">
                                <h5 class="mb-0">Payment Methods</h5>
                            </div>
                            <div class="card-body text-center">
                                <p class="text-muted">Only Cash on Delivery (COD) is supported at the moment. You cannot add card or e-wallet methods.</p>
                                <div class="mb-3 d-flex justify-content-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="savedPayment" id="codOption" checked disabled>
                                        <label class="form-check-label" for="codOption">Cash on Delivery (COD)</label>
                                    </div>
                                </div>
                                <button class="btn btn-primary" id="addPaymentBtn">Add Payment Method</button>
                            </div>
                        </div>
                    </div>
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
                        <li><a href="index.php" class="text-white">Home</a></li>
                        <li><a href="products.php" class="text-white">Products</a></li>
                        <!-- <li><a href="deals.php" class="text-white">Deals</a></li> -->
                        <li><a href="about.php" class="text-white">About Us</a></li>
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

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAccountModalLabel">Delete Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete your account? This action cannot be undone.</p>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmDelete">
                        <label class="form-check-label" for="confirmDelete">
                            I understand that all my data will be permanently deleted
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="deleteAccountBtn" disabled>Delete Account</button>
                </div>
            </div>
        </div>
    </div>

        <!-- Cart Modal -->
        <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cartModalLabel"><i class="bi bi-cart3"></i> My Cart</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="cartItemsContainer">
                        <div class="text-center py-5 text-muted">Your cart is empty.</div>
                    </div>
                            <div class="modal-footer">
                                <div class="me-auto d-flex align-items-center gap-2">
                                    <label class="mb-0">Payment:</label>
                                    <!-- Only COD available for now -->
                                    <select id="paymentMethodSelect" class="form-select form-select-sm" style="width:200px;" disabled>
                                        <option value="cod" selected>Cash on Delivery (COD)</option>
                                    </select>
                                    <small class="text-muted ms-2">Only Cash on Delivery is available at the moment.</small>
                                </div>
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="checkoutBtn">Proceed to Checkout</button>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="wishlistItemsContainer">
                        <div class="text-center py-5 text-muted">Your wishlist is empty.</div>
                    </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                </div>
            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // // Profile picture upload preview
        // document.getElementById('profile-upload').addEventListener('change', function(e) {
        //     const file = e.target.files[0];
        //     if (file) {
        //         const reader = new FileReader();
        //         reader.onload = function(e) {
        //             document.getElementById('profile-preview').src = e.target.result;
        //         }
        //         reader.readAsDataURL(file);
        //     }
        // });

        // // Enable delete button only when checkbox is checked
        // document.getElementById('confirmDelete').addEventListener('change', function() {
        //     document.getElementById('deleteAccountBtn').disabled = !this.checked;
        // });

        // // Tab persistence
        // const triggerTabList = document.querySelectorAll('#v-pills-tab button');
        // triggerTabList.forEach(triggerEl => {
        //     triggerEl.addEventListener('click', function() {
        //         if (this.id !== 'v-pills-logout-tab') {
        //             localStorage.setItem('lastTab', this.id);
        //         }
        //     });
        // });

        // // Activate last visited tab if available
        // window.addEventListener('DOMContentLoaded', () => {
        //     const lastTab = localStorage.getItem('lastTab');
        //     if (lastTab) {
        //         const tab = new bootstrap.Tab(document.querySelector(`#${lastTab}`));
        //         tab.show();
        //     }
        // });
    </script>
    
<script>
    // API base
    let API_BASE_URL = 'https://log1.imarketph.com/api/profile.php';
    // If this vendor page is viewing a specific user's profile, include user_id so API returns that user's data
    (function(){
        const profileEl = document.querySelector('[data-profile-user-id]');
        if (profileEl) {
            const uid = profileEl.getAttribute('data-profile-user-id');
            if (uid) {
                API_BASE_URL += '?user_id=' + encodeURIComponent(uid);
            }
        }
    })();

    function buildUploadUrl(fileName) {
        return `https://log1.imarketph.com/uploads/${fileName}`;
    }

    function updateHero(user) {
        const nameEl = document.querySelector('.profile-name');
        if (nameEl) nameEl.textContent = `${user.first_name || ''} ${user.last_name || ''}`.trim();
        const bioEl = document.querySelector('.profile-bio');
        if (bioEl) bioEl.textContent = user.bio && user.bio.trim() !== '' ? user.bio : 'No bio provided yet.';
        const imgEl = document.querySelector('.profile-img');
        if (imgEl && user.profile_image) {
            imgEl.src = buildUploadUrl(user.profile_image);
        }
        const loc = document.getElementById('profileLocation');
        if (loc) loc.textContent = user.address_short || (user.address || 'No address');
    }

    function updateOverview(user) {
        const table = document.querySelector('.table.table-borderless');
        if (table) {
            const rows = table.querySelectorAll('tr');
            if (rows.length >= 1) rows[0].querySelector('td').textContent = `${user.first_name || ''} ${user.last_name || ''}`.trim();
            if (rows.length >= 2) rows[1].querySelector('td').textContent = user.email || '';
            if (rows.length >= 3) rows[2].querySelector('td').textContent = user.phone || '';
        }
        const addressEl = document.querySelector('address');
        if (addressEl) {
            const fullName = `${user.first_name || ''} ${user.last_name || ''}`.trim();
            const phone = user.phone || '';
            const addr = user.address || 'No address on file';
            addressEl.innerHTML = `<strong>${fullName}</strong><br>${addr}<br><i class="bi bi-telephone"></i> ${phone}`;
        }
    }

    function updateForm(user) {
        const firstName = document.getElementById('firstName');
        const lastName = document.getElementById('lastName');
        const email = document.getElementById('email');
        const phone = document.getElementById('phone');
        const bio = document.getElementById('bio');
        const address = document.getElementById('address');
        const preview = document.getElementById('profile-preview');
        if (firstName) firstName.value = user.first_name || '';
        if (lastName) lastName.value = user.last_name || '';
        if (email) { email.value = user.email || ''; email.readOnly = true; }
        if (phone) phone.value = user.phone || '';
        if (bio) bio.value = user.bio || '';
        if (address) address.value = user.address || '';
        if (preview && user.profile_image) preview.src = buildUploadUrl(user.profile_image);
    }

    async function loadUserData() {
        const resp = await fetch(API_BASE_URL, { credentials: 'same-origin' });
        if (resp.status === 401) {
            window.location.href = 'login.php';
            return;
        }
        if (!resp.ok) {
            throw new Error('Failed to load profile: ' + resp.status);
        }
        const data = await resp.json();
        if (data.status !== 'success') {
            throw new Error(data.message || 'Failed to load profile');
        }
        const user = data.data;
        updateHero(user);
        updateOverview(user);
        updateForm(user);
    }

    function wireImagePreview() {
        const upload = document.getElementById('profile-upload');
        const preview = document.getElementById('profile-preview');
        if (!upload || !preview) return;
        upload.addEventListener('change', (e) => {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => { preview.src = ev.target.result; };
            reader.readAsDataURL(file);
        });
    }

    function wireEditFormSubmit() {
        const form = document.querySelector('#v-pills-profile form');
        if (!form) return;
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData();
            const firstName = document.getElementById('firstName')?.value || '';
            const lastName  = document.getElementById('lastName')?.value || '';
            const phone     = document.getElementById('phone')?.value || '';
            const bio       = document.getElementById('bio')?.value || '';
            const address   = document.getElementById('address')?.value || '';
            if (firstName) fd.append('first_name', firstName);
            if (lastName)  fd.append('last_name', lastName);
            if (phone)     fd.append('phone', phone);
            if (bio)       fd.append('bio', bio);
            if (address)   fd.append('address', address);
            const file = document.getElementById('profile-upload')?.files?.[0];
            if (file) fd.append('profile_image', file);
            try {
                const resp = await fetch(API_BASE_URL, { method: 'POST', credentials: 'same-origin', body: fd });
                const data = await resp.json();
                if (resp.ok && data.status === 'success') {
                    alert('Profile updated successfully');
                    await loadUserData();
                } else {
                    alert('Update failed: ' + (data.message || resp.status));
                }
            } catch (err) {
                console.error(err);
                alert('Update failed. Please try again.');
            }
        });
    }



    function wirePasswordChange() {
        const settingsTab = document.querySelector('#v-pills-settings');
        if (!settingsTab) return;
        const form = settingsTab.querySelector('form');
        if (!form) return;
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const current = document.getElementById('currentPassword')?.value || '';
            const next    = document.getElementById('newPassword')?.value || '';
            const confirm = document.getElementById('confirmPassword')?.value || '';
            if (next !== confirm) {
                alert('New password and confirmation do not match');
                return;
            }
            try {
                const resp = await fetch(`${API_BASE_URL}?action=password`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ current_password: current, new_password: next })
                });
                const data = await resp.json();
                if (resp.ok && data.status === 'success') {
                    alert('Password updated successfully');
                    document.getElementById('currentPassword').value = '';
                    document.getElementById('newPassword').value = '';
                    document.getElementById('confirmPassword').value = '';
                } else {
                    alert('Password update failed: ' + (data.message || resp.status));
                }
            } catch (err) {
                console.error(err);
                alert('Password update failed. Please try again.');
            }
        });
    }
    async function loadNavProfile() {
        try {
            const r = await fetch(API_BASE_URL, { credentials: 'same-origin' });
            if (!r.ok) return;
            const d = await r.json();
            if (d.status === 'success') {
                const u = d.data;
                const img = document.getElementById('navProfileImg');
                if (img) {
                    if (u.profile_image) {
                        img.src = buildUploadUrl(u.profile_image);
                    }
                    img.title = `${u.first_name || ''} ${u.last_name || ''}`.trim();
                    img.alt = img.title || 'profile';
                }
            }
        } catch (e) { /* noop */ }
    }
    document.addEventListener('DOMContentLoaded', async () => {
        wireImagePreview();
        wireEditFormSubmit();
        wirePasswordChange();
        loadNavProfile()
        try { 
            await Promise.all([preloadProducts(), loadUserData()]);
            // refresh counts after initial load
            try { await refreshCartCount(); } catch (e) { /* noop */ }
            try { await refreshWishlistCountAll(); } catch (e) { /* noop */ }
        } catch (e) { console.error(e); }
        // Add handler: Add Payment Method button should inform user COD-only
        try {
            const addPaymentBtn = document.getElementById('addPaymentBtn');
            if (addPaymentBtn) {
                addPaymentBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    showToast('Only Cash on Delivery (COD) is available at the moment.', 'error');
                });
            }
        } catch (e) { /* noop */ }
    });
    
    // Deliveries loader
    const deliveriesApi = 'https://log1.imarketph.com/api/deliveries.php';

    const wishlistApi = 'https://log1.imarketph.com/api/wishlist.php';
    const inventoryApi = 'https://log1.imarketph.com/api/inventory.php';
    const cartApi = 'https://log1.imarketph.com/api/cart.php';

    // product cache used to show real names/prices in wishlist and cart
    let allProducts = [];

    async function preloadProducts() {
        try {
            const r = await fetch(inventoryApi, { credentials: 'same-origin' });
            if (!r.ok) return;
            const p = await r.json();
            // inventory API in other pages returns an array; be defensive
            allProducts = Array.isArray(p) ? p : (p.data || []);
        } catch (e) { console.error('Failed to preload products', e); }
    }

    function getProductNameById(productId) {
        const product = allProducts.find(p => p.id == productId);
        return product ? product.item_name : `Product #${productId}`;
    }

    function getProductPriceById(productId) {
        const product = allProducts.find(p => p.id == productId);
        return product ? parseFloat(product.price).toFixed(2) : '0.00';
    }

    function getProductStockById(productId) {
        const product = allProducts.find(p => p.id == productId);
        return product ? product.stock_level : 0;
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
                return false;
            }
            await refreshCartCount();
            showToast('Product added to cart successfully!');
            try { await loadRecentActivity(); } catch (e) { /* noop */ }
            return true;
        } catch (e) {
            console.error(e);
            alert('Failed to add to cart.');
            return false;
        }
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

    async function loadCartItems() {
        const container = document.getElementById('cartItemsContainer');
        container.innerHTML = `<div class="text-center py-4 text-muted">Loading cart...</div>`;
        try {
            const r = await fetch(cartApi, { credentials: 'same-origin' });
            if (!r.ok) throw new Error('Failed');
            const d = await r.json();
            if (d.status !== 'success') throw new Error(d.message || 'Failed');
            if (!Array.isArray(d.data) || d.data.length === 0) {
                container.innerHTML = `<div class="text-center py-4 text-muted">Your cart is empty.</div>`;
                return;
            }

            let html = `<table class="table align-middle">
                <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th></th></tr></thead><tbody>`;

            let cartTotal = 0;

            d.data.forEach(item => {
                let productName = getProductNameById(item.product_id);
                if (!productName || productName.startsWith('Product #')) productName = 'Product removed';
                const productPrice = parseFloat(getProductPriceById(item.product_id));
                const maxStock = getProductStockById(item.product_id);
                const qty = Number(item.quantity || 1);
                const itemTotal = (productPrice * qty).toFixed(2);
                cartTotal += parseFloat(itemTotal);

                html += `
                    <tr>
                        <td>${productName}</td>
                        <td>₱${productPrice.toFixed(2)}</td>
                        <td>
                            <div class="quantity-controls">
                                <button class="btn btn-sm btn-outline-secondary" onclick="changeQuantity(${item.product_id}, ${qty - 1}, ${maxStock})">-</button>
                                <span class="quantity-value" id="quantity-${item.product_id}">${qty}</span>
                                <button class="btn btn-sm btn-outline-secondary" onclick="changeQuantity(${item.product_id}, ${qty + 1}, ${maxStock})">+</button>
                            </div>
                            <input type="range" class="form-range quantity-slider" id="slider-${item.product_id}"
                                min="1" max="${maxStock}" value="${qty}"
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
            console.error(e);
            container.innerHTML = `<div class="text-center text-danger">Failed to load cart.</div>`;
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
                return false;
            }
            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    }

    async function changeQuantity(productId, newQuantity, maxStock) {
        newQuantity = parseInt(newQuantity);
        if (isNaN(newQuantity) || newQuantity < 1) newQuantity = 1;
        if (newQuantity > maxStock) newQuantity = maxStock;

        const qtyEl = document.getElementById(`quantity-${productId}`);
        const slider = document.getElementById(`slider-${productId}`);
        const totalEl = document.getElementById(`total-${productId}`);

        if (qtyEl) qtyEl.textContent = newQuantity;
        if (slider) slider.value = newQuantity;

        // update total cell immediately
        const price = parseFloat(getProductPriceById(productId)) || 0;
        if (totalEl) totalEl.textContent = `₱${(price * newQuantity).toFixed(2)}`;

        updateCartTotal();

        const success = await updateCartQuantity(productId, newQuantity);
        if (!success) {
            // revert by reloading
            await loadCartItems();
        } else {
            await refreshCartCount();
        }
    }

    function updateQuantityFromSlider(productId, newQuantity, maxStock) {
        newQuantity = parseInt(newQuantity);
        const qtyEl = document.getElementById(`quantity-${productId}`);
        if (qtyEl) qtyEl.textContent = newQuantity;
        const price = parseFloat(getProductPriceById(productId)) || 0;
        const totalEl = document.getElementById(`total-${productId}`);
        if (totalEl) totalEl.textContent = `₱${(price * newQuantity).toFixed(2)}`;

        updateCartTotal();

        clearTimeout(window.sliderTimeout);
        window.sliderTimeout = setTimeout(async () => {
            const success = await updateCartQuantity(productId, newQuantity);
            if (!success) await loadCartItems();
            else await refreshCartCount();
        }, 400);
    }

    function updateCartTotal() {
        let cartTotal = 0;
        const rows = document.querySelectorAll('#cartItemsContainer tbody tr');
        rows.forEach(row => {
            if (!row.classList.contains('fw-bold')) {
                const totalCell = row.querySelector('td:nth-child(4)');
                if (totalCell) {
                    const totalText = totalCell.textContent.replace('₱', '').trim();
                    const n = parseFloat(totalText) || 0;
                    cartTotal += n;
                }
            }
        });
        const el = document.getElementById('cart-total');
        if (el) el.textContent = `₱${cartTotal.toFixed(2)}`;
    }

    async function removeFromCart(productId) {
        if (!confirm('Remove this item from cart?')) return;
        try {
            await fetch(cartApi + '?product_id=' + Number(productId), { method: 'DELETE', credentials: 'same-origin' });
        } catch (e) { /* noop */ }
        await refreshCartCount();
        await loadCartItems();
        showToast('Product removed from cart!');
    }

    async function loadDeliveries() {
        const container = document.getElementById('ordersContainer');
        container.innerHTML = '<div class="text-center py-4 text-muted">Loading deliveries...</div>';
        try {
            // If this profile page is being viewed for a specific user, include user_id so vendor sees only that user's deliveries
            const profileUserIdEl = document.querySelector('[data-profile-user-id]');
            let url = deliveriesApi;
            if (profileUserIdEl) {
                const uid = profileUserIdEl.getAttribute('data-profile-user-id');
                if (uid) {
                    url += '?user_id=' + encodeURIComponent(uid);
                }
            }
            const r = await fetch(url, { credentials: 'same-origin' });
            if (!r.ok) throw new Error('Failed');
            const items = await r.json();
            if (!Array.isArray(items) || items.length === 0) {
                container.innerHTML = '<div class="text-center text-muted">No deliveries found.</div>';
                return;
            }
            let html = '';
            items.forEach(it => {
                const receivedBtn = (it.status && it.status.toLowerCase() === 'complete') ?
                    `<span class="badge bg-success">Received</span>` :
                    `<div class="btn-group">
                        <button class="btn btn-sm btn-primary" onclick="markDeliveryReceived(${it.id});">Order received</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="cancelDelivery(${it.id});">Cancel</button>
                    </div>`;

                const invoiceBtn = (it.status && it.status.toLowerCase() === 'complete' && it.shipment_id) ?
                    `<button class="btn btn-sm btn-info ms-2" onclick="viewInvoice(${it.shipment_id});">View Invoice</button>` : '';

                html += `<div class="card mb-2">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold">${it.delivery_number || 'Delivery #' + it.id} <small class="text-muted">(Shipment ${it.shipment_id || '-'})</small></div>
                            <div class="small text-muted">${it.origin || ''} → ${it.destination || ''}</div>
                            <div class="small text-muted">Items: ${it.items_quantity || 0}</div>
                            <div class="small text-muted">${it.delivery_date ? new Date(it.delivery_date).toLocaleString() : ''}</div>
                        </div>
                        <div class="text-end">
                            <div class="mb-2"><strong>Status:</strong> ${it.status || 'Pending'}</div>
                            ${receivedBtn}
                            ${invoiceBtn}
                        </div>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        } catch (e) {
            console.error(e);
            container.innerHTML = '<div class="text-center text-danger">Failed to load deliveries.</div>';
        }
    }

    // Simple toast helper (creates container if missing)
    function showToast(message, type = 'success') {
        try {
            let container = document.getElementById('toastContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toastContainer';
                container.style.position = 'fixed';
                container.style.right = '20px';
                container.style.bottom = '20px';
                container.style.zIndex = '1080';
                document.body.appendChild(container);
            }

            const toastEl = document.createElement('div');
            toastEl.className = 'toast align-items-center text-bg-' + (type === 'error' ? 'danger' : 'success') + ' border-0';
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            toastEl.style.minWidth = '220px';

            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>`;

            container.appendChild(toastEl);
            const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
            bsToast.show();
            // remove after hidden
            toastEl.addEventListener('hidden.bs.toast', () => { toastEl.remove(); });
        } catch (e) { console.warn('Toast failed', e); }
    }

        async function cancelDelivery(id) {
            if (!confirm('Are you sure you want to cancel this delivery?')) return;
            try {
                const r = await fetch(deliveriesApi + '?id=' + encodeURIComponent(id), { method: 'DELETE', credentials: 'same-origin' });
                if (!r.ok) throw new Error('Failed to cancel');
                await loadDeliveries();
                try { await loadRecentActivity(); } catch (e) { /* noop */ }
                showToast('Delivery cancelled');
            } catch (e) {
                console.error(e);
                alert('Failed to cancel delivery: ' + (e.message || e));
            }
        }

    async function markDeliveryReceived(id) {
        if (!confirm('Confirm that you have received this order?')) return;
        try {
            const payload = { status: 'Complete' };
            const r = await fetch(deliveriesApi + '?id=' + encodeURIComponent(id), {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            const d = await r.json();
            if (!r.ok) throw new Error(d.error || d.message || 'Failed to update');
            // refresh list
            await loadDeliveries();
            showToast('Order marked as received. Thank you!');
        } catch (e) {
            console.error(e);
            alert('Failed to update order status: ' + (e.message || e));
        }
    }

    // Wishlist loaders
    async function refreshWishlistCountAll() {
        try {
            const r = await fetch(wishlistApi + '?count=1', { credentials: 'same-origin' });
            if (!r.ok) return;
            const d = await r.json();
            if (d.status === 'success') {
                const count = d.data.total ?? 0;
                const el = document.getElementById('wishlistCount');
                if (el) el.textContent = count;
                const s = document.getElementById('settingsWishlistCount');
                if (s) s.textContent = count;
            }
        } catch (e) { /* noop */ }
    }

    async function loadWishlistModal() {
        const container = document.getElementById('wishlistItemsContainer');
        container.innerHTML = '<div class="text-center py-4 text-muted">Loading wishlist...</div>';
        try {
            const r = await fetch(wishlistApi, { credentials: 'same-origin' });
            if (!r.ok) throw new Error('Failed');
            const d = await r.json();
            if (d.status !== 'success') throw new Error(d.message || 'Failed');
            const items = d.data || [];
            if (!items.length) {
                container.innerHTML = '<div class="text-center text-muted">Your wishlist is empty.</div>';
                return;
            }
            // Map product ids to names/prices using allProducts
            let html = '<ul class="list-group">';
            items.forEach(it => {
                let name = (typeof getProductNameById === 'function') ? getProductNameById(it.product_id) : ('Product #' + it.product_id);
                if (!name || name.startsWith('Product #')) name = 'Product removed';
                const price = (typeof getProductPriceById === 'function') ? getProductPriceById(it.product_id) : '0.00';
                const addToCartAction = (typeof addToCart === 'function') ? `addToCart(${it.product_id},1);` : `window.location.href='product.php?id=${it.product_id}';`;
                html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">${name}</div>
                        <div class="text-muted small">₱${price}</div>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-primary me-2" onclick="${addToCartAction} removeFromWishlist(${it.product_id});">Add to Cart</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeFromWishlist(${it.product_id});">Remove</button>
                    </div>
                </li>`;
            });
            html += '</ul>';
            container.innerHTML = html;
        } catch (e) {
            console.error(e);
            container.innerHTML = '<div class="text-center text-danger">Failed to load wishlist.</div>';
        }
    }

    async function loadProfileWishlist() {
        const container = document.getElementById('profileWishlistContainer');
        container.innerHTML = '<div class="text-center py-4 text-muted">Loading wishlist...</div>';
        try {
            const r = await fetch(wishlistApi, { credentials: 'same-origin' });
            if (!r.ok) throw new Error('Failed');
            const d = await r.json();
            if (d.status !== 'success') throw new Error(d.message || 'Failed');
            const items = d.data || [];
            if (!items.length) {
                container.innerHTML = '<div class="text-center text-muted">Your wishlist is empty.</div>';
                return;
            }
            let html = '<div class="list-group">';
            items.forEach(it => {
                let name = (typeof getProductNameById === 'function') ? getProductNameById(it.product_id) : ('Product #' + it.product_id);
                if (!name || name.startsWith('Product #')) name = 'Product removed';
                const price = (typeof getProductPriceById === 'function') ? getProductPriceById(it.product_id) : '0.00';
                const addToCartAction2 = (typeof addToCart === 'function') ? `addToCart(${it.product_id},1);` : `window.location.href='product.php?id=${it.product_id}';`;
                html += `<div class="list-group-item d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-bold">${name}</div>
                        <div class="small text-muted">₱${price}</div>
                    </div>
                    <div class="text-end">
                        <button class="btn btn-sm btn-primary mb-1" onclick="${addToCartAction2} removeFromWishlist(${it.product_id});">Add to Cart</button>
                        <br>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeFromWishlist(${it.product_id});">Remove</button>
                    </div>
                </div>`;
            });
            html += '</div>';
            container.innerHTML = html;
        } catch (e) {
            console.error(e);
            container.innerHTML = '<div class="text-center text-danger">Failed to load wishlist.</div>';
        }
    }

    async function removeFromWishlist(productId) {
        try {
            const r = await fetch(wishlistApi + '?product_id=' + Number(productId), { method: 'DELETE', credentials: 'same-origin' });
            // Refresh UI regardless of success to reflect current server state
            await refreshWishlistCountAll();
            await loadWishlistModal();
            await loadProfileWishlist();
            try { await loadRecentActivity(); } catch (e) { /* noop */ }
            if (typeof refreshCartCount === 'function') await refreshCartCount();
            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    }

    // Activate orders tab if ?tab=orders
    window.addEventListener('DOMContentLoaded', () => {
        // initial wishlist counts and profile wishlist
        refreshWishlistCountAll();
        loadProfileWishlist();

        const params = new URLSearchParams(window.location.search);
        if (params.get('tab') === 'orders') {
            const ordersTabBtn = document.getElementById('v-pills-orders-tab');
            if (ordersTabBtn) {
                const tab = new bootstrap.Tab(ordersTabBtn);
                tab.show();
            }
        }
        // load when orders tab is shown
        const ordersTabEl = document.getElementById('v-pills-orders-tab');
        if (ordersTabEl) {
            ordersTabEl.addEventListener('shown.bs.tab', () => {
                loadDeliveries();
            });
        }
        const wishlistModalEl = document.getElementById('wishlistModal');
        if (wishlistModalEl) {
            wishlistModalEl.addEventListener('show.bs.modal', () => {
                loadWishlistModal();
            });
        }
        const cartModalEl = document.getElementById('cartModal');
        if (cartModalEl) {
            cartModalEl.addEventListener('show.bs.modal', () => {
                loadCartItems();
            });
        }
        const wishlistTabBtn = document.getElementById('v-pills-wishlist-tab');
        if (wishlistTabBtn) {
            wishlistTabBtn.addEventListener('shown.bs.tab', () => {
                loadProfileWishlist();
            });
        }
    // Create shipment from cart - origin is Warehouse A, destination is user's address
    window.createShipmentFromCart = async function() {
            try {
                const r = await fetch(cartApi, { credentials: 'same-origin' });
                const d = await r.json();
                if (d.status !== 'success' || !Array.isArray(d.data) || d.data.length === 0) {
                    alert('Your cart is empty');
                    return;
                }

                const pr = await fetch(API_BASE_URL, { credentials: 'same-origin' });
                const pu = await pr.json();
                if (pu.status !== 'success') {
                    alert('Unable to determine shipping address. Please login and set your address.');
                    return;
                }

                const itemsQuantity = d.data.reduce((s, it) => s + (Number(it.quantity) || 0), 0);
                const destination = pu.data.address || `${pu.data.first_name || ''} ${pu.data.last_name || ''}`.trim();
                const shipmentNumber = 'SHP-' + Date.now();

                // read selected payment method
                const paymentMethodEl = document.getElementById('paymentMethodSelect');
                const paymentMethod = paymentMethodEl ? paymentMethodEl.value : 'cod';

                const sr = await fetch('https://log1.imarketph.com/api/shipments.php', {
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
                const dr = await fetch('https://log1.imarketph.com/api/deliveries.php', {
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
                // refresh UI counts
                try { await refreshCartCount(); } catch (e) { /* noop */ }

                alert('Shipment and delivery created successfully');
                // refresh recent activity and then navigate to orders tab
                try { await loadRecentActivity(); } catch (e) { /* noop */ }
                window.location.href = 'profile.php?tab=orders';
            } catch (e) {
                console.error(e);
                alert('Failed to create shipment: ' + (e.message || e));
            }
        }

    async function loadRecentActivity(){
        try{
            const recentContainer = document.querySelector('.profile-card .card-body');
            // Build separate lists: deliveries + wishlist
            // If viewing a specific profile user, request only their deliveries
            const profileUserIdEl = document.querySelector('[data-profile-user-id]');
            let deliveriesUrl = deliveriesApi;
            if (profileUserIdEl) {
                const puid = profileUserIdEl.getAttribute('data-profile-user-id');
                if (puid) deliveriesUrl += '?user_id=' + encodeURIComponent(puid);
            }
            const [delR, wishR] = await Promise.all([
                fetch(deliveriesUrl, { credentials: 'same-origin' }),
                fetch(wishlistApi, { credentials: 'same-origin' })
            ]);
            const deliveries = delR.ok ? await delR.json() : [];
            const wishlist = wishR.ok ? await wishR.json() : { data: [] };

            // find the Recent Activity card body specifically by heading
            const recentCardBodies = Array.from(document.querySelectorAll('.profile-card'));
            let targetBody = null;
            for (const card of recentCardBodies){
                const h = card.querySelector('.card-header h5');
                if (h && h.textContent.trim() === 'Recent Activity') { targetBody = card.querySelector('.card-body'); break; }
            }
            if (!targetBody) return;

            // Compose HTML
            let html = '';
            // recent deliveries
            if (Array.isArray(deliveries) && deliveries.length){
                deliveries.slice(0,5).forEach(it => {
                    html += `<div class="activity-item">
                        <div class="activity-badge bg-success"><i class="bi bi-truck text-white"></i></div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between"><span>Delivery ${it.delivery_number || ('#'+it.id)} - ${it.status || 'Pending'}</span><small class="text-muted">${new Date(it.created_at).toLocaleString()}</small></div>
                            <div class="small text-muted">${(it.origin||'')} → ${(it.destination||'')} • Items: ${it.items_quantity||0}</div>
                        </div>
                    </div>`;
                });
            }
            // recent wishlist
            if (wishlist && Array.isArray(wishlist.data) && wishlist.data.length){
                wishlist.data.slice(0,5).forEach(it => {
                    const name = getProductNameById(it.product_id);
                    html += `<div class="activity-item">
                        <div class="activity-badge bg-warning"><i class="bi bi-heart text-white"></i></div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between"><span>Added to wishlist: ${name}</span><small class="text-muted">${new Date(it.created_at).toLocaleString()}</small></div>
                        </div>
                    </div>`;
                });
            }
            if (!html) html = '<div class="text-center text-muted">No recent activity.</div>';
            targetBody.innerHTML = html;
        }catch(e){ console.error('Failed to load recent activity', e); }
    }

    // call loadRecentActivity when profile loads
    try { loadRecentActivity(); } catch (e) { /* noop */ }
        // Ensure the checkout button triggers the global function (fallback for inline onclick issues)
        try {
            const checkoutBtnEl = document.getElementById('checkoutBtn');
            if (checkoutBtnEl) {
                // avoid duplicate handlers
                checkoutBtnEl.removeEventListener && checkoutBtnEl.removeEventListener('click', window.createShipmentFromCart);
                checkoutBtnEl.addEventListener('click', function (ev) {
                    ev.preventDefault();
                    window.createShipmentFromCart();
                });
            }
        } catch (ee) { /* noop */ }

        // View invoice function
        window.viewInvoice = async function(shipmentId) {
            try {
                const r = await fetch('https://log1.imarketph.com/api/invoices.php?shipment_id=' + encodeURIComponent(shipmentId), { credentials: 'same-origin' });
                if (!r.ok) throw new Error('Invoice not found');
                const invoices = await r.json();
                if (!Array.isArray(invoices) || invoices.length === 0) throw new Error('No invoice found for this shipment');
                const inv = invoices[0]; // Take the first invoice for this shipment
                const html = buildInvoiceHtml(inv);
                showInvoiceModal(html);
            } catch (e) {
                alert('Failed to load invoice: ' + e.message);
            }
        };

        // Helper functions for invoice display
        function buildInvoiceHtml(inv) {
            const companyName = 'Logistics1 Ecommerce';
            const companyAddress = '123 Warehouse Ave, City, Country';
            const companyPhone = '+1 (555) 123-4567';
            const companyEmail = 'info@logistics1.com';
            const logoUrl = '/logistics1_ecommerce/images/logo.jpg';
            const invoiceNumber = inv.invoice_number || ('#' + (inv.id || ''));
            const date = inv.date || (inv.created_at ? inv.created_at.split(' ')[0] : '');
            const due = inv.due_date || '';
            const subtotal = Number(inv.subtotal || 0).toFixed(2);
            const notes = inv.notes || '';
            const shipment = inv.shipment_id ? `<div><strong>Shipment ID:</strong> ${inv.shipment_id}</div>` : '';

            // Decode items JSON
            let items = [];
            try {
                items = inv.items ? (typeof inv.items === 'string' ? JSON.parse(inv.items) : inv.items) : [];
            } catch (e) {
                items = [];
            }

            let itemsHtml = '';
            if (Array.isArray(items) && items.length) {
                itemsHtml = `<table style="width:100%;border-collapse:collapse;margin-top:20px;border:1px solid #ddd"><thead><tr style="background:#f9f9f9"><th style="border:1px solid #ddd;padding:8px;text-align:left">Description</th><th style="border:1px solid #ddd;padding:8px;text-align:center">Qty</th><th style="border:1px solid #ddd;padding:8px;text-align:right">Unit Price</th><th style="border:1px solid #ddd;padding:8px;text-align:right">Total</th></tr></thead><tbody>`;
                items.forEach(it=>{
                    itemsHtml += `<tr><td style="border:1px solid #ddd;padding:8px">${escapeHtml(it.name||it.description||'Item')}</td><td style="border:1px solid #ddd;padding:8px;text-align:center">${it.quantity||1}</td><td style="border:1px solid #ddd;padding:8px;text-align:right">₱${Number(it.unit_price||0).toFixed(2)}</td><td style="border:1px solid #ddd;padding:8px;text-align:right">₱${Number((it.quantity||1)*(it.unit_price||0)).toFixed(2)}</td></tr>`;
                });
                itemsHtml += `</tbody></table>`;
            } else {
                itemsHtml = `<div style="margin-top:20px;padding:10px;border:1px solid #ddd;background:#f9f9f9"><strong>Subtotal:</strong> ₱${subtotal}</div>`;
            }

            const footer = notes ? `<div style="margin-top:20px;padding:10px;border:1px solid #ddd;background:#f9f9f9"><strong>Notes:</strong><br>${escapeHtml(notes)}</div>` : '';

            const invoiceHtml = `
                <div style="font-family:Arial,Helvetica,sans-serif;max-width:800px;margin:0 auto;padding:20px;border:1px solid #ccc;background:#fff">
                    <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #333;padding-bottom:10px">
                        <div style="display:flex;align-items:center">
                            <img src="${logoUrl}" alt="Logo" style="height:60px;margin-right:15px">
                            <div>
                                <h2 style="margin:0;color:#333">${escapeHtml(companyName)}</h2>
                                <div style="color:#666;font-size:14px">${escapeHtml(companyAddress)}</div>
                                <div style="color:#666;font-size:14px">${escapeHtml(companyPhone)} | ${escapeHtml(companyEmail)}</div>
                            </div>
                        </div>
                        <div style="text-align:right">
                            <h1 style="margin:0;color:#333;font-size:28px">INVOICE</h1>
                            <div style="font-size:16px;font-weight:bold">${escapeHtml(invoiceNumber)}</div>
                            <div style="color:#666">Date: ${escapeHtml(date)}</div>
                            <div style="color:#666">Due Date: ${escapeHtml(due)}</div>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:space-between;margin-top:20px">
                        <div style="flex:1">
                            <h3 style="margin:0 0 10px 0;color:#333">Bill To:</h3>
                            <div>${escapeHtml(inv.user_name || inv.user || 'Customer')}</div>
                            <div>${escapeHtml(inv.user_address || '')}</div>
                        </div>
                        <div style="flex:1;text-align:right">
                            <h3 style="margin:0 0 10px 0;color:#333">Invoice Details:</h3>
                            <div><strong>Delivery Route:</strong> ${escapeHtml(inv.delivery_from || '')} → ${escapeHtml(inv.delivery_to || '')}</div>
                            ${shipment}
                        </div>
                    </div>

                    ${itemsHtml}

                    <div style="display:flex;justify-content:flex-end;margin-top:20px">
                        <div style="text-align:right;min-width:200px">
                            <div style="padding:5px 0;border-bottom:1px solid #ddd"><span style="font-weight:bold">Subtotal:</span> ₱${subtotal}</div>
                            <div style="padding:5px 0;border-bottom:1px solid #ddd"><span style="font-weight:bold">Tax (0%):</span> ₱0.00</div>
                            <div style="padding:10px 0;font-size:18px;font-weight:bold;border-top:2px solid #333">Total: ₱${subtotal}</div>
                        </div>
                    </div>

                    ${footer}

                    <div style="margin-top:30px;text-align:center;color:#666;font-size:12px;border-top:1px solid #ddd;padding-top:10px">
                        Thank you for your business! Payment is due within 30 days. Please include the invoice number on your payment.
                    </div>

                    <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end">
                        <button id="printInvoiceBtn" style="padding:8px 16px;background:#007bff;color:#fff;border:none;border-radius:4px;cursor:pointer">Print Invoice</button>
                    </div>
                </div>
            `;
            setTimeout(()=>{
                const btn = document.getElementById('printInvoiceBtn');
                if (btn) btn.addEventListener('click', ()=>printInvoiceWindow(invoiceHtml));
            }, 40);
            return invoiceHtml;
        }

        function escapeHtml(str) {
            if (!str && str !== 0) return '';
            return String(str).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#039;"})[m]; });
        }

        function printInvoiceWindow(contentHtml) {
            const win = window.open('', '_blank', 'width=900,height=700');
            if (!win) { alert('Popup blocked - allow popups to print invoice'); return; }
            const doc = win.document.open();
            const full = `<!doctype html><html><head><meta charset="utf-8"><title>Invoice</title><style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;color:#222} table{width:100%;border-collapse:collapse}</style></head><body>${contentHtml}<script>window.onload=function(){setTimeout(()=>{window.print();},200);}<\/script></body></html>`;
            doc.write(full);
            doc.close();
        }

        function showInvoiceModal(html) {
            let modal = document.getElementById('invoiceModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'invoiceModal';
                modal.className = 'modal fade';
                modal.innerHTML = `
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Invoice</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="invoiceModalBody"></div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            document.getElementById('invoiceModalBody').innerHTML = html;
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    });
</script>
</body>
</html>