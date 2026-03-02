<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur serveur - CommercialiZe</title>
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
            background: linear-gradient(135deg, #f0f4f8 0%, #d9e2ec 100%);
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
            background: linear-gradient(135deg, #6c757d, #495057);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 0;
        }
        .error-icon {
            font-size: 80px;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        .gear {
            display: inline-block;
            animation: spin 4s linear infinite;
        }
        .gear-reverse {
            display: inline-block;
            animation: spin-reverse 4s linear infinite;
            margin-left: -15px;
            font-size: 60px;
            vertical-align: middle;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes spin-reverse {
            from { transform: rotate(360deg); }
            to { transform: rotate(0deg); }
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
        .btn-refresh {
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
        .btn-refresh:hover {
            background: var(--secondary);
            color: white;
        }
        .info-box {
            margin-top: 2rem;
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            text-align: left;
        }
        .info-box h6 {
            color: #333;
            margin-bottom: 1rem;
        }
        .info-box ul {
            color: #666;
            margin-bottom: 0;
        }
        .info-box li {
            margin-bottom: 0.5rem;
        }
        .status-box {
            margin-top: 1.5rem;
            background: #e8f5e9;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            border: 1px solid #a5d6a7;
        }
        .status-box.checking {
            background: #fff8e1;
            border-color: #ffd54f;
        }
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #4caf50;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        .status-indicator.checking {
            background: #ff9800;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .support-link {
            margin-top: 1.5rem;
            font-size: 0.95rem;
        }
        .support-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        .support-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <span class="gear"><i class="fas fa-cog"></i></span>
            <span class="gear-reverse"><i class="fas fa-cog"></i></span>
        </div>
        <h1 class="error-code">500</h1>
        <h2 class="error-title">Erreur interne du serveur</h2>
        <p class="error-message">
            Oups ! Quelque chose s'est mal passé de notre côté. Nos équipes ont été notifiées et travaillent à résoudre le problème.
        </p>
        
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ url('/') }}" class="btn-home">
                <i class="fas fa-home"></i> Retour à l'accueil
            </a>
            <a href="javascript:location.reload()" class="btn-refresh">
                <i class="fas fa-sync-alt"></i> Réessayer
            </a>
        </div>
        
        <div class="info-box">
            <h6><i class="fas fa-tools me-2 text-muted"></i>Que pouvez-vous faire ?</h6>
            <ul>
                <li>Rafraîchir la page dans quelques instants</li>
                <li>Vérifier votre connexion internet</li>
                <li>Vider le cache de votre navigateur</li>
                <li>Réessayer plus tard si le problème persiste</li>
            </ul>
        </div>
        
        <div class="status-box checking" id="statusBox">
            <span class="status-indicator checking" id="statusDot"></span>
            <span id="statusText">Vérification du statut du serveur...</span>
        </div>
        
        <div class="support-link">
            <p class="text-muted mb-1">Le problème persiste ?</p>
            <a href="mailto:support@commercialize.ga">
                <i class="fas fa-envelope me-1"></i>Contacter le support
            </a>
        </div>
    </div>
    
    <script>
        // Simulated status check
        setTimeout(function() {
            document.getElementById('statusBox').classList.remove('checking');
            document.getElementById('statusDot').classList.remove('checking');
            document.getElementById('statusText').textContent = 'Serveur opérationnel - Veuillez réessayer';
        }, 3000);
    </script>
</body>
</html>
