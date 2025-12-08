<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Status - {$site_name}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
        }
        .header {
            background: linear-gradient(135deg, {if $all_ok}#76B82A{else}#ff5722{/if} 0%, {if $all_ok}#5a9020{else}#d32f2f{/if} 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 { font-size: 32px; font-weight: 600; margin-bottom: 10px; }
        .header p { font-size: 18px; opacity: 0.9; }
        .header a {
            color: white;
            text-decoration: none;
            margin-top: 15px;
            display: inline-block;
            opacity: 0.9;
        }
        .header a:hover { opacity: 1; text-decoration: underline; }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .status-overview {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .status-icon {
            font-size: 64px;
            margin-bottom: 15px;
        }
        .status-text {
            font-size: 24px;
            font-weight: 600;
            color: {if $all_ok}#76B82A{else}#ff5722{/if};
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .status-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .status-card-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-badge.ok {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .status-badge.error {
            background: #ffebee;
            color: #c62828;
        }
        .status-icon-large {
            font-size: 48px;
            text-align: center;
            margin: 20px 0;
        }
        .status-description {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .info-box h3 {
            color: #1976d2;
            margin-bottom: 10px;
            font-size: 18px;
        }
        .info-box p {
            color: #555;
            font-size: 14px;
            line-height: 1.6;
        }
        .back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 24px;
            background: #76B82A;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
            text-align: center;
        }
        .back-link:hover { background: #5a9020; }
        @media (max-width: 768px) {
            .status-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{if $all_ok}✓{else}⚠{/if} System Status</h1>
        <p>{if $all_ok}Alle Systeme betriebsbereit{else}Einige Dienste sind beeinträchtigt{/if}</p>
        <a href="/">← Zurück zur Startseite</a>
    </div>
    
    <div class="container">
        <div class="status-overview">
            <div class="status-icon">{if $all_ok}✓{else}⚠{/if}</div>
            <div class="status-text">
                {if $all_ok}
                    Alle Systeme funktionieren einwandfrei
                {else}
                    Einige Dienste sind derzeit nicht verfügbar
                {/if}
            </div>
        </div>
        
        <div class="status-grid">
            <!-- Web Service -->
            <div class="status-card">
                <div class="status-card-header">
                    <div class="status-card-title">🌐 Webserver</div>
                    <span class="status-badge {if $status.web}ok{else}error{/if}">
                        {if $status.web}Online{else}Offline{/if}
                    </span>
                </div>
                <div class="status-icon-large">{if $status.web}✓{else}✗{/if}</div>
                <div class="status-description">
                    {if $status.web}
                        Webserver ist erreichbar und funktioniert normal.
                    {else}
                        Webserver antwortet nicht oder ist nicht verfügbar.
                    {/if}
                </div>
            </div>
            
            <!-- Database -->
            <div class="status-card">
                <div class="status-card-header">
                    <div class="status-card-title">💾 Datenbank</div>
                    <span class="status-badge {if $status.database}ok{else}error{/if}">
                        {if $status.database}Online{else}Offline{/if}
                    </span>
                </div>
                <div class="status-icon-large">{if $status.database}✓{else}✗{/if}</div>
                <div class="status-description">
                    {if $status.database}
                        Datenbankverbindung ist aktiv und funktioniert.
                    {else}
                        Datenbankverbindung konnte nicht hergestellt werden.
                    {/if}
                </div>
            </div>
            
            <!-- Mail Service -->
            <div class="status-card">
                <div class="status-card-header">
                    <div class="status-card-title">📧 E-Mail</div>
                    <span class="status-badge {if $status.mail}ok{else}error{/if}">
                        {if $status.mail}Online{else}Offline{/if}
                    </span>
                </div>
                <div class="status-icon-large">{if $status.mail}✓{else}✗{/if}</div>
                <div class="status-description">
                    {if $status.mail}
                        E-Mail-Dienst ist konfiguriert und bereit.
                    {else}
                        E-Mail-Dienst ist nicht konfiguriert.
                    {/if}
                </div>
            </div>
            
            <!-- Storage -->
            <div class="status-card">
                <div class="status-card-header">
                    <div class="status-card-title">💿 Speicher</div>
                    <span class="status-badge {if $status.storage}ok{else}error{/if}">
                        {if $status.storage}Online{else}Offline{/if}
                    </span>
                </div>
                <div class="status-icon-large">{if $status.storage}✓{else}✗{/if}</div>
                <div class="status-description">
                    {if $status.storage}
                        Speicher ist verfügbar und beschreibbar.
                    {else}
                        Speicher ist nicht verfügbar oder schreibgeschützt.
                    {/if}
                </div>
            </div>
        </div>
        
        <div class="info-box">
            <h3>ℹ️ Über diese Seite</h3>
            <p>
                Diese Seite zeigt den aktuellen Status aller wichtigen Systemkomponenten. 
                Bei Problemen werden Sie hier informiert. Die Statusanzeige wird automatisch aktualisiert.
            </p>
        </div>
        
        <div style="text-align: center;">
            <a href="/" class="back-link">← Zurück zur Startseite</a>
        </div>
    </div>
</body>
</html>
