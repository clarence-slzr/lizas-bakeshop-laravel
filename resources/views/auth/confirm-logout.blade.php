<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Logout | Liza's Bakeshop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #F0EADC;
            color: #2C2B26;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            padding: 1.5rem;
        }

        .logout-card {
            background: white;
            border-radius: 1.25rem;
            padding: 2.5rem 2rem;
            border: 1px solid #E3DCD0;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 8px 32px rgba(87, 98, 56, 0.08);
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
        .logout-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .logout-logo-img {
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
        .logout-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #2C2B26;
            margin-bottom: 0.35rem;
            line-height: 1.2;
        }

        .logout-subtitle {
            font-size: 0.7rem;
            color: #9E9D97;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-weight: 500;
            margin-bottom: 1.75rem;
        }

        .logout-message {
            margin-bottom: 1.75rem;
        }

        .logout-message p {
            font-size: 0.9rem;
            color: #6B6A65;
            margin-bottom: 0.35rem;
            line-height: 1.5;
        }

        .logout-message strong {
            color: #2C2B26;
            font-weight: 600;
        }

        .logout-message small {
            font-size: 0.75rem;
            color: #9E9D97;
        }

        /* ============ BUTTONS ============ */
        .logout-buttons {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .btn-logout-yes,
        .btn-logout-no {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 0.625rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            cursor: pointer;
            flex: 1;
            font-family: inherit;
            border: none;
            line-height: 1;
            white-space: nowrap;
        }

        .btn-logout-yes {
            background: #C5705A;
            color: white;
            box-shadow: 0 2px 8px rgba(197, 112, 90, 0.25);
        }

        .btn-logout-yes:hover {
            background: #A85A45;
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(197, 112, 90, 0.35);
        }

        .btn-logout-yes:active {
            transform: translateY(0);
        }

        .btn-logout-no {
            background: #F0EADC;
            color: #576238;
            border: 1px solid #E3DCD0;
        }

        .btn-logout-no:hover {
            background: white;
            border-color: #576238;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.1);
        }

        .btn-logout-no:active {
            transform: translateY(0);
        }

        .btn-logout-yes i,
        .btn-logout-no i {
            font-size: 0.8rem;
        }

        /* ============ FOOTER NOTE ============ */
        .logout-note {
            padding-top: 1.25rem;
            border-top: 1px solid #F0EADC;
            font-size: 0.72rem;
            color: #9E9D97;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            line-height: 1.4;
        }

        .logout-note i {
            color: #576238;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 480px) {
            .logout-card {
                padding: 2rem 1.5rem;
            }

            .logout-title {
                font-size: 1.5rem;
            }

            .logout-logo-img {
                width: 80px;
                height: 80px;
            }

            .logout-buttons {
                flex-direction: column;
            }

            .btn-logout-yes,
            .btn-logout-no {
                width: 100%;
            }
        }

        @media print {
            body {
                background: white;
            }
        }
    </style>
</head>

<body>
    <div class="logout-card">

        {{-- ============ LOGO (walang icon na) ============ --}}
        <div class="logout-logo">
            <img src="{{ asset('images/liza-logo.png') }}" alt="Liza's Bakeshop" class="logout-logo-img">
        </div>

        {{-- ============ TEXT ============ --}}
        <h1 class="logout-title">Liza's Bakeshop</h1>
        <p class="logout-subtitle">Est. 1989 | San Miguel, Bulacan</p>

        <div class="logout-message">
            <p>Are you sure you want to <strong>logout</strong>?</p>
            <small>You'll be redirected to the homepage.</small>
        </div>

        {{-- ============ BUTTONS ============ --}}
        <div class="logout-buttons">
            <form method="POST" action="{{ route('logout') }}" style="flex: 1; display: flex;">
                @csrf
                <button type="submit" class="btn-logout-yes" style="width: 100%;">
                    <i class="fas fa-sign-out-alt"></i> Yes, Logout
                </button>
            </form>
            <a href="{{ route('dashboard') }}" class="btn-logout-no">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>

        {{-- ============ FOOTER NOTE ============ --}}
        <div class="logout-note">
            <i class="fas fa-info-circle"></i>
            <span>Make sure to save your work before logging out.</span>
        </div>

    </div>
</body>

</html>