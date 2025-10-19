<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - {{ __('Login') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 50%, #FFC107 100%);
            background: url('{{ asset('assets/img/atirtc1bg.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow-x: hidden;
            padding: 20px 0;
        }

        /* Background overlay effect */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(ellipse at 20% 20%, rgba(76, 175, 80, 0.3) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(255, 193, 7, 0.3) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(139, 195, 74, 0.2) 0%, transparent 70%);
            z-index: 1;
        }

        /* Header section styles */
        .header-section {
            text-align: center;
            margin-bottom: 30px;
            z-index: 2;
            position: relative;
        }

        .header-section .logo {
            margin-bottom: 20px;
        }

        .header-section .logo img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .header-section .logo-fallback {
            width: 80px;
            height: 80px;
            background: #2e7d32;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .header-section .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 12px;
            letter-spacing: 1px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .header-section .subtitle {
            color: rgba(255, 255, 255, 0.95);
            font-size: 1rem;
            line-height: 1.5;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
        }

        .header-section .subtitle strong {
            color: white;
            font-weight: 600;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.2),
                0 8px 25px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 2;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1.2rem;
            z-index: 3;
            width: 20px;
            height: 20px;
        }

        .form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background: #fafafa;
        }

        .form-control:focus {
            outline: none;
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
            background: white;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
            background: #fff5f5;
        }

        .form-control.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        .invalid-feedback {
            display: none; /* Hide inline errors since we're using popup */
            width: 100%;
            margin-top: 8px;
            font-size: 0.875rem;
            color: #dc3545;
            background: #f8d7da;
            padding: 8px 12px;
            border-radius: 6px;
            border-left: 4px solid #dc3545;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 5px;
            z-index: 3;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: #2e7d32;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #2e7d32;
        }

        .checkbox-wrapper label {
            margin: 0 !important;
            font-size: 0.9rem;
            color: #666;
        }

        .forgot-link {
            color: #2e7d32;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: #1b5e20;
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            background: #2e7d32;
            color: white;
            border: none;
            padding: 16px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-bottom: 20px;
        }

        .login-btn:hover {
            background: #1b5e20;
            transform: translateY(-1px);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
            color: #999;
            font-size: 0.9rem;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e0e0e0;
            z-index: 1;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            z-index: 2;
        }

        .google-btn {
            width: 100%;
            background: white;
            border: 2px solid #e0e0e0;
            padding: 14px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 25px;
            text-decoration: none;
            color: #333;
        }

        .google-btn:hover {
            border-color: #2e7d32;
            box-shadow: 0 2px 8px rgba(46, 125, 50, 0.1);
            text-decoration: none;
            color: #333;
        }

        .google-icon {
            width: 20px;
            height: 20px;
        }

        .signup-text {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }

        .signup-link {
            color: #2e7d32;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-link:hover {
            color: #1b5e20;
            text-decoration: underline;
        }

        /* Error Modal Styles */
        .error-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .error-modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .error-modal-icon {
            width: 60px;
            height: 60px;
            background: #dc3545;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: white;
            font-size: 24px;
        }

        .error-modal-title {
            color: #dc3545;
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .error-modal-message {
            color: #666;
            font-size: 1rem;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .error-modal-close {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .error-modal-close:hover {
            background: #c82333;
        }

        /* Mobile responsiveness */
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .header-section .welcome-title {
                font-size: 2rem;
            }
            
            .header-section .subtitle {
                font-size: 0.9rem;
            }
            
            .header-section .logo img,
            .header-section .logo-fallback {
                width: 70px;
                height: 70px;
            }
            
            .login-container {
                padding: 30px 25px;
            }

            .form-control {
                padding: 14px 14px 14px 40px;
            }

            .form-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .error-modal-content {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                padding: 30px 25px;
                width: 95%;
                max-width: 350px;
            }
        }

        /* Navigation styles for top links */
        .nav-links {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 10;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            margin-left: 15px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <!-- Logo, Title and Subtitle outside the container -->
    <div class="header-section">
        <div class="logo">
            @if(file_exists(public_path('assets/img/atirtc1logo.jpg')))
                <img src="{{ asset('assets/img/atirtc1logo.jpg') }}" alt="{{ config('app.name', 'AGRISUPPLY') }}">
            @else
                <div class="logo-fallback">
                    {{ substr(config('app.name', 'AGRISUPPLY'), 0, 2) }}
                </div>
            @endif
        </div>
        
        <h1 class="welcome-title">{{ __('WELCOME BACK') }}</h1>
        <p class="subtitle"><strong>{{ __('Log in') }}</strong><br>{{ __('Enter your verified email and password for signing in.') }}</p>
    </div>

    <div class="login-container">
        
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">{{ __('Enter your email address') }}</label>
                <div class="input-wrapper">
                    <ion-icon name="person-outline" class="input-icon"></ion-icon>
                    <input id="email" 
                           type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           name="email" 
                           value="{{ old('email') }}" 
                           placeholder="name@gmail.com"
                           required 
                           autocomplete="email" 
                           autofocus>

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="password">{{ __('Enter your password') }}</label>
                <div class="input-wrapper">
                    <ion-icon name="lock-closed-outline" class="input-icon"></ion-icon>
                    <input id="password" 
                           type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           name="password" 
                           placeholder="mypassword"
                           required 
                           autocomplete="current-password">
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <ion-icon name="eye-outline" id="toggle-icon"></ion-icon>
                    </button>

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="checkbox-wrapper">
                    <input type="checkbox" 
                           name="remember" 
                           id="remember" 
                           {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">{{ __('Remember me') }}</label>
                </div>
                
                @if (Route::has('password.request'))
                    <a class="forgot-link" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <button type="submit" class="login-btn">
                {{ __('Log In') }}
            </button>

            <div class="divider">
                <span>{{ __('Or') }}</span>
            </div>

            {{-- Google Sign In Button (optional - remove if not needed) --}}
            <a href="#" class="google-btn" onclick="alert('Google OAuth not configured yet')">
                <svg class="google-icon" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                {{ __('Sign in with Google') }}
            </a>

            <p class="signup-text">
                {{ __("Don't have an account?") }} 
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="signup-link">{{ __('Sign up') }}</a>
                @else
                    <a href="#" class="signup-link">{{ __('Sign up') }}</a>
                @endif
            </p>
        </form>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="error-modal">
        <div class="error-modal-content">
            <div class="error-modal-icon">
                <ion-icon name="close-circle-outline"></ion-icon>
            </div>
            <h3 class="error-modal-title">Login Failed</h3>
            <p class="error-modal-message">Invalid email or password. Please try again.</p>
            <button class="error-modal-close" onclick="closeErrorModal()">Try Again</button>
        </div>
    </div>

    <script>
        // Check for authentication errors on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Check if there are any authentication errors
            const hasEmailError = document.querySelector('.form-control[name="email"].is-invalid');
            const hasPasswordError = document.querySelector('.form-control[name="password"].is-invalid');
            
            // If there are authentication errors, show the modal instead of inline errors
            if (hasEmailError || hasPasswordError) {
                showErrorModal();
            }
        });

        function showErrorModal() {
            document.getElementById('errorModal').style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }

        function closeErrorModal() {
            document.getElementById('errorModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore scrolling
            
            // Clear the form and remove error classes
            document.getElementById('email').classList.remove('is-invalid');
            document.getElementById('password').classList.remove('is-invalid');
            document.getElementById('password').value = ''; // Clear password field
            document.getElementById('email').focus(); // Focus back to email field
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('errorModal');
            if (event.target == modal) {
                closeErrorModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeErrorModal();
            }
        });

        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggle-icon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.setAttribute('name', 'eye-off-outline');
            } else {
                passwordField.type = 'password';
                toggleIcon.setAttribute('name', 'eye-outline');
            }
        }

        // Add interactive effects
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Clear validation errors on input
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                const feedback = this.parentElement.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.style.display = 'none';
                }
            });
        });
    </script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>