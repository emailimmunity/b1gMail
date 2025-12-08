<!DOCTYPE html>
<html lang="{$mf_language_code}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{mf_t key="frontend.signup.title" default="Registrierung"} - {$bm_prefs.pagetitle|default:$_SERVER.HTTP_HOST}</title>
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
        
        .signup-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 480px;
            width: 100%;
            overflow: hidden;
            margin: 20px auto;
        }
        
        .signup-header {
            background: linear-gradient(135deg, #76B82A 0%, #5a9020 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .signup-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .signup-header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .signup-body {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
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
        
        .form-group small {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            color: #666;
        }
        
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            margin-bottom: 24px;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-right: 8px;
            margin-top: 2px;
            width: 18px;
            height: 18px;
            cursor: pointer;
            flex-shrink: 0;
        }
        
        .checkbox-group label {
            font-size: 13px;
            color: #666;
            cursor: pointer;
            line-height: 1.4;
        }
        
        .checkbox-group label a {
            color: #76B82A;
            text-decoration: none;
        }
        
        .checkbox-group label a:hover {
            text-decoration: underline;
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
        
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
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
        
        .alert-info {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #90caf9;
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
        
        .password-strength {
            margin-top: 8px;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s, background 0.3s;
        }
        
        @media (max-width: 480px) {
            .signup-body {
                padding: 30px 20px;
            }
            
            .signup-header {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-header">
            <h1>{mf_t key="frontend.signup.heading" default="Kostenloses Konto erstellen"}</h1>
            <p>{mf_t key="frontend.signup.subheading" default="Starten Sie jetzt mit Ihrem sicheren E-Mail-Postfach"}</p>
        </div>
        
        <div class="signup-body">
            {if isset($signupError)}
                <div class="alert alert-danger">
                    {$signupError}
                </div>
            {/if}
            
            <form method="POST" action="?action=signup" id="signupForm">
                <input type="hidden" name="do" value="signup">
                
                <div class="form-group">
                    <label for="email">{mf_t key="frontend.signup.email_label" default="E-Mail-Adresse *"}</label>
                    <input type="email" id="email" name="email" placeholder="{mf_t key='frontend.signup.email_placeholder' default='wunsch@email.de'}" required autofocus>
                    <small>{mf_t key="frontend.signup.email_help" default="Dies wird Ihre Login-Adresse"}</small>
                </div>
                
                <div class="form-group">
                    <label for="password">{mf_t key="frontend.signup.password_label" default="Passwort *"}</label>
                    <input type="password" id="password" name="password" placeholder="{mf_t key='frontend.signup.password_placeholder' default='Mindestens 8 Zeichen'}" required minlength="8">
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <small>{mf_t key="frontend.signup.password_help" default="Verwenden Sie Buchstaben, Zahlen und Sonderzeichen"}</small>
                </div>
                
                <div class="form-group">
                    <label for="password2">{mf_t key="frontend.signup.password2_label" default="Passwort wiederholen *"}</label>
                    <input type="password" id="password2" name="password2" placeholder="{mf_t key='frontend.signup.password2_placeholder' default='Passwort bestätigen'}" required>
                </div>
                
                <div class="form-group">
                    <label for="firstname">{mf_t key="frontend.signup.firstname_label" default="Vorname (optional)"}</label>
                    <input type="text" id="firstname" name="vorname" placeholder="{mf_t key='frontend.signup.firstname_placeholder' default='Max'}">
                </div>
                
                <div class="form-group">
                    <label for="lastname">{mf_t key="frontend.signup.lastname_label" default="Nachname (optional)"}</label>
                    <input type="text" id="lastname" name="nachname" placeholder="{mf_t key='frontend.signup.lastname_placeholder' default='Mustermann'}">
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="agb" name="agb" required>
                    <label for="agb">
                        {mf_t key="frontend.signup.agb_text" default="Ich habe die"} <a href="?action=tos" target="_blank">{mf_t key="frontend.signup.agb_link" default="AGB"}</a>, 
                        <a href="?action=privacy" target="_blank">{mf_t key="frontend.signup.privacy_link" default="Datenschutzerklärung"}</a> {mf_t key="frontend.signup.and" default="und"} 
                        <a href="?action=imprint" target="_blank">{mf_t key="frontend.signup.imprint_link" default="Impressum"}</a> {mf_t key="frontend.signup.agb_accept" default="gelesen und akzeptiere diese. *"}
                    </label>
                </div>
                
                <button type="submit" class="btn-primary" id="submitBtn">
                    {mf_t key="frontend.signup.submit" default="Kostenloses Konto erstellen"}
                </button>
            </form>
            
            <div class="divider">
                <span>{mf_t key="frontend.signup.or" default="oder"}</span>
            </div>
            
            <div class="links">
                <a href="?action=login">{mf_t key="frontend.signup.login_link" default="Bereits ein Konto? Jetzt anmelden"}</a>
            </div>
            
            <div class="footer-links">
                <a href="?action=imprint">{mf_t key="frontend.signup.imprint" default="Impressum"}</a>
                <a href="?action=tos">{mf_t key="frontend.signup.tos" default="AGB"}</a>
                <a href="?action=privacy">{mf_t key="frontend.signup.privacy" default="Datenschutz"}</a>
                <a href="/">{mf_t key="frontend.signup.back_home" default="Zurück zur Startseite"}</a>
            </div>
        </div>
    </div>
    
    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 25;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 15;
            if (/[^A-Za-z0-9]/.test(password)) strength += 10;
            
            strengthBar.style.width = strength + '%';
            
            if (strength < 40) {
                strengthBar.style.background = '#f44336';
            } else if (strength < 70) {
                strengthBar.style.background = '#ff9800';
            } else {
                strengthBar.style.background = '#4caf50';
            }
        });
        
        // Password match validation
        const form = document.getElementById('signupForm');
        const password2 = document.getElementById('password2');
        const submitBtn = document.getElementById('submitBtn');
        
        form.addEventListener('submit', function(e) {
            if (passwordInput.value !== password2.value) {
                e.preventDefault();
                alert('{mf_t key="frontend.signup.password_mismatch" default="Die Passwörter stimmen nicht überein!"}');
                password2.focus();
                return false;
            }
        });
        
        // Real-time password match indicator
        password2.addEventListener('input', function() {
            if (this.value && passwordInput.value !== this.value) {
                this.style.borderColor = '#f44336';
            } else if (this.value) {
                this.style.borderColor = '#4caf50';
            } else {
                this.style.borderColor = '#e0e0e0';
            }
        });
    </script>
</body>
</html>
