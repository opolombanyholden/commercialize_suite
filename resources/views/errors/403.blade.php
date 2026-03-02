<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès refusé - CommercialiZe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #FF6B35;
            --secondary: #004E89;
            --danger: #dc3545;
        }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%);
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
            background: linear-gradient(135deg, var(--danger), #ff6b6b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 0;
        }
        .error-icon {
            font-size: 80px;
            color: var(--danger);
            margin-bottom: 1rem;
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
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
        .permission-box {
            margin-top: 2rem;
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            border-left: 4px solid var(--danger);
        }
        .permission-box h6 {
            color: var(--danger);
            margin-bottom: 0.5rem;
        }
        .permission-box p {
            color: #666;
            margin-bottom: 0;
            font-size: 0.95rem;
        }
        .upgrade-box {
            margin-top: 1.5rem;
            background: linear-gradient(135deg, #fff8e1, #ffecb3);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid #ffd54f;
        }
        .upgrade-box h6 {
            color: #f57c00;
            margin-bottom: 0.5rem;
        }
        .upgrade-box a {
            color: var(--primary);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-lock"></i>
        </div>
        <h1 class="error-code">403</h1>
        <h2 class="error-title">Accès refusé</h2>
        <p class="error-message">
            Vous n'avez pas les permissions nécessaires pour accéder à cette page ou effectuer cette action.
        </p>
        
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ url('/') }}" class="btn-home">
                <i class="fas fa-home"></i> Retour à l'accueil
            </a>
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-arrow-left"></i> Page précédente
            </a>
        </div>
        
        <div class="permission-box">
            <h6><i class="fas fa-shield-alt me-2"></i>Pourquoi je vois cette page ?</h6>
            <p>
                Votre rôle actuel ne dispose pas des permissions requises pour cette ressource.
                Contactez votre administrateur si vous pensez qu'il s'agit d'une erreur.
            </p>
        </div>
        
        @if(auth()->check() && !auth()->user()->hasVersionOrHigher('pro'))
        <div class="upgrade-box">
            <h6><i class="fas fa-star me-2"></i>Fonctionnalité Premium</h6>
            <p class="mb-2">Cette fonctionnalité peut nécessiter une version supérieure.</p>
            <a href="{{ route('pricing.plans') }}">Voir les offres <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        @endif
    </div>
</body>
</html>
