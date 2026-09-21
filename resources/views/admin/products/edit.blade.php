@extends('layouts.app')

@section('content')
    @php
        $page_title = 'Edit Product';
        $hide_page_title = true;

        $errors_list = $errors->all();
    @endphp

    <div class="form-container">
        <div class="form-header">
            <div class="form-header-left">
                <a href="{{ route('admin.products.index') }}" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i>Back to Products
                </a>
                <h1>Edit Product</h1>
                <p class="form-description">Update product information for {{ $product->name }}</p>
            </div>
            <div class="product-status">
                <span class="status-badge {{ $product->is_available ? 'status-active' : 'status-inactive' }}">
                    {{ $product->is_available ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>

        @if(!empty($errors_list))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <ul>
                    @foreach($errors_list as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" class="product-form">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-section">
                    <h3 class="section-title">Basic Information</h3>

                    <div class="form-group">
                        <label for="name">Product Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        <small class="form-hint">Enter the full product name as it appears to customers</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="input-icon">₱</span>
                                <input type="number" id="price" name="price" step="0.01" min="0"
                                    value="{{ old('price', $product->price) }}" required>
                            </div>
                            <small class="form-hint">Price in Philippine Peso (PHP)</small>
                        </div>

                        <div class="form-group">
                            <label for="stock">Stock Quantity</label>
                            <input type="number" id="stock" name="stock" min="0"
                                value="{{ old('stock', $product->stock) }}">
                            <small class="form-hint">Current inventory count</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="Breads" {{ old('category', $product->category) == 'Breads' ? 'selected' : '' }}>
                                Breads</option>
                            <option value="Cakes" {{ old('category', $product->category) == 'Cakes' ? 'selected' : '' }}>Cakes
                            </option>
                            <option value="Pastries" {{ old('category', $product->category) == 'Pastries' ? 'selected' : '' }}>Pastries</option>
                            <option value="Cookies" {{ old('category', $product->category) == 'Cookies' ? 'selected' : '' }}>
                                Cookies</option>
                            <option value="Others" {{ old('category', $product->category) == 'Others' ? 'selected' : '' }}>
                                Others</option>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Availability Settings</h3>

                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <strong style="white-space: nowrap;">Available for purchase</strong>
                        <label style="display: flex; align-items: center;">
                            <input type="checkbox" name="is_available" value="1" {{ $product->is_available ? 'checked' : '' }}>
                        </label>
                    </div>

                    <small style="display: block; margin-top: 4px;">When unchecked, this product will be hidden from
                        POS</small>

                    <div class="info-box" style="margin-top: 16px;">
                        <i class="fas fa-info-circle"></i>
                        <div class="info-content">
                            <strong>Product ID:</strong> #{{ str_pad($product->id, 5, '0', STR_PAD_LEFT) }}<br>
                            <strong>Created:</strong> {{ date('F j, Y', strtotime($product->created_at ?? 'now')) }}
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fa-regular fa-floppy-disk"></i>Update Product
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn-secondary">
                        <i class="fa-solid fa-x"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <style>
        .form-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.75rem;
            gap: 1rem;
        }

        .form-header-left {
            flex: 1;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #576238;
            text-decoration: none;
            font-size: 0.8rem;
            margin-bottom: 0.75rem;
            transition: all 0.2s ease;
        }

        .back-link:hover {
            color: #3E4A28;
            transform: translateX(-2px);
        }

        .form-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.25rem;
        }

        .form-description {
            color: #9E9D97;
            font-size: 0.85rem;
        }

        .product-status {
            background: #F0EADC;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .status-active {
            background: #E8F0E3;
            color: #576238;
        }

        .status-inactive {
            background: #FEF0ED;
            color: #C5705A;
        }

        .alert {
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .alert-error {
            background: #FEF0ED;
            border: 1px solid #C5705A;
            color: #C5705A;
        }

        .alert-error i {
            margin-top: 0.125rem;
        }

        .alert-error ul {
            margin: 0;
            padding-left: 1.25rem;
        }

        .alert-error li {
            margin-bottom: 0.25rem;
        }

        .alert-error li:last-child {
            margin-bottom: 0;
        }

        .product-form {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .form-grid {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
        }

        .form-section {
            border-bottom: 1px solid #F0EADC;
            padding-bottom: 1.5rem;
        }

        .form-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .section-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 1.25rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #576238;
            display: inline-block;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.8rem;
            color: #2C2B26;
        }

        .required {
            color: #C5705A;
            margin-left: 0.25rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
        }

        .input-with-icon {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 0.875rem;
            color: #9E9D97;
            font-size: 0.875rem;
        }

        .input-with-icon input {
            padding-left: 1.75rem;
        }

        .form-hint {
            display: block;
            font-size: 0.7rem;
            color: #9E9D97;
            margin-top: 0.375rem;
        }

        .info-box {
            background: #FDF8F0;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            padding: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .info-box i {
            color: #576238;
            font-size: 1rem;
            margin-top: 0.125rem;
        }

        .info-content {
            font-size: 0.8rem;
            color: #6B6A65;
            line-height: 1.5;
        }

        .form-actions {
            background: #FDF8F0;
            padding: 1rem 1.5rem;
            border-top: 1px solid #E3DCD0;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .btn-primary,
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background: #576238;
            color: white;
        }

        .btn-primary:hover {
            background: #3E4A28;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: white;
            color: #6B6A65;
            border: 1px solid #E3DCD0;
        }

        .btn-secondary:hover {
            background: #F0EADC;
            border-color: #576238;
            color: #576238;
        }
    </style>
@endsection