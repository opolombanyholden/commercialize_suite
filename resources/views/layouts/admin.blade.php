<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'CommercialiZe') }}</title>
    
    <!-- Google Fonts : Montserrat (charte officielle CommercialiZe) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- Admin CSS -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')
        
        <!-- Overlay mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Main Content -->
        <div class="admin-main">
            <!-- Navbar -->
            @include('layouts.partials.navbar')
            
            <!-- Content -->
            <main class="admin-content">
                <!-- Breadcrumb -->
                @hasSection('breadcrumb')
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i></a></li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
                @endif
                
                <!-- Page Header -->
                @hasSection('page-header')
                <div class="page-header mb-4">
                    @yield('page-header')
                </div>
                @endif
                
                <!-- Alerts -->
                @include('components.alerts')
                
                <!-- Main Content -->
                @yield('content')
            </main>
            
            <!-- Footer -->
            <footer class="admin-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</span>
                    <span class="text-muted">Version {{ config('commercialize.version', '1.0.0') }}</span>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (pour AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Admin JS -->
    <script src="{{ asset('js/admin.js') }}"></script>
    
    <!-- CSRF Token for AJAX -->
    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
