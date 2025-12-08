<!DOCTYPE html>
<html lang="{$mf_language_code}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{mf_t key="frontend.login.title" default="Login"} - {$bm_prefs.pagetitle|default:$_SERVER.HTTP_HOST}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 420px;
            width: 100%;
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #76B82A 0%, #5a9020 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .login-header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-group input[type="email"],
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #76B82A;
            box-shadow: 0 0 0 3px rgba(118, 184, 42, 0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .checkbox-group label {
            font-size: 14px;
            color: #666;
            cursor: pointer;
        }
        
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #76B82A 0%, #5a9020 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(118, 184, 42, 0.3);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider span {
            background: white;
            padding: 0 15px;
            color: #999;
            font-size: 14px;
            position: relative;
        }
        
        .links {
            text-align: center;
        }
        
        .links a {
            color: #76B82A;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .footer-links {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
        }
        
        .footer-links a {
            color: #666;
            text-decoration: none;
            font-size: 13px;
            margin: 0 10px;
        }
        
        .footer-links a:hover {
            color: #76B82A;
        }
        
        @media (max-width: 480px) {
            .login-body {
                padding: 30px 20px;
            }
            
            .login-header {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>{mf_t key="frontend.login.heading" default="Willkommen zurück!"}</h1>
            <p>{mf_t key="frontend.login.subheading" default="Melden Sie sich in Ihrem Konto an"}</p>
        </div>
        
        <div class="login-body">
            {if isset($loginError)}
                <div class="alert alert-danger">
                    {$loginError}
                </div>
            {/if}
            
            <form method="POST" action="?action=login">
                <input type="hidden" name="do" value="login">
                <input type="hidden" name="timezone" value="{$timezone|default:'0'}">
                
                <div class="form-group">
                    <label for="email">{mf_t key="frontend.login.email_label" default="E-Mail-Adresse"}</label>
                    <input type="email" id="email" name="email_full" placeholder="{mf_t key='frontend.login.email_placeholder' default='ihre@email.de'}" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">{mf_t key="frontend.login.password_label" default="Passwort"}</label>
                    <input type="password" id="password" name="password" placeholder="{mf_t key='frontend.login.password_placeholder' default='••••••••'}" required>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="savelogin" name="savelogin" value="true">
                    <label for="savelogin">{mf_t key="frontend.login.remember_me" default="Angemeldet bleiben"}</label>
                </div>
                
                <button type="submit" class="btn-primary">{mf_t key="frontend.login.submit" default="Anmelden"}</button>
            </form>
            
            <div class="divider">
                <span>{mf_t key="frontend.login.or" default="oder"}</span>
            </div>
            
            <div class="links">
                <a href="?action=signup">{mf_t key="frontend.login.signup_link" default="Noch kein Konto? Jetzt registrieren"}</a>
            </div>
            
            <div class="footer-links">
                <a href="?action=imprint">{mf_t key="frontend.login.imprint" default="Impressum"}</a>
                <a href="?action=tos">{mf_t key="frontend.login.tos" default="AGB"}</a>
                <a href="?action=privacy">{mf_t key="frontend.login.privacy" default="Datenschutz"}</a>
                <a href="/">{mf_t key="frontend.login.back_home" default="Zurück zur Startseite"}</a>
            </div>
        </div>
    </div>
</body>
</html>
