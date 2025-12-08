<!DOCTYPE html>
<html lang="{$mf_language_code}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{mf_t key="frontend.packages.title" default="Pakete"} - {$site_name}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Inter, system-ui, Arial, sans-serif; background:#f7fafc; color:#2d3748; }
        .header { background: linear-gradient(135deg, #76B82A 0%, #5a9020 100%); color:#fff; padding:40px 20px; text-align:center; }
        .container { max-width: 1200px; margin:40px auto; padding:0 20px; }
        .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
        .card { background:#fff; border-radius:12px; padding:24px; box-shadow:0 6px 18px rgba(0,0,0,.08); border:2px solid transparent; transition:.25s; position:relative; }
        .card.highlight { border-color:#76B82A; box-shadow:0 10px 24px rgba(0,0,0,.15); }
        .tag { position:absolute; top:16px; right:16px; background:#2d3748; color:#fff; font-size:11px; padding:4px 8px; border-radius:999px; text-transform:uppercase; letter-spacing:0.04em; }
        .card:hover { transform: translateY(-3px); border-color:#76B82A; }
        .badge { display:inline-block; padding:6px 10px; border-radius:999px; font-size:12px; background:#edf2f7; color:#4a5568; margin-bottom:10px; }
        .price { font-size:24px; font-weight:700; color:#2d3748; margin:10px 0; }
        .features { color:#4a5568; font-size:14px; }
        .btn { display:block; text-align:center; margin-top:16px; background:#76B82A; color:#fff; padding:10px 16px; border-radius:6px; text-decoration:none; }
        .btn:hover { background:#5a9020; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{mf_t key="frontend.packages.heading" default="Pakete"}</h1>
        <p>{mf_t key="frontend.packages.subheading" default="Alle Protokolle und Funktionen in jedem Paket enthalten"}</p>
    </div>
    <div class="container">
        <div class="grid">
            {foreach from=$packages item=p}
                <div class="card{if $p.product_key == 'aikq_q_plus'} highlight{/if}">
                    {if $p.product_key == 'aikq_q_start'}<div class="tag">{mf_t key="frontend.packages.tag_start" default="ab 1 &euro; / Monat"}</div>{/if}
                    {if $p.product_key == 'aikq_q_plus'}<div class="tag">{mf_t key="frontend.packages.tag_recommended" default="Empfohlen"}</div>{/if}
                    <span class="badge">{$p.group_name}</span>
                    <h3>{$p.product_name}</h3>
                    <div class="price">{$p.price}/{mf_t key="frontend.packages.per_month" default="Monat"}</div>
                    {if $p.product_description}
                        <p class="features">{$p.product_description}</p>
                    {/if}
                    <p class="features">
                        {if $p.grant_storage_gb}💾 {mf_t key="frontend.packages.cloud" default="Cloud"}: {$p.grant_storage_gb} GB<br>{/if}
                        {if $p.grant_domains}🌐 {mf_t key="frontend.packages.domains" default="Domains"}: {$p.grant_domains}<br>{/if}
                        {if $p.grant_users}👥 {mf_t key="frontend.packages.users" default="Users"}: {$p.grant_users}<br>{/if}
                        {if $p.grant_tenants}🏢 {mf_t key="frontend.packages.tenants" default="Tenants"}: {$p.grant_tenants}<br>{/if}
                        ✉️ {mf_t key="frontend.packages.protocols" default="Alle Protokolle"}: IMAP, POP3, SMTP, JMAP<br>
                        📅 CalDAV · 👤 CardDAV · 📁 WebDAV / SFTP
                    </p>
                    <a class="btn" href="/index.php?action=paccOrder&id={$p.product_id}">{mf_t key="frontend.packages.order_now" default="Jetzt bestellen"}</a>
                </div>
            {/foreach}
        </div>
    </div>
</body>
</html>
