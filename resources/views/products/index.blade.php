@extends('layouts.app')

@section('content')
    <style>
        .products-page {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 2rem 4rem;
        }

        /* ========== BACK BUTTON ========== */
        .page-nav {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            color: #6B6A65;
            border: 1px solid #E3DCD0;
            padding: 0.55rem 1.1rem;
            border-radius: 2rem;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .btn-back:hover {
            background: #F0EADC;
            border-color: #576238;
            color: #576238;
            transform: translateX(-2px);
        }

        .btn-back i {
            transition: transform 0.2s ease;
        }

        .btn-back:hover i {
            transform: translateX(-3px);
        }

        /* ========== HEADER ========== */
        .products-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .products-header .eyebrow {
            display: inline-block;
            color: #D4A054;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }

        .products-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.75rem;
        }

        .products-header p {
            color: #6B6A65;
            max-width: 600px;
            margin: 0 auto;
            font-size: 0.95rem;
        }

        /* ========== FILTER BAR ========== */
        .filter-bar {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            background: #F0EADC;
            border: 1px solid #E3DCD0;
            color: #6B6A65;
            padding: 0.5rem 1.25rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.25s ease;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            text-decoration: none;
        }

        .filter-btn:hover {
            background: #E3DCD0;
            border-color: #576238;
            color: #576238;
        }

        .filter-btn.active {
            background: #576238;
            color: white;
            border-color: #576238;
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.25);
        }

        .filter-btn i {
            font-size: 0.7rem;
        }

        /* ========== PRODUCTS GRID ========== */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }

        @media (max-width: 1024px) {
            .products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.25rem;
            }
        }

        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ========== PRODUCT CARD ========== */
        .product-card {
            background: #F0EADC;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-8px);
            border-color: #576238;
            box-shadow: 0 20px 40px rgba(87, 98, 56, 0.15);
        }

        .product-image {
            height: 220px;
            background: #F5F1E8;
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
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.08);
        }

        .image-category {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: rgba(255, 255, 255, 0.95);
            color: #576238;
            padding: 0.3rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            backdrop-filter: blur(4px);
            z-index: 2;
        }

        .quick-view-btn {
            position: absolute;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: rgba(87, 98, 56, 0.95);
            color: white;
            padding: 0.6rem 1.25rem;
            border-radius: 2rem;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            opacity: 0;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
            z-index: 2;
            white-space: nowrap;
        }

        .product-card:hover .quick-view-btn {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .quick-view-btn:hover {
            background: #3E4A28;
        }

        .product-info {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .product-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-bottom: 0.75rem;
            min-height: 22px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.6rem;
            border-radius: 2rem;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            line-height: 1;
        }

        .badge i {
            font-size: 0.55rem;
        }

        .badge-best {
            background: #D4A054;
            color: white;
        }

        .badge-stock {
            background: #E8F0E3;
            color: #576238;
        }

        .badge-out {
            background: #FEF0ED;
            color: #C5705A;
        }

        .product-info h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.05rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.5rem;
            line-height: 1.3;
            min-height: 2.6rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .stars {
            color: #FFD700;
            font-size: 0.7rem;
            letter-spacing: 1px;
        }

        .rating-text {
            font-size: 0.65rem;
            color: #9E9D97;
            font-weight: 500;
        }

        .product-price-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            margin-top: auto;
        }

        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #576238;
            font-family: 'Inter', sans-serif;
        }

        .stock-warning {
            font-size: 0.65rem;
            color: #D4A054;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: #FEF5E8;
            padding: 0.2rem 0.5rem;
            border-radius: 2rem;
        }

        .btn-view-details {
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

        .btn-view-details:hover {
            background: #576238;
            color: white;
            transform: translateX(3px);
        }

        .btn-view-details i {
            transition: transform 0.2s ease;
        }

        .btn-view-details:hover i {
            transform: translateX(3px);
        }

        /* ========== EMPTY STATE ========== */
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

        /* ========== PAGINATION (CUSTOM) ========== */
        .pagination-wrapper {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #E3DCD0;
        }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .pagination-info {
            font-size: 0.85rem;
            color: #6B6A65;
            font-weight: 500;
        }

        .pagination-info strong {
            color: #2C2B26;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: wrap;
        }

        .page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            padding: 0 0.65rem;
            border-radius: 0.5rem;
            background: white;
            border: 1px solid #E3DCD0;
            color: #6B6A65;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            font-family: inherit;
            cursor: pointer;
        }

        .page-btn:hover:not(.disabled):not(.active) {
            background: #F0EADC;
            border-color: #576238;
            color: #576238;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(87, 98, 56, 0.1);
        }

        .page-btn.active {
            background: #576238;
            color: white;
            border-color: #576238;
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.3);
            cursor: default;
        }

        .page-btn.disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background: #F5F1E8;
        }

        .page-btn.dots {
            border: none;
            background: transparent;
            cursor: default;
            color: #9E9D97;
        }

        .page-btn.dots:hover {
            background: transparent;
            border: none;
            color: #9E9D97;
            transform: none;
            box-shadow: none;
        }

        .page-btn i {
            font-size: 0.75rem;
        }

        @media (max-width: 640px) {
            .pagination-container {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .page-btn {
                min-width: 34px;
                height: 34px;
                font-size: 0.8rem;
            }
        }
    </style>

    <div class="products-page">

        {{-- BACK BUTTON --}}
        <div class="page-nav">
            <a href="{{ route('landing') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>

        {{-- HEADER --}}
        <div class="products-header">
            <span class="eyebrow">Our Selection</span>
            <h1>All Our Products</h1>
            <p>Freshly baked goods made with love and quality ingredients — browse by category</p>
        </div>

        {{-- FILTER BAR --}}
        @if($categories->count() > 0)
            <div class="filter-bar">
                <a href="{{ route('products.index') }}" class="filter-btn {{ !request('category') ? 'active' : '' }}">
                    <i class="fas fa-th"></i> All Products
                </a>
                @foreach($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category]) }}"
                        class="filter-btn {{ request('category') === $category ? 'active' : '' }}">
                        {{ $category }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- PRODUCTS GRID --}}
        <div class="products-grid">
            @if($products->count() > 0)
                @foreach($products as $product)
                    <div class="product-card">
                        <div class="product-image">
                            @if($product->image && file_exists(storage_path('app/public/' . $product->image)))
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                            @else
                                <img src="{{ asset('images/liza-logo.png') }}" alt="{{ $product->name }}"
                                    style="object-fit: contain; padding: 2rem;"
                                    onerror="this.src='{{ asset('assets/images/liza-logo.jpg') }}'">
                            @endif

                            @if($product->category)
                                <span class="image-category">{{ $product->category }}</span>
                            @endif

                            <a href="{{ route('products.show', $product->id) }}" class="quick-view-btn">
                                <i class="fas fa-eye"></i> Quick View
                            </a>
                        </div>

                        <div class="product-info">
                            <div class="product-badges">
                                @if($product->is_best_seller)
                                    <span class="badge badge-best">
                                        <i class="fas fa-fire"></i> Best Seller
                                    </span>
                                @endif
                                @if($product->is_available && $product->stock > 0)
                                    <span class="badge badge-stock">
                                        <i class="fas fa-check"></i> In Stock
                                    </span>
                                @else
                                    <span class="badge badge-out">
                                        <i class="fas fa-times"></i> Out of Stock
                                    </span>
                                @endif
                            </div>

                            <h3>{{ $product->name }}</h3>

                            <div class="product-rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="rating-text">4.5 (12 reviews)</span>
                            </div>

                            <div class="product-price-row">
                                <span class="product-price">₱{{ number_format($product->price, 2) }}</span>
                                @if($product->stock > 0 && $product->stock < 10)
                                    <span class="stock-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Only {{ $product->stock }} left
                                    </span>
                                @endif
                            </div>

                            <a href="{{ route('products.show', $product->id) }}" class="btn-view-details">
                                View Details <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state-full">
                    <i class="fas fa-cookie-bite"></i>
                    <p>No products available</p>
                    <small>Please check back later for our fresh baked goods.</small>
                </div>
            @endif
        </div>

        {{-- ========== CUSTOM PAGINATION ========== --}}
        @if($products->hasPages())
            <div class="pagination-wrapper">
                <div class="pagination-container">
                    {{-- INFO --}}
                    <div class="pagination-info">
                        Showing <strong>{{ $products->firstItem() }}</strong>–<strong>{{ $products->lastItem() }}</strong>
                        of <strong>{{ $products->total() }}</strong> products
                    </div>

                    {{-- CONTROLS --}}
                    <div class="pagination-controls">
                        {{-- PREVIOUS --}}
                        @if($products->onFirstPage())
                            <span class="page-btn disabled" title="Previous">
                                <i class="fas fa-chevron-left"></i>
                            </span>
                        @else
                            <a href="{{ $products->previousPageUrl() }}" class="page-btn" title="Previous">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        @endif

                        {{-- PAGE NUMBERS --}}
                        @php
                            $currentPage = $products->currentPage();
                            $lastPage = $products->lastPage();
                            $start = max(1, $currentPage - 2);
                            $end = min($lastPage, $currentPage + 2);
                        @endphp

                        {{-- FIRST PAGE --}}
                        @if($start > 1)
                            <a href="{{ $products->url(1) }}" class="page-btn">1</a>
                            @if($start > 2)
                                <span class="page-btn dots">...</span>
                            @endif
                        @endif

                        {{-- MIDDLE PAGES --}}
                        @for($i = $start; $i <= $end; $i++)
                            @if($i == $currentPage)
                                <span class="page-btn active">{{ $i }}</span>
                            @else
                                <a href="{{ $products->url($i) }}" class="page-btn">{{ $i }}</a>
                            @endif
                        @endfor

                        {{-- LAST PAGE --}}
                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="page-btn dots">...</span>
                            @endif
                            <a href="{{ $products->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
                        @endif

                        {{-- NEXT --}}
                        @if($products->hasMorePages())
                            <a href="{{ $products->nextPageUrl() }}" class="page-btn" title="Next">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="page-btn disabled" title="Next">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection