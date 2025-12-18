<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - {{ __('Login') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Poppins', sans-serif;
        min-height: 100vh;
        background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 50%, #FFC107 100%);
        background: url('{{ asset('assets/img/BgForLoginAndRegister.png') }}') no-repeat center center fixed;
        background-size: cover;
        background-position: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        position: relative;
        overflow-x: hidden;
        overflow-y: auto;
        padding: 20px 0 120px 0;
    }

    /* Form row with forgot password link */
    .form-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .forgot-password-link {
        color: #4CAF50;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .forgot-password-link:hover {
        color: #296218;
        text-decoration: underline;
    }

    /* Additional styles for checkbox wrapper */
    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .checkbox-wrapper input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .checkbox-wrapper label {
        font-size: 13px;
        color: #333;
        cursor: pointer;
        user-select: none;
    }
</style>

</head>

<body>
    <!-- Logo, Title and Subtitle outside the container -->
    <div class="header-section">
        <div class="logo">
            @if(file_exists(public_path('assets/img/BgForLoginAndRegister.png'))) 
                <img src="{{ asset('assets/img/sample.png') }}" alt="{{ config('app.name', 'AGRISUPPLY') }}">
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
                
                <a href="{{ route('password.request') }}" class="forgot-password-link">
                    {{ __('Forgot Password?') }}
                </a>
            </div>

            <button type="submit" class="login-btn">
                {{ __('Log In') }}
            </button>


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

    @include('layouts.core.footer')
</body>
</html>