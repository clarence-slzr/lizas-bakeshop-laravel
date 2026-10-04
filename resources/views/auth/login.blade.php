<x-guest-layout>
    <div class="login-section">
        <div class="login-container">
            <div class="login-card" id="loginCard">

                {{-- ============ LOGO ============ --}}
                <div class="login-logo">
                    <img src="/assets/images/liza-logo.jpg" alt="Liza's Bakeshop" class="login-logo-img">
                </div>

                {{-- ============ TEXT ============ --}}
                <h1 class="login-title">Liza's Bakeshop</h1>
                <p class="login-subtitle">Est. 1989 | San Miguel, Bulacan</p>

                {{-- ============ ERROR ============ --}}
                @if($errors->any())
                    <div class="alert error" id="loginError">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- ============ FORM ============ --}}
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" value="{{ old('username') }}" required
                            autofocus autocomplete="username" placeholder="Enter your username">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" required
                                autocomplete="current-password" placeholder="Enter your password">
                            <button type="button" class="password-toggle" onclick="togglePassword()" tabindex="-1">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                {{-- ============ FOOTER NOTE ============ --}}
                <div class="login-note">
                    <i class="fas fa-info-circle"></i>
                    <span>This is a private system. Authorized users only.</span>
                </div>

                {{-- ============ BACK TO HOME ============ --}}
                <div class="back-home">
                    <a href="{{ route('landing') }}">
                        <i class="fas fa-arrow-left"></i> Back to Homepage
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ============ LOGIN SECTION ============ */
        .login-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: #F0EADC;
            margin: -1.5rem;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .login-container {
            max-width: 420px;
            width: 100%;
        }

        /* ============ CARD ============ */
        .login-card {
            background: white;
            border-radius: 1.25rem;
            padding: 2.5rem 2rem;
            border: 1px solid #E3DCD0;
            box-shadow: 0 8px 32px rgba(87, 98, 56, 0.08);
            text-align: center;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============ LOGO (mas malaki ngayon) ============ */
        .login-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .login-logo-img {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #F0EADC;
            box-shadow: 0 6px 20px rgba(87, 98, 56, 0.12);
            background: white;
            padding: 6px;
        }

        /* ============ TEXT ============ */
        .login-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #2C2B26;
            margin-bottom: 0.35rem;
            line-height: 1.2;
        }

        .login-subtitle {
            font-size: 0.7rem;
            color: #9E9D97;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-weight: 500;
            margin-bottom: 1.75rem;
        }

        /* ============ ALERT ============ */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-align: left;
            line-height: 1.4;
        }

        .alert.error {
            background: #FEF0ED;
            color: #C5705A;
            border: 1px solid #F8DCD4;
        }

        .alert i {
            flex-shrink: 0;
        }

        /* ============ FORM ============ */
        .form-group {
            margin-bottom: 1.25rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #2C2B26;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
            background: white;
            box-sizing: border-box;
            color: #2C2B26;
        }

        .form-group input::placeholder {
            color: #C4C3BC;
        }

        .form-group input:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
        }

        /* ============ PASSWORD TOGGLE ============ */
        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 2.75rem;
        }

        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #9E9D97;
            cursor: pointer;
            font-size: 0.9rem;
            padding: 0.25rem;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: #576238;
        }

        /* ============ BUTTON ============ */
        .btn-login {
            width: 100%;
            background: #576238;
            color: white;
            border: none;
            padding: 0.85rem;
            border-radius: 0.625rem;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: 'Inter', sans-serif;
            margin-top: 0.5rem;
            letter-spacing: 0.3px;
        }

        .btn-login:hover {
            background: #3E4A28;
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(87, 98, 56, 0.25);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* ============ FOOTER NOTE ============ */
        .login-note {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid #F0EADC;
            font-size: 0.72rem;
            color: #9E9D97;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            line-height: 1.4;
            text-align: center;
        }

        .login-note i {
            color: #576238;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        /* ============ BACK HOME ============ */
        .back-home {
            text-align: center;
            margin-top: 1rem;
        }

        .back-home a {
            font-size: 0.8rem;
            color: #576238;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: color 0.2s ease;
            font-weight: 500;
        }

        .back-home a:hover {
            color: #3E4A28;
            text-decoration: underline;
        }

        .back-home a i {
            font-size: 0.75rem;
        }

        /* ============ SHAKE ANIMATION ============ */
        .shake {
            animation: shake 0.5s;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-8px);
            }

            75% {
                transform: translateX(8px);
            }
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
            }

            .login-title {
                font-size: 1.5rem;
            }

            .login-logo-img {
                width: 80px;
                height: 80px;
            }
        }
    </style>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (password.type === 'password') {
                password.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                password.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        @if($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                const card = document.getElementById('loginCard');
                if (card) {
                    card.classList.add('shake');
                    setTimeout(() => card.classList.remove('shake'), 500);
                }
            });
        @endif
    </script>
</x-guest-layout>