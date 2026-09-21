@extends('layouts.app')

@section('content')
    @php
        $page_title = 'Add New User';
        $hide_page_title = true;

        $error = session('error') ?? '';
        if ($error) {
            session()->forget('error');
        }
    @endphp

    <div class="form-container">
        <div class="form-header">
            <div class="form-header-left">
                <a href="{{ route('admin.users.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
                <h1>Add New User</h1>
                <p class="form-description">Create a new system user account</p>
            </div>
        </div>

        @if($error)
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ $error }}
            </div>
        @endif

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

        <form action="{{ route('admin.users.store') }}" method="POST" class="user-form">
            @csrf
            <div class="form-grid">
                <div class="form-section">
                    <h3 class="section-title">Account Information</h3>

                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus>
                        <small class="form-hint">Used for logging in (Example: john_doe)</small>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                        <small class="form-hint">Complete name of the user (Example: John M. Doe)</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password <span class="required">*</span></label>
                            <input type="password" id="password" name="password" required>
                            <small class="form-hint">Minimum 6 characters for security</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                            <small class="form-hint">Re-enter the password to confirm</small>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Role & Permissions</h3>

                    <div class="form-group">
                        <label for="role">User Role <span class="required">*</span></label>
                        <select id="role" name="role">
                            <option value="cashier" {{ old('role') == 'cashier' ? 'selected' : '' }}>Cashier</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>
                        <small class="form-hint">
                            <strong>Cashier:</strong> Can process orders, view inventory<br>
                            <strong>Admin:</strong> Full access to all features
                        </small>
                    </div>

                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <div class="info-content">
                            <strong>Note:</strong> New users will be able to log in immediately after creation.
                            Please provide the username and password securely to the user.
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fa-regular fa-floppy-disk"></i> Add User
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">
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

        .alert-error i {
            font-size: 1rem;
        }

        .user-form {
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

        .form-hint {
            display: block;
            font-size: 0.7rem;
            color: #9E9D97;
            margin-top: 0.375rem;
            line-height: 1.4;
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

    <script>
        document.querySelector('.user-form')?.addEventListener('submit', function (e) {
            const password = document.getElementById('password');
            const confirm = document.getElementById('confirm_password');

            if (password.value !== confirm.value) {
                e.preventDefault();
                alert('Password and Confirm Password do not match!');
                confirm.focus();
            }
        });
    </script>
@endsection