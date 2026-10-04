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

        /* SCROLL PROGRESS */
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

        /* NAVBAR */
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

        .menu-toggle {
            display: none;
            background: transparent;
            border: none;
            font-size: 1.5rem;
            color: #2C2B26;
            cursor: pointer;
        }

        /* HERO */
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

        /* BUTTONS */
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

        /* SECTIONS */
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

        /* BEST PRODUCTS SECTION */
        .best-products-section {
            padding: 5rem 2rem;
            background: white;
        }

        .best-products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            max-width: 1280px;
            margin: 0 auto;
        }

        .best-products-grid .product-card {
            background: #F0EADC;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .best-products-grid .product-card:hover {
            transform: translateY(-6px);
            border-color: #576238;
            box-shadow: 0 12px 24px rgba(87, 98, 56, 0.12);
        }

        .best-products-grid .product-image {
            height: 220px;
            background: #F5F1E8;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .best-products-grid .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .best-products-grid .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .best-products-grid .image-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: #D4A054;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
        }

        .best-products-grid .product-info {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .best-products-grid .product-info h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.05rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.75rem;
            line-height: 1.3;
            min-height: 2.6rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .best-products-grid .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #576238;
            font-family: 'Inter', sans-serif;
            margin-bottom: 1rem;
        }

        .best-products-grid .btn-view-details {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            background: transparent;
            color: #576238;
            padding: 0.6rem 1.2rem;
            border-radius: 2rem;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid #576238;
            transition: all 0.2s ease;
            margin-top: auto;
            align-self: flex-start;
        }

        .best-products-grid .btn-view-details:hover {
            background: #576238;
            color: white;
        }

        .best-products-grid .btn-view-details i {
            transition: transform 0.2s ease;
        }

        .best-products-grid .btn-view-details:hover i {
            transform: translateX(3px);
        }

        .empty-state-full {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 2rem;
            background: #F0EADC;
            border: 2px dashed #E3DCD0;
            border-radius: 12px;
        }

        .empty-state-full i {
            font-size: 3.5rem;
            color: #D4C9BD;
            margin-bottom: 1rem;
            display: block;
        }

        .empty-state-full p {
            color: #6B6A65;
            font-size: 1rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .empty-state-full small {
            color: #9E9D97;
            font-size: 0.85rem;
        }

        .view-all-wrapper {
            text-align: center;
            margin-top: 3rem;
        }

        .view-all-wrapper .btn-view-all {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: #576238;
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 2rem;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.2);
        }

        .view-all-wrapper .btn-view-all:hover {
            background: #3E4A28;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(87, 98, 56, 0.3);
        }

        .view-all-wrapper .btn-view-all i {
            transition: transform 0.2s ease;
        }

        .view-all-wrapper .btn-view-all:hover i {
            transform: translateX(4px);
        }

        /* FEATURES */
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

        /* TESTIMONIALS */
        .testimonials-section {
            padding: 5rem 2rem;
            background: white;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            max-width: 1280px;
            margin: 0 auto;
        }

        .testimonial-card {
            background: #F0EADC;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 2rem 1.5rem;
            transition: all 0.2s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-4px);
            border-color: #D4A054;
        }

        .testimonial-stars {
            color: #FFD700;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .testimonial-text {
            color: #2C2B26;
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 1.5rem;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .testimonial-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #576238;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .testimonial-author-info strong {
            display: block;
            font-size: 0.9rem;
            color: #2C2B26;
            font-weight: 600;
        }

        .testimonial-author-info span {
            font-size: 0.75rem;
            color: #6B6A65;
        }

        /* ABOUT */
        .about-section {
            padding: 5rem 2rem;
            background: #F0EADC;
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
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid #E3DCD0;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #576238;
            font-family: 'Inter', sans-serif;
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

        /* FAQ */
        .faq-section {
            padding: 5rem 2rem;
            background: white;
        }

        .faq-grid {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .faq-item {
            background: #F0EADC;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 1.25rem 1.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .faq-item:hover {
            border-color: #576238;
        }

        .faq-item[open] {
            border-color: #576238;
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.08);
        }

        .faq-item summary {
            font-weight: 600;
            color: #2C2B26;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            list-style: none;
            outline: none;
        }

        .faq-item summary::-webkit-details-marker {
            display: none;
        }

        .faq-item summary::after {
            content: '\f067';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 0.75rem;
            color: #576238;
            transition: transform 0.3s ease;
        }

        .faq-item[open] summary::after {
            content: '\f068';
        }

        .faq-item p {
            margin-top: 1rem;
            color: #6B6A65;
            font-size: 0.9rem;
            line-height: 1.7;
        }

        /* CONTACT */
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

        .map-container iframe {
            width: 100%;
            border-radius: 0.75rem;
            border: 1px solid #E3DCD0;
        }

        /* NEWSLETTER */
        .newsletter-section {
            padding: 5rem 2rem;
            background: white;
            text-align: center;
        }

        .newsletter-content {
            max-width: 600px;
            margin: 0 auto;
        }

        .newsletter-section h2 {
            font-size: 2rem;
            font-weight: 500;
            color: #2C2B26;
            margin-bottom: 0.75rem;
        }

        .newsletter-section p {
            color: #6B6A65;
            margin-bottom: 2rem;
        }

        .newsletter-form {
            display: flex;
            gap: 0.5rem;
            max-width: 500px;
            margin: 0 auto;
            background: #F0EADC;
            border: 1px solid #E3DCD0;
            border-radius: 2rem;
            padding: 0.35rem;
        }

        .newsletter-form input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            padding: 0.75rem 1.25rem;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            color: #2C2B26;
        }

        .newsletter-form input::placeholder {
            color: #A09F9A;
        }

        .newsletter-form button {
            background: #576238;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .newsletter-form button:hover {
            background: #D4A054;
        }

        .alert-message {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #E8F0E3;
            color: #576238;
            border: 1px solid #576238;
        }

        .alert-error {
            background: #FCE8E6;
            color: #C5705A;
            border: 1px solid #C5705A;
        }

        /* SOCIAL */
        .social-section {
            padding: 5rem 2rem;
            background: #F0EADC;
        }

        .fb-page-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 0.75rem;
            border: 1px solid #E3DCD0;
            overflow: hidden;
        }

        /* CTA */
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
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* CTA Buttons */
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
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

        .btn-cta-outline {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: transparent;
            color: white;
            padding: 0.85rem 2rem;
            border-radius: 2rem;
            text-decoration: none;
            font-weight: 600;
            border: 2px solid white;
            transition: all 0.2s ease;
        }

        .btn-cta-outline:hover {
            background: white;
            color: #576238;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        /* BACK TO TOP */
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

        /* FOOTER */
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

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .hero {
                padding: 4rem 2rem;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .best-products-grid,
            .features-grid,
            .testimonials-grid {
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

            .best-products-grid,
            .features-grid,
            .testimonials-grid {
                grid-template-columns: 1fr;
            }

            .section-header h2 {
                font-size: 1.75rem;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }

            .newsletter-form {
                flex-direction: column;
                background: transparent;
                border: none;
                padding: 0;
            }

            .newsletter-form input {
                background: #F0EADC;
                border: 1px solid #E3DCD0;
                border-radius: 2rem;
                margin-bottom: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-cta,
            .btn-cta-outline {
                width: 100%;
                justify-content: center;
                max-width: 300px;
            }
        }
    </style>
</head>

<body>

    <div class="scroll-progress" id="scrollProgress"></div>

    <!-- NAVBAR -->
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

    @php
        $current_hour = (int) date('G');
        $is_open = ($current_hour >= 7 && $current_hour < 19);
    @endphp

    <!-- HERO -->
    <section class="hero" id="home">
        <div class="hero-content">
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

    <!-- BEST PRODUCTS OF THE DAY -->
    <section class="best-products-section" id="products">
        <div class="section-header">
            <span class="section-eyebrow">Today's Best</span>
            <h2>Best Products of the Day</h2>
            <p>Our top picks — freshly baked and ready for you</p>
        </div>

        <div class="best-products-grid">
            @if(isset($bestSellers) && $bestSellers->count() > 0)
                @foreach($bestSellers as $product)
                    <div class="product-card">
                        <div class="product-image">
                            @if($product->image && file_exists(storage_path('app/public/' . $product->image)))
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                            @else
                                <img src="{{ asset('images/liza-logo.png') }}" alt="{{ $product->name }}"
                                    style="object-fit: contain; padding: 2rem;"
                                    onerror="this.src='{{ asset('assets/images/liza-logo.jpg') }}'">
                            @endif

                            @if($product->is_best_seller)
                                <span class="image-badge">Best Seller</span>
                            @endif
                        </div>

                        <div class="product-info">
                            <h3>{{ $product->name }}</h3>
                            <div class="product-price">₱{{ number_format($product->price, 2) }}</div>
                            <a href="{{ route('products.show', $product->id) }}" class="btn-view-details">
                                View Details <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state-full">
                    <i class="fas fa-cookie-bite"></i>
                    <p>No best sellers available</p>
                    <small>Please check back later for our fresh baked goods.</small>
                </div>
            @endif
        </div>

        <div class="view-all-wrapper">
            <a href="{{ route('products.index') }}" class="btn-view-all">
                View All Products <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <!-- FEATURES -->
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

    <!-- TESTIMONIALS -->
    <section class="testimonials-section">
        <div class="section-header">
            <span class="section-eyebrow">Testimonials</span>
            <h2>What Our Customers Say</h2>
            <p>Real feedback from our beloved customers on Facebook</p>
        </div>
        <div class="testimonials-grid">

          
            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"Cozy place & the staffs are accommodating & friendly Delicious pastries,
                    unique desserts, and a large menu. Highly recommended!"</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">RM</div>
                    <div class="testimonial-author-info">
                        <strong>Racquel Macatiguib</strong>
                        <span><i class="fab fa-facebook" style="color: #1877F2;"></i> Recommends on Facebook</span>
                    </div>
                </div>
            </div>

            
            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"The cake is superb. Tama lang yung tamis and the cake itself is
                    remarkable.
                    Ang ganda, walang air pockets between the layers "</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">AS</div>
                    <div class="testimonial-author-info">
                        <strong>Alysa Mae Santos</strong>
                        <span><i class="fab fa-facebook" style="color: #1877F2;"></i> Recommends on Facebook</span>
                    </div>
                </div>
            </div>

           
            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"Great cakes, great staff! Always a pleasure to visit Liza's Bakeshop.
                    Highly
                    recommended for anyone craving quality baked goods."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">ML</div>
                    <div class="testimonial-author-info">
                        <strong>Mona J Labitoria-Visperas</strong>
                        <span><i class="fab fa-facebook" style="color: #1877F2;"></i> Recommends on Facebook</span>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ABOUT -->
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

    <!-- FAQ -->
    <section class="faq-section">
        <div class="section-header">
            <span class="section-eyebrow">FAQ</span>
            <h2>Frequently Asked Questions</h2>
            <p>Common questions from our customers</p>
        </div>
        <div class="faq-grid">
            <details class="faq-item">
                <summary>Do you deliver?</summary>
                <p>Yes! We deliver within San Miguel, Bulacan. Contact us at 0917 514 3444 for delivery fees and
                    scheduling.</p>
            </details>
            <details class="faq-item">
                <summary>Do you accept bulk orders?</summary>
                <p>Absolutely! We accept bulk orders for events, parties, and corporate giveaways. Please order at least
                    2 days in advance.</p>
            </details>
            <details class="faq-item">
                <summary>How long do your products stay fresh?</summary>
                <p>Our baked goods are best consumed within 3-5 days. Store in an airtight container to maintain
                    freshness.</p>
            </details>
            <details class="faq-item">
                <summary>What are your business hours?</summary>
                <p>We are open Monday to Sunday, from 7:00 AM to 7:00 PM.</p>
            </details>
            <details class="faq-item">
                <summary>Do you accept custom cake orders?</summary>
                <p>Yes! We accept custom cake orders for birthdays, weddings, and other special occasions. Please
                    contact us for inquiries.</p>
            </details>
        </div>
    </section>

    <!-- CONTACT -->
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

    <!-- NEWSLETTER -->
    <section class="newsletter-section">
        <div class="newsletter-content">
            <span class="section-eyebrow">Stay Updated</span>
            <h2>Get Notified of New Products</h2>
            <p>Subscribe to our newsletter for fresh updates, promos, and new arrivals.</p>

            @if(session('newsletter_success'))
                <div class="alert-message alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('newsletter_success') }}
                </div>
            @endif

            @if(session('newsletter_error'))
                <div class="alert-message alert-error">
                    <i class="fas fa-exclamation-circle"></i> {{ session('newsletter_error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert-message alert-error">
                    <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="newsletter-form">
                @csrf
                <input type="email" name="email" placeholder="Enter your email address" required>
                <button type="submit">
                    <i class="fas fa-paper-plane"></i> Subscribe
                </button>
            </form>
        </div>
    </section>

    <!-- SOCIAL -->
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

    <!-- CTA -->
    <section class="cta-section">
        <h2>Craving Something Fresh?</h2>
        <p>Visit our shop or contact us — freshly baked goods, ready when you are</p>
        <div class="cta-buttons">
            <a href="{{ route('products.index') }}" class="btn-cta">
                <i class="fas fa-shopping-bag"></i> Browse Products
            </a>
            <a href="https://www.google.com/maps/search/?api=1&query=Liza's+Bakeshop+San+Miguel+Bulacan" target="_blank"
                rel="noopener noreferrer" class="btn-cta-outline">
                <i class="fas fa-map-marker-alt"></i> Visit Our Shop
            </a>
        </div>
    </section>

    <!-- BACK TO TOP -->
    <a href="#" class="back-to-top" id="backToTop" title="Back to top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- FOOTER -->
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