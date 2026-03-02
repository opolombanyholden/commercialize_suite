<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page non trouvée - CommercialiZe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #FF6B35;
            --secondary: #004E89;
        }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
        }
        .error-code {
            font-size: 120px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 0;
        }
        .error-icon {
            font-size: 80px;
            color: var(--primary);
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .error-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }
        .error-message {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        .btn-home {
            background: linear-gradient(135deg, var(--primary), #ff8c5a);
            border: none;
            padding: 12px 30px;
            font-size: 1rem;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 107, 53, 0.4);
            color: white;
        }
        .btn-back {
            background: transparent;
            border: 2px solid var(--secondary);
            padding: 10px 25px;
            font-size: 1rem;
            border-radius: 50px;
            color: var(--secondary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-back:hover {
            background: var(--secondary);
            color: white;
        }
        .search-box {
            margin-top: 2rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        .suggestions {
            margin-top: 2rem;
            text-align: left;
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        .suggestions h6 {
            color: #333;
            margin-bottom: 1rem;
        }
        .suggestions a {
            color: var(--secondary);
            text-decoration: none;
        }
        .suggestions a:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-search"></i>
        </div>
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Page non trouvée</h2>
        <p class="error-message">
            Oups ! La page que vous recherchez semble avoir été déplacée, supprimée ou n'existe pas.
        </p>
        
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ url('/') }}" class="btn-home">
                <i class="fas fa-home"></i> Retour à l'accueil
            </a>
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-arrow-left"></i> Page précédente
            </a>
        </div>
        
        <div class="suggestions">
            <h6><i class="fas fa-lightbulb me-2 text-warning"></i>Pages populaires</h6>
            <ul class="list-unstyled mb-0">
                <li class="mb-2"><a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>Tableau de bord</a></li>
                <li class="mb-2"><a href="{{ route('invoices.index') }}"><i class="fas fa-file-invoice me-2"></i>Factures</a></li>
                <li class="mb-2"><a href="{{ route('clients.index') }}"><i class="fas fa-users me-2"></i>Clients</a></li>
                <li><a href="{{ route('products.index') }}"><i class="fas fa-box me-2"></i>Produits</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
