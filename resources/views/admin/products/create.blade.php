@extends('layouts.app')

@section('content')
    @php
        $page_title = 'Add New Product';
        $hide_page_title = true;
    @endphp

    <div class="form-container">
        <div class="form-header">
            <div class="form-header-left">
                <a href="{{ route('admin.products.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
                <h1>Add New Product</h1>
                <p class="form-description">Add a new product to your bakery inventory</p>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.products.store') }}" method="POST" class="product-form" enctype="multipart/form-data">
            @csrf
            <div class="form-grid">
                <div class="form-section">
                    <h3 class="section-title">Basic Information</h3>

                    <div class="form-group">
                        <label for="name">Product Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                            placeholder="e.g., Pandesal, Ube Cake, etc.">
                        <small class="form-hint">Enter the full product name as it appears to customers</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="input-icon">₱</span>
                                <input type="number" id="price" name="price" step="0.01" min="0" value="{{ old('price') }}"
                                    required placeholder="0.00">
                            </div>
                            <small class="form-hint">Price in Philippine Peso (PHP)</small>
                        </div>

                        <div class="form-group">
                            <label for="stock">Initial Stock Quantity</label>
                            <input type="number" id="stock" name="stock" min="0" value="{{ old('stock', 0) }}"
                                placeholder="0">
                            <small class="form-hint">Current inventory count (can be updated later)</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="category">Category <span class="required">*</span></label>
                        <select id="category" name="category" required>
                            <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select a category</option>
                            <option value="Breads" {{ old('category') == 'Breads' ? 'selected' : '' }}>Breads</option>
                            <option value="Cakes" {{ old('category') == 'Cakes' ? 'selected' : '' }}>Cakes</option>
                            <option value="Pastries" {{ old('category') == 'Pastries' ? 'selected' : '' }}>Pastries</option>
                            <option value="Cookies" {{ old('category') == 'Cookies' ? 'selected' : '' }}>Cookies</option>
                            <option value="Others" {{ old('category') == 'Others' ? 'selected' : '' }}>Others</option>
                        </select>
                        <small class="form-hint">Select the product category for organization</small>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Availability Settings</h3>

                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_available" value="1" checked>
                            <span class="checkbox-text">
                                <strong>Available for purchase</strong>
                                <small>When unchecked, this product will be hidden from POS</small>
                            </span>
                        </label>
                    </div>

                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <div class="info-content">
                            <strong>Note:</strong> Products marked as "Available" will appear in the POS system for cashiers
                            to sell.
                            You can always edit this later.
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Save Product
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>

    <style>
        .form-container {
            max-width: 800px;
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

        .alert {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-error {
            background: #FEF0ED;
            border: 1px solid #C5705A;
            color: #C5705A;
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
            line-height: 1.4;
        }

        .checkbox-group {
            margin-bottom: 0;
        }

        .checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            cursor: pointer;
        }

        .checkbox-label input {
            width: 18px;
            height: 18px;
            margin-top: 0.125rem;
            cursor: pointer;
        }

        .checkbox-text {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .checkbox-text strong {
            font-size: 0.85rem;
            color: #2C2B26;
        }

        .checkbox-text small {
            font-size: 0.7rem;
            color: #9E9D97;
            font-weight: normal;
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

        .info-content strong {
            color: #2C2B26;
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