<x-guest-layout>
    <div class="login-section">
        <div class="login-container">
            <div class="login-card" id="loginCard">
                <div class="login-header">
                    <img src="/assets/images/liza-logo.jpg" alt="Liza's Bakeshop">
                    <h2>Liza's Bakeshop</h2>
                    <p class="login-subtitle">Sign in to your account</p>
                </div>

                <!-- ERROR MESSAGE -->
                @if($errors->any())
                    <div class="alert error" id="loginError">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" value="{{ old('username') }}" required
                            autofocus autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" required
                                autocomplete="current-password">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                <div class="demo-note">
                    <i class="fas fa-info-circle"></i> This is a private system. Authorized users only.
                </div>

                <div class="back-home">
                    <a href="{{ route('landing') }}">
                        <i class="fas fa-arrow-left"></i> Back to Homepage
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* LOGIN SECTION — CUSTOM DESIGN */
        .login-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #F0EADC;
            margin: -1.5rem;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .login-container {
            max-width: 400px;
            width: 100%;
        }

        .login-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            border: 1px solid #E3DCD0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            object-fit: cover;
            display: block;
        }

        .login-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: #2C2B26;
            margin-bottom: 0.25rem;
        }

        .login-subtitle {
            font-size: 0.85rem;
            color: #9E9D97;
            margin-top: 0.25rem;
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

        .alert.error {
            background: #FCE8E6;
            color: #C5705A;
            border: 1px solid #C5705A;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: #2C2B26;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            transition: border-color 0.2s ease;
            font-family: 'Inter', sans-serif;
            background: white;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
        }

        /* Password toggle */
        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 2.5rem;
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
        }

        .password-toggle:hover {
            color: #576238;
        }

        .btn-login {
            width: 100%;
            background: #576238;
            color: white;
            border: none;
            padding: 0.85rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: 'Inter', sans-serif;
        }

        .btn-login:hover {
            background: #3E4A28;
        }

        .demo-note {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #E3DCD0;
            font-size: 0.7rem;
            color: #9E9D97;
            text-align: center;
            line-height: 1.6;
        }

        .demo-note i {
            margin-right: 0.25rem;
            color: #576238;
        }

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
            gap: 0.3rem;
        }

        .back-home a:hover {
            text-decoration: underline;
        }

        /* Shake animation for login error */
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