<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Connexion') - {{ config('app.name', 'CommercialiZe') }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #FF6B35;
            --secondary: #004E89;
            --dark: #1a1a2e;
        }
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--dark) 0%, #16213e 50%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        
        .auth-container {
            width: 100%;
            max-width: 420px;
        }
        
        .auth-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .auth-header {
            background: linear-gradient(135deg, var(--primary) 0%, #e55a2b 100%);
            color: #fff;
            padding: 2rem;
            text-align: center;
        }
        
        .auth-logo {
            max-height: 50px;
            margin-bottom: 1rem;
        }
        
        .auth-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .auth-subtitle {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .auth-body {
            padding: 2rem;
        }
        
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: var(--primary);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.15);
        }
        
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: #e55a2b;
            border-color: #e55a2b;
        }
        
        .auth-footer {
            text-align: center;
            padding: 1.5rem 2rem;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        
        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e9ecef;
        }
        
        .divider span {
            padding: 0 1rem;
            color: #6c757d;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                {{-- Logo --}}
                @if(config('commercialize.logo'))
                    <img src="{{ asset(config('commercialize.logo')) }}" alt="Logo" class="auth-logo">
                @else
                    <i class="fas fa-chart-line fa-3x mb-3"></i>
                @endif
                
                <h1 class="auth-title">@yield('auth-title', config('app.name', 'CommercialiZe'))</h1>
                <p class="auth-subtitle mb-0">@yield('auth-subtitle', 'Gérez votre activité commerciale')</p>
            </div>
            
            <div class="auth-body">
                {{-- Alerts --}}
                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @yield('content')
            </div>
            
            @hasSection('footer')
            <div class="auth-footer">
                @yield('footer')
            </div>
            @endif
        </div>
        
        <p class="text-center text-white-50 mt-4 small">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.
        </p>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password toggle
        document.querySelectorAll('.password-toggle').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
