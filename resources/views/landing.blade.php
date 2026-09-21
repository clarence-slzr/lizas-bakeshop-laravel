<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liza's Bakeshop | Freshly Baked Since 1989 | San Miguel, Bulacan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #F0EADC;
            color: #2C2B26;
            line-height: 1.5;
        }

        h1,
        h2,
        h3,
        .logo-text {
            font-family: 'Playfair Display', serif;
        }

        /* ========== SCROLL PROGRESS BAR ========== */
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #576238, #D4A054);
            width: 0%;
            z-index: 9999;
            transition: width 0.1s ease;
        }

        /* ========== NAVIGATION ========== */
        .navbar {
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #E3DCD0;
            background: #F0EADC;
            position: sticky;
            top: 0;
            z-index: 100;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
        }

        .logo h1 {
            font-size: 1.35rem;
            font-weight: 600;
            color: #2C2B26;
        }

        .logo h1 span {
            color: #576238;
            font-weight: 700;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #6B6A65;
            font-weight: 500;
            font-size: 0.9rem;
            transition: color 0.2s ease;
            position: relative;
        }

        .nav-links a:not(.btn-login-nav)::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: #576238;
            transition: width 0.3s ease;
        }

        .nav-links a:not(.btn-login-nav):hover::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: #576238;
        }

        .btn-login-nav {
            background: #576238;
            color: white !important;
            padding: 0.5rem 1.5rem;
            border-radius: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-login-nav:hover {
            background: #3E4A28;
            color: white !important;
        }

        /* Mobile menu toggle */
        .menu-toggle {
            display: none;
            background: transparent;
            border: none;
            font-size: 1.5rem;
            color: #2C2B26;
            cursor: pointer;
        }

        /* ========== HERO SECTION ========== */
        .hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 4rem;
            padding: 6rem 8rem;
            width: 100%;
            background: linear-gradient(rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.75)),
                url('/assets/images/tinapay.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 600px;
        }

        .hero-content {
            flex: 1;
            max-width: 700px;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
            margin-bottom: 1.25rem;
            line-height: 1.2;
        }

        .hero-content h1 span {
            color: #D4A054;
        }

        .hero-content p {
            font-size: 1rem;
            color: white;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 2rem;
            max-width: 500px;
            line-height: 1.6;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(5px);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.85rem;
            color: white;
        }

        .hero-badge i {
            color: #FFD700;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        /* Store status indicator */
        .store-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(5px);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.8rem;
            color: white;
            width: fit-content;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .status-open {
            background: #4ADE80;
            box-shadow: 0 0 8px #4ADE80;
        }

        .status-closed {
            background: #F87171;
            box-shadow: 0 0 8px #F87171;
            animation: none;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .btn-primary {
            display: inline-block;
            background: #576238;
            color: white;
            padding: 0.85rem 2rem;
            border-radius: 2rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background: #D4A054;
            transform: translateY(-2px);
        }

        .btn-outline-white {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: transparent;
            color: white;
            padding: 0.85rem 2rem;
            border-radius: 2rem;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            border: 1px solid rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-outline-white:hover {
            background: white;
            color: #2C2B26;
            border-color: white;
        }

        .btn-outline {
            display: inline-block;
            background: transparent;
            color: #576238;
            padding: 0.85rem 2rem;
            border-radius: 2rem;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            border: 1px solid #576238;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-outline:hover {
            background: #576238;
            color: white;
        }

        /* ========== SECTION STYLES ========== */
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-size: 2.25rem;
            font-weight: 500;
            color: #2C2B26;
            margin-bottom: 0.75rem;
        }

        .section-header p {
            color: #6B6A65;
            max-width: 600px;
            margin: 0 auto;
        }

        .section-eyebrow {
            display: inline-block;
            color: #D4A054;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }

        /* ========== PRODUCTS SECTION ========== */
        .products-section {
            padding: 5rem 2rem;
            background: white;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            max-width: 1280px;
            margin: 0 auto;
        }

        .product-card {
            background: #F0EADC;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-6px);
            border-color: #576238;
            box-shadow: 0 12px 24px rgba(87, 98, 56, 0.12);
        }

        .product-image {
            height: 200px;
            background: #E8E1D4;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-info {
            padding: 1.25rem;
        }

        .product-info h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.5rem;
        }

        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #576238;
            margin-bottom: 0.75rem;
        }

        .product-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 600;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .badge-best {
            background: #D4A054;
            color: white;
        }

        .badge-stock {
            background: #576238;
            color: white;
        }

        .badge-new {
            background: #C5705A;
            color: white;
        }

        .btn-order {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: transparent;
            color: #576238;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            border: 1px solid #576238;
            margin-top: 0.75rem;
            transition: all 0.2s ease;
        }

        .btn-order:hover {
            background: #576238;
            color: white;
        }

        .view-all {
            text-align: center;
            margin-top: 3rem;
        }

        /* ========== WHY CHOOSE US ========== */
        .features-section {
            padding: 5rem 2rem;
            background: #F0EADC;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            max-width: 1280px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 2rem 1.5rem;
            text-align: center;
            transition: all 0.2s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            border-color: #576238;
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: #F0EADC;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 1.5rem;
            color: #576238;
        }

        .feature-card h3 {
            font-size: 1.05rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.5rem;
        }

        .feature-card p {
            font-size: 0.85rem;
            color: #6B6A65;
            line-height: 1.6;
        }

        /* ========== ABOUT SECTION ========== */
        .about-section {
            padding: 5rem 2rem;
            background: white;
        }

        .about-container {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            gap: 4rem;
            align-items: center;
        }

        .about-content {
            flex: 1;
        }

        .about-content h2 {
            font-size: 2rem;
            font-weight: 500;
            color: #2C2B26;
            margin-bottom: 1.25rem;
        }

        .about-content p {
            color: #6B6A65;
            margin-bottom: 1.5rem;
            line-height: 1.7;
        }

        .about-stats {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .stat-item {
            background: #F0EADC;
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid #E3DCD0;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #576238;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #6B6A65;
        }

        .about-image {
            flex: 1;
            text-align: center;
        }

        .about-image img {
            width: 100%;
            max-width: 400px;
            height: auto;
            border-radius: 24px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #E3DCD0;
        }

        /* ========== CONTACT SECTION ========== */
        .contact-section {
            padding: 5rem 2rem;
            background: #F0EADC;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 3rem;
            max-width: 1280px;
            margin: 0 auto;
        }

        .contact-info h3,
        .contact-form-container h3 {
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            color: #2C2B26;
        }

        .contact-details {
            margin-top: 2rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .contact-item i {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #576238;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .contact-item p {
            color: #6B6A65;
        }

        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid #E3DCD0;
        }

        .contact-form input,
        .contact-form textarea {
            padding: 0.85rem 1rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            background: white;
            transition: border-color 0.2s ease;
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
        }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert.success {
            background: #E8F0E3;
            color: #576238;
            border: 1px solid #576238;
        }

        .alert.error {
            background: #FCE8E6;
            color: #C5705A;
            border: 1px solid #C5705A;
        }

        .map-container iframe {
            width: 100%;
            border-radius: 0.75rem;
            border: 1px solid #E3DCD0;
        }

        /* ========== SOCIAL SECTION ========== */
        .social-section {
            padding: 5rem 2rem;
            background: white;
        }

        .fb-page-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 0.75rem;
            border: 1px solid #E3DCD0;
            overflow: hidden;
        }

        /* ========== CTA SECTION ========== */
        .cta-section {
            padding: 5rem 2rem;
            background: #576238;
            text-align: center;
            color: white;
        }

        .cta-section h2 {
            font-size: 2rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .cta-section p {
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            color: #576238;
            padding: 0.85rem 2rem;
            border-radius: 2rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-cta:hover {
            background: #F0EADC;
            transform: translateY(-2px);
        }

        /* ========== BACK TO TOP ========== */
        .back-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #576238;
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
        }

        .back-to-top.visible {
            opacity: 0.9;
            visibility: visible;
        }

        .back-to-top:hover {
            opacity: 1;
            background: #3E4A28;
            transform: translateY(-3px);
        }

        /* ========== FOOTER ========== */
        .footer {
            background: #2C2B26;
            color: #A09F9A;
            padding: 3rem 2rem 2rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            max-width: 1280px;
            margin: 0 auto;
        }

        .footer-col h4 {
            color: white;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .footer-col p,
        .footer-col a {
            font-size: 0.85rem;
            color: #A09F9A;
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
        }

        .footer-col a:hover {
            color: #D4A054;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid #3D3C38;
            font-size: 0.75rem;
        }

        /* ========== MOBILE RESPONSIVE ========== */
        @media (max-width: 1024px) {
            .hero {
                padding: 4rem 2rem;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .about-container {
                flex-direction: column;
                gap: 2rem;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 1rem 1.25rem;
            }

            .menu-toggle {
                display: block;
            }

            .nav-links {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                flex-direction: column;
                gap: 0;
                padding: 1rem;
                border-bottom: 1px solid #E3DCD0;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                display: none;
            }

            .nav-links.open {
                display: flex;
            }

            .nav-links a {
                padding: 0.75rem 1rem;
                width: 100%;
                border-radius: 0.5rem;
            }

            .nav-links a:hover {
                background: #F0EADC;
            }

            .btn-login-nav {
                text-align: center;
                justify-content: center;
                margin-top: 0.5rem;
            }

            .hero {
                padding: 3rem 1.5rem;
                min-height: auto;
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .products-grid,
            .features-grid {
                grid-template-columns: 1fr;
            }

            .section-header h2 {
                font-size: 1.75rem;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <!-- Scroll Progress Bar -->
    <div class="scroll-progress" id="scrollProgress"></div>

    <!-- ========== LANDING PAGE ========== -->
    <nav class="navbar" id="navbar">
        <div class="logo">
            <img src="/assets/images/liza-logo.jpg" alt="Liza's Bakeshop" class="logo-img">
            <h1>Liza's <span>BAKESHOP</span></h1>
        </div>
        <button class="menu-toggle" onclick="toggleMenu()">
            <i class="fas fa-bars" id="menuIcon"></i>
        </button>
        <div class="nav-links" id="navLinks">
            <a href="#home">Home</a>
            <a href="#products">Products</a>
            <a href="#features">Why Us</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
            @auth
                <a href="{{ route('dashboard') }}" class="btn-login-nav">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-login-nav">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            @endauth
        </div>
    </nav>

    <!-- ========== HERO SECTION ========== -->
    @php
        $current_hour = (int) date('G');
        $is_open = ($current_hour >= 7 && $current_hour < 19); // 7AM - 7PM
    @endphp

    <section class="hero" id="home">
        <div class="hero-content">
            <!-- Store Status -->
            <div class="store-status">
                <span class="status-dot {{ $is_open ? 'status-open' : 'status-closed' }}"></span>
                <span>{{ $is_open ? 'Open Now' : 'Closed' }} • Mon-Sun 7AM-7PM</span>
            </div>

            <div class="hero-badge">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
                <span>94% recommend (41+ reviews)</span>
            </div>
            <h1>Freshly Baked <br><span>Delights Since 1989</span></h1>
            <p>Located at Tecson St. Brgy. Poblacion, San Miguel, Bulacan. Serving quality baked goods made with love
                for over three decades.</p>
            <div class="hero-actions">
                <a href="#products" class="btn-primary">
                    <i class="fas fa-shopping-bag"></i> View Products
                </a>
                <a href="#about" class="btn-outline-white">
                    Learn More <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- ========== PRODUCTS SECTION ========== -->
    <section class="products-section" id="products">
        <div class="section-header">
            <span class="section-eyebrow">Our Selection</span>
            <h2>Best Seller Products</h2>
            <p>Freshly baked goods made with love and quality ingredients</p>
        </div>
        <div class="products-grid">
            <div class="product-card">
                <div class="product-image"><img src="/assets/images/products/regularbiscocho.jpg" alt="Biscocho Regular"
                        onerror="this.parentElement.innerHTML='<i class=\'fas fa-cookie-bite\'></i>'"></div>
                <div class="product-info">
                    <span class="product-badge badge-best">Best Seller</span>
                    <span class="product-badge badge-stock">In Stock</span>
                    <h3>Biscocho Regular</h3>
                    <div class="product-price">₱30.00</div>
                    <a href="{{ route('login') }}" class="btn-order">View Details <i
                            class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="product-card">
                <div class="product-image"><img src="/assets/images/products/premiumbiscocho.jpg" alt="Biscocho Premium"
                        onerror="this.parentElement.innerHTML='<i class=\'fas fa-crown\'></i>'"></div>
                <div class="product-info">
                    <span class="product-badge badge-best">Best Seller</span>
                    <span class="product-badge badge-stock">In Stock</span>
                    <h3>Biscocho Premium</h3>
                    <div class="product-price">₱35.00</div>
                    <a href="{{ route('login') }}" class="btn-order">View Details <i
                            class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="product-card">
                <div class="product-image"><img src="/assets/images/products/ubebrazo.jpg" alt="Ube Brazo Cake"
                        onerror="this.parentElement.innerHTML='<i class=\'fas fa-birthday-cake\'></i>'"></div>
                <div class="product-info">
                    <span class="product-badge badge-best">Best Seller</span>
                    <span class="product-badge badge-stock">In Stock</span>
                    <h3>Ube Brazo Cake</h3>
                    <div class="product-price">₱540.00</div>
                    <a href="{{ route('login') }}" class="btn-order">View Details <i
                            class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="product-card">
                <div class="product-image"><img src="/assets/images/products/nyc.jpg" alt="New York Cheesecake"
                        onerror="this.parentElement.innerHTML='<i class=\'fas fa-cheese\'></i>'"></div>
                <div class="product-info">
                    <span class="product-badge badge-new">New</span>
                    <span class="product-badge badge-stock">In Stock</span>
                    <h3>New York Cheesecake</h3>
                    <div class="product-price">₱1,500.00</div>
                    <a href="{{ route('login') }}" class="btn-order">View Details <i
                            class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
        <div class="view-all">
            <a href="{{ route('login') }}" class="btn-outline">View All Products <i
                    class="fa-solid fa-arrow-right"></i></a>
        </div>
    </section>

    <!-- ========== WHY CHOOSE US ========== -->
    <section class="features-section" id="features">
        <div class="section-header">
            <span class="section-eyebrow">Why Choose Us</span>
            <h2>What Makes Us Special</h2>
            <p>Three decades of baking tradition and quality you can taste</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-heart"></i></div>
                <h3>Made with Love</h3>
                <p>Every product is baked fresh daily using traditional family recipes.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-award"></i></div>
                <h3>35+ Years Experience</h3>
                <p>Serving San Miguel, Bulacan since 1989 with consistent quality.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-leaf"></i></div>
                <h3>Quality Ingredients</h3>
                <p>We use only the finest ingredients — no shortcuts, no compromise.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-store"></i></div>
                <h3>Fresh & Fast</h3>
                <p>Pickup or delivery — freshly baked goods right at your fingertips.</p>
            </div>
        </div>
    </section>

    <!-- ========== ABOUT SECTION ========== -->
    <section class="about-section" id="about">
        <div class="about-container">
            <div class="about-content">
                <span class="section-eyebrow">Our Story</span>
                <h2>About Liza's Bakeshop</h2>
                <p>Since 1989, Liza's Bakeshop has been serving the San Miguel, Bulacan community with freshly baked
                    goods made from quality ingredients and lots of love. We take pride in our traditional recipes
                    passed down through generations, ensuring every bite brings warmth and happiness to our customers.
                </p>
                <div class="about-stats">
                    <div class="stat-item">
                        <div class="stat-number">35+</div>
                        <div class="stat-label">Years of Excellence</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">1000+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Delicious Products</div>
                    </div>
                </div>
            </div>
            <div class="about-image">
                <img src="/assets/images/loobliza.jpg" alt="Liza's Bakeshop">
            </div>
        </div>
    </section>

    <!-- ========== CONTACT SECTION ========== -->
    <section class="contact-section" id="contact">
        <div class="section-header">
            <span class="section-eyebrow">Get in Touch</span>
            <h2>Visit or Contact Us</h2>
            <p>We'd love to hear from you</p>
        </div>
        <div class="contact-grid">
            <div class="contact-info">
                <h3>Find Us</h3>
                <div class="map-container">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3859.119290187258!2d120.97911431483139!3d15.096456489090287!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3396f839cb4266d7%3A0xb29bf8fb62c3e795!2sLiza's%20Bakeshop!5e0!3m2!1sen!2sph!4v1715680000000!5m2!1sen!2sph"
                        width="100%" height="250" style="border:0; border-radius: 12px;" allowfullscreen=""
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <div class="contact-details">
                    <div class="contact-item"><i class="fas fa-map-marker-alt"></i>
                        <p>Tecson St. Brgy. Poblacion, San Miguel, Bulacan, 3011</p>
                    </div>
                    <div class="contact-item"><i class="fas fa-phone"></i>
                        <p>0917 514 3444</p>
                    </div>
                    <div class="contact-item"><i class="fas fa-clock"></i>
                        <p>Mon-Sun: 7AM - 7PM</p>
                    </div>
                </div>
            </div>
            <div class="contact-form-container">
                <h3>Send us a Message</h3>
                @if(session('contact_success'))
                    <div class="alert success">
                        <i class="fas fa-check-circle"></i> {{ session('contact_success') }}
                    </div>
                @endif
                <form method="POST" action="{{ route('contact.store') }}" class="contact-form">
                    @csrf
                    <input type="text" name="name" placeholder="Your Name *" required>
                    <input type="email" name="email" placeholder="Your Email *" required>
                    <input type="text" name="subject" placeholder="Subject">
                    <textarea name="message" rows="4" placeholder="Your Message *" required></textarea>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- ========== SOCIAL SECTION ========== -->
    <section class="social-section">
        <div class="section-header">
            <span class="section-eyebrow">Stay Connected</span>
            <h2>Follow Us on Facebook</h2>
            <p>Stay updated with our latest products and promos</p>
        </div>
        <div class="fb-page-container">
            <div class="fb-page" data-href="https://www.facebook.com/LizasBakeshopSanMiguel/" data-tabs="timeline"
                data-width="500" data-height="400" data-small-header="true" data-adapt-container-width="true"
                data-hide-cover="false" data-show-facepile="true"></div>
        </div>
    </section>

    <!-- ========== CTA SECTION ========== -->
    <section class="cta-section">
        <h2>Ready to Order?</h2>
        <p>Freshly baked goods, ready when you are</p>
        <a href="{{ route('login') }}" class="btn-cta">
            <i class="fas fa-shopping-bag"></i> Browse Products
        </a>
    </section>

    <!-- ========== BACK TO TOP ========== -->
    <a href="#" class="back-to-top" id="backToTop" title="Back to top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- ========== FOOTER ========== -->
    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-col">
                <h4>Liza's Bakeshop</h4>
                <p>Est. 1989 | San Miguel, Bulacan</p>
                <p><i class="fas fa-map-marker-alt"></i> Tecson St. Brgy. Poblacion</p>
                <p><i class="fas fa-phone"></i> 0917 514 3444</p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <a href="#home">Home</a>
                <a href="#products">Products</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
            </div>
            <div class="footer-col">
                <h4>Business Hours</h4>
                <p>Monday - Sunday: 7AM - 7PM</p>
                <p style="margin-top: 0.75rem;">
                    <span class="status-dot {{ $is_open ? 'status-open' : 'status-closed' }}"
                        style="display: inline-block; vertical-align: middle;"></span>
                    <span style="color: {{ $is_open ? '#4ADE80' : '#F87171' }}; font-weight: 600; font-size: 0.8rem;">
                        {{ $is_open ? 'OPEN NOW' : 'CLOSED' }}
                    </span>
                </p>
            </div>
            <div class="footer-col">
                <h4>Follow Us</h4>
                <a href="https://www.facebook.com/share/18nsug1BLY/" target="_blank"><i class="fab fa-facebook"></i>
                    Facebook</a>
                <a href="https://www.instagram.com/lizasbakeshop/" target="_blank"><i class="fab fa-instagram"></i>
                    Instagram</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Liza's Bakeshop. All rights reserved. | Owned by Mahrilag G. Nagalinngam</p>
        </div>
    </footer>

    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v18.0"></script>

    <script>
        // ========== MOBILE MENU ==========
        function toggleMenu() {
            const nav = document.getElementById('navLinks');
            const icon = document.getElementById('menuIcon');
            nav.classList.toggle('open');
            icon.className = nav.classList.contains('open') ? 'fas fa-times' : 'fas fa-bars';
        }

        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('navLinks').classList.remove('open');
                document.getElementById('menuIcon').className = 'fas fa-bars';
            });
        });

        // ========== SCROLL PROGRESS BAR ==========
        window.addEventListener('scroll', () => {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            document.getElementById('scrollProgress').style.width = scrolled + '%';

            const navbar = document.getElementById('navbar');
            if (navbar) {
                if (winScroll > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }

            const backToTop = document.getElementById('backToTop');
            if (backToTop) {
                if (winScroll > 400) {
                    backToTop.classList.add('visible');
                } else {
                    backToTop.classList.remove('visible');
                }
            }
        });

        // ========== SMOOTH SCROLL ==========
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>

</html>