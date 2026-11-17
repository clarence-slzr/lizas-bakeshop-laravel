@extends('layouts.app')

@section('content')
    <div class="profile-container">

        {{-- ============================================ --}}
        {{-- SUCCESS POPUP ALERT --}}
        {{-- ============================================ --}}
        @if(session('status') === 'profile-updated' || session('status') === 'password-updated')
            <div class="success-alert" id="successAlert">
                <div class="alert-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="alert-content">
                    <strong>Success!</strong>
                    @if(session('status') === 'password-updated')
                        Your password has been updated successfully.
                    @else
                        Your profile has been saved successfully.
                    @endif
                </div>
                <button type="button" class="alert-close" onclick="document.getElementById('successAlert').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        {{-- HEADER CARD --}}
        <div class="profile-header-card">
            <div class="profile-avatar-wrapper">
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->full_name }}" class="profile-avatar-img">
                @else
                    <div class="profile-avatar-initials">
                        {{ strtoupper(substr($user->full_name, 0, 1)) }}
                    </div>
                @endif
            </div>
            <div class="profile-header-info">
                <h1>{{ $user->full_name }}</h1>
                <span class="profile-role-badge {{ $user->role === 'admin' ? 'role-admin' : 'role-cashier' }}">
                    {{ $user->role === 'admin' ? 'Administrator' : 'Cashier' }}
                </span>
                <div class="profile-meta">
                    <span><i class="fas fa-user"></i> {{ $user->username }}</span>
                    @if($user->phone)
                        <span><i class="fas fa-phone"></i> {{ $user->phone }}</span>
                    @endif
                    @if($user->last_login)
                        <span><i class="fas fa-clock"></i> Last login: {{ $user->last_login->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ACTIVITY STATS --}}
        <div class="profile-card">
            <div class="profile-card-header">
                <h2><i class="fas fa-chart-line"></i> My Activity This Week</h2>
            </div>
            <div class="activity-grid">
                @foreach($activity['stats'] as $stat)
                    <div class="activity-stat">
                        <div class="activity-icon">
                            <i class="fas {{ $stat['icon'] }}"></i>
                        </div>
                        <div class="activity-info">
                            <span class="activity-label">{{ $stat['label'] }}</span>
                            <span class="activity-value">{{ $stat['value'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- PERSONAL INFO --}}
        <div class="profile-card">
            <div class="profile-card-header">
                <h2><i class="fas fa-user-edit"></i> Personal Information</h2>
            </div>
            <div class="profile-form">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        {{-- PASSWORD --}}
        <div class="profile-card">
            <div class="profile-card-header">
                <h2><i class="fas fa-lock"></i> Change Password</h2>
            </div>
            <div class="profile-form">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        {{-- DELETE ACCOUNT --}}
        <div class="profile-card">
            <div class="profile-card-header">
                <h2><i class="fas fa-trash"></i> Delete Account</h2>
            </div>
            <div class="profile-form">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- SCRIPT PARA SA AUTO-HIDE POPUP --}}
    {{-- ============================================ --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const alert = document.getElementById('successAlert');
            if (alert) {
                // Mag-hide pagkalipas ng 3.5 seconds
                setTimeout(function () {
                    alert.style.transition = 'all 0.4s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(function () {
                        alert.remove();
                    }, 400);
                }, 3500);
            }
        });
    </script>

    <style>
        .profile-container {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            padding: 1.5rem;
        }

        /* ============ SUCCESS POPUP ============ */
        .success-alert {
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: white;
            border: 1px solid #C5D4A8;
            border-left: 5px solid #576238;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 10px 30px rgba(87, 98, 56, 0.15);
            z-index: 9999;
            animation: slideInRight 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-icon {
            width: 36px;
            height: 36px;
            background: #E8F0E3;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #576238;
            flex-shrink: 0;
        }

        .alert-content {
            font-size: 0.85rem;
            color: #2C2B26;
            line-height: 1.4;
        }

        .alert-content strong {
            color: #576238;
            display: block;
            margin-bottom: 0.15rem;
            font-size: 0.9rem;
        }

        .alert-close {
            background: transparent;
            border: none;
            color: #9E9D97;
            cursor: pointer;
            padding: 0.25rem;
            margin-left: 0.5rem;
            transition: color 0.2s;
        }

        .alert-close:hover {
            color: #C5705A;
        }

        /* ============ HEADER CARD ============ */
        .profile-header-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 1.75rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .profile-avatar-wrapper {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 3px solid #F0EADC;
            background: linear-gradient(135deg, #7A8B4F, #576238);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-avatar-initials {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            font-family: 'Playfair Display', serif;
        }

        .profile-header-info h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #2C2B26;
            margin: 0 0 0.4rem 0;
        }

        .profile-role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }

        .profile-role-badge.role-admin {
            background: rgba(212, 160, 84, 0.15);
            color: #B8893A;
        }

        .profile-role-badge.role-cashier {
            background: rgba(87, 98, 56, 0.15);
            color: #576238;
        }

        .profile-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.8rem;
            color: #9E9D97;
        }

        .profile-meta span i {
            color: #576238;
            margin-right: 0.3rem;
        }

        /* ============ CARDS ============ */
        .profile-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .profile-card-header {
            background: #FDF8F0;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #E3DCD0;
        }

        .profile-card-header h2 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2C2B26;
            margin: 0;
        }

        .profile-card-header h2 i {
            color: #576238;
            margin-right: 0.5rem;
        }

        /* ============ ACTIVITY GRID ============ */
        .activity-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            padding: 1.25rem;
        }

        @media (max-width: 768px) {
            .activity-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .activity-stat {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem;
            background: #FDF8F0;
            border-radius: 0.5rem;
            border: 1px solid #F0EADC;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .activity-icon i {
            color: #576238;
            font-size: 1rem;
        }

        .activity-info {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            min-width: 0;
        }

        .activity-label {
            font-size: 0.65rem;
            color: #9E9D97;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .activity-value {
            font-size: 0.95rem;
            font-weight: 700;
            color: #2C2B26;
        }

        /* ============ FORM STYLING ============ */
        .profile-form {
            padding: 1.5rem;
        }

        .profile-form form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .profile-form .form-group,
        .profile-form>form>div {
            margin-bottom: 1.25rem !important;
        }

        .profile-form .form-group:last-child,
        .profile-form>form>div:last-child {
            margin-bottom: 0 !important;
        }

        .profile-form label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #2C2B26;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .profile-form input[type="text"],
        .profile-form input[type="email"],
        .profile-form input[type="password"],
        .profile-form input[type="tel"],
        .profile-form input[type="file"],
        .profile-form textarea,
        .profile-form select {
            width: 100%;
            padding: 0.7rem 0.875rem;
            border: 1px solid #E3DCD0;
            border-radius: 8px;
            font-size: 0.85rem;
            font-family: inherit;
            color: #2C2B26;
            background: white;
            transition: all 0.2s ease;
            margin-top: 0.25rem;
        }

        .profile-form input:focus,
        .profile-form textarea:focus,
        .profile-form select:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
        }

        .profile-form input::placeholder,
        .profile-form textarea::placeholder {
            color: #B8B7B0;
        }

        .profile-form small,
        .profile-form .form-hint {
            display: block;
            font-size: 0.7rem;
            color: #9E9D97;
            margin-top: 0.35rem;
        }

        .profile-form .alert,
        .profile-form .text-green-600,
        .profile-form .text-red-600 {
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }

        .profile-form button[type="submit"],
        .profile-form .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #7A8B4F, #576238);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            align-self: flex-start;
            margin-top: 0.5rem;
        }

        .profile-form button[type="submit"]:hover,
        .profile-form .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.3);
        }

        .profile-form .btn-danger,
        .profile-form button[type="submit"].danger {
            background: linear-gradient(135deg, #E8A594, #C5705A);
        }

        .profile-form .btn-danger:hover,
        .profile-form button[type="submit"].danger:hover {
            box-shadow: 0 4px 12px rgba(197, 112, 90, 0.3);
        }

        .profile-form input[type="checkbox"],
        .profile-form input[type="radio"] {
            width: auto;
            margin-right: 0.5rem;
        }

        .profile-form input:disabled {
            background: #F0EADC;
            cursor: not-allowed;
        }

        .profile-form .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        @media (max-width: 640px) {
            .profile-form .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection