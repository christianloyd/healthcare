<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare Portal - Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
        }

        .login-card {
            display: flex;
            max-width: 1000px;
            width: 100%;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }

        /* Left Panel - Illustration */
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #D4A373 0%, #C9956B 100%);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .branding {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .logo-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .logo-circle img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
        }

        .branding h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .branding p {
            font-size: 14px;
            opacity: 0.95;
        }

        .illustration {
            max-width: 350px;
            width: 100%;
            margin-top: 20px;
        }

        .illustration img {
            width: 100%;
            height: auto;
            filter: drop-shadow(0 10px 30px rgba(0,0,0,0.1));
        }

        .features {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .feature-badge {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            backdrop-filter: blur(10px);
        }

        /* Right Panel - Login Form */
        .right-panel {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            font-size: 56px !important;
            font-weight: 700 !important;
            color: #1f2937 !important;
            margin-bottom: 15px !important;
            letter-spacing: -1px !important;
        }

        .secure-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fef3c7;
            color: #92400e;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .login-description {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 16px;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 45px;
            font-size: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .form-input:focus {
            outline: none;
            border-color: #D4A373;
            box-shadow: 0 0 0 3px rgba(212, 163, 115, 0.1);
            background: white;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            font-size: 16px;
            padding: 8px;
        }

        .password-toggle:hover {
            color: #D4A373;
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4b5563;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #D4A373;
            cursor: pointer;
        }

        .forgot-link {
            color: #D4A373;
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            background: linear-gradient(135deg, #D4A373 0%, #B8956A 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(212, 163, 115, 0.3);
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 163, 115, 0.4);
        }

        .portal-note {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #6b7280;
            padding: 12px;
            background: #fef3c7;
            border-radius: 8px;
            border-left: 3px solid #D4A373;
        }

        .footer-text {
            text-align: center;
            margin-top: 25px;
            font-size: 11px;
            color: #9ca3af;
        }

        .footer-text span {
            display: block;
            margin-top: 5px;
            font-weight: 600;
            letter-spacing: 1px;
            color: #D4A373;
        }

        .error-message {
            color: #ef4444;
            font-size: 12px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-card {
                flex-direction: column;
                max-width: 500px;
            }

            .left-panel {
                padding: 40px 30px;
            }

            .right-panel {
                padding: 40px 30px;
            }

            .illustration {
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <!-- Left Panel - Branding & Imagery -->
        <div class="left-panel">
            <div class="branding">
                <div class="logo-circle">
                    <img src="{{ asset('images/logo_final.jpg') }}" alt="Healthcare Logo">
                </div>
                <h1>HealthCare Portal</h1>
                <p>Prenatal & Immunization Management System</p>
            </div>

            <div class="illustration">
                <img src="{{ asset('images/maternal-care.png') }}" alt="Maternal Care" onerror="this.style.display='none'">
            </div>

            <div class="features">
                
                <div class="feature-badge">
                    <i class="fas fa-baby"></i>
                    <span>Prenatal Care</span>
                </div>
                <div class="feature-badge">
                    <i class="fas fa-syringe"></i>
                    <span>Immunization</span>
                </div>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="right-panel">
            <div class="login-box">
                <div class="login-header">
                    <h2 style="font-size: 56px !important; font-weight: 700 !important; letter-spacing: -1px !important;">Login</h2>
                    <div class="secure-badge">
                        <i class="fas fa-lock"></i>
                        <span>Authorized Access Only</span>
                    </div>
                </div>

                <p class="login-description">
                    Sign in with your assigned credentials to manage prenatal and immunization records for your community.
                </p>

                @include('components.flowbite-alert')

                <form action="{{ route('login.authenticate') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                required
                                value="{{ old('username') }}"
                                class="form-input"
                                placeholder="Enter your username"
                                autocomplete="username"
                            >
                        </div>
                        @error('username')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                class="form-input"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye-slash" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div class="form-footer">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="login-button">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Sign In</span>
                    </button>
                </form>

                

                <div class="footer-text">
                    Preventive Healthcare Management System © {{ date('Y') }}
                    
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>