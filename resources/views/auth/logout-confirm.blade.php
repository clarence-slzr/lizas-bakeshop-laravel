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
        }

        .logout-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 1.5rem;
            width: 100%;
        }

        .logout-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            border: 1px solid #E3DCD0;
            max-width: 400px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logout-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .logout-logo-img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 0.75rem;
            border: 2px solid #E3DCD0;
        }

        .logout-logo h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 600;
            color: #2C2B26;
            margin: 0;
        }

        .logout-logo p {
            font-size: 0.7rem;
            color: #9E9D97;
            margin-top: 0.25rem;
        }

        .logout-icon {
            font-size: 3rem;
            color: #C5705A;
            margin: 1rem 0;
            width: 70px;
            height: 70px;
            background: #FEF0ED;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            margin-right: auto;
        }

        .logout-message {
            margin-bottom: 1.75rem;
        }

        .logout-message p {
            font-size: 0.9rem;
            color: #6B6A65;
            margin-bottom: 0.5rem;
        }

        .logout-message strong {
            color: #2C2B26;
            font-weight: 600;
        }

        .logout-buttons {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }

        .btn-logout-yes,
        .btn-logout-no {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            cursor: pointer;
            flex: 1;
            font-family: inherit;
            border: none;
        }

        .btn-logout-yes:active,
        .btn-logout-no:active {
            transform: scale(0.98);
        }

        .btn-logout-yes {
            background: #C5705A;
            color: white;
        }

        .btn-logout-yes:hover {
            background: #A85A45;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(197, 112, 90, 0.2);
        }

        .btn-logout-no {
            background: #F0EADC;
            color: #576238;
            border: 1px solid #E3DCD0;
        }

        .btn-logout-no:hover {
            background: #E3DCD0;
            transform: translateY(-1px);
            border-color: #576238;
        }

        .demo-note {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #E3DCD0;
            font-size: 0.7rem;
            color: #9E9D97;
        }

        .demo-note i {
            margin-right: 0.25rem;
            color: #576238;
        }

        @media print {
            body {
                background: white;
            }
        }
    </style>
</head>

<body>
    <div class="logout-container">
        <div class="logout-card">
            <div class="logout-logo">
                <img src="{{ asset('images/liza-logo.png') }}" alt="Liza's Bakeshop" class="logout-logo-img">
            </div>
            <div class="logout-icon">
                <i class="fas fa-arrow-right-from-bracket"></i>
            </div>
            <h1>Liza's Bakeshop</h1>
            <p style="font-size: 0.7rem; color: #9E9D97; margin-bottom: 1.5rem;">Est. 1989 | San Miguel, Bulacan</p>

            <div class="logout-message">
                <p>Are you sure you want to <strong>logout</strong>?</p>
                <p style="font-size: 0.75rem; color: #9E9D97;">You'll be redirected to the homepage.</p>
            </div>

            <div class="logout-buttons">
                <form method="POST" action="{{ route('logout') }}" style="flex: 1; margin: 0;">
                    @csrf
                    <button type="submit" class="btn-logout-yes" style="width: 100%;">
                        <i class="fas fa-sign-out-alt"></i> Yes, Logout
                    </button>
                </form>
                <a href="{{ route('dashboard') }}" class="btn-logout-no">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>

            <div class="demo-note">
                <i class="fas fa-info-circle"></i> Make sure to save your work before logging out.
            </div>
        </div>
    </div>
</body>

</html>