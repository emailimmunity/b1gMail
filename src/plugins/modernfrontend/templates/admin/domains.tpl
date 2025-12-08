{include file='_navigation.tpl' currentPage='domains' pageTitle='Domain Management'}

<style>
.mf-domains {
    padding: 20px;
    background: #f5f5f5;
}
.mf-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}
.mf-btn {
    display: inline-block;
    padding: 10px 20px;
    background: #76B82A;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    border: none;
    cursor: pointer;
}
.mf-btn:hover {
    background: #5D9321;
}
.mf-btn-danger {
    background: #dc3545;
}
.mf-btn-danger:hover {
    background: #c82333;
}
.mf-btn-secondary {
    background: #6c757d;
}
.mf-btn-secondary:hover {
    background: #5a6268;
}
.mf-table {
    width: 100%;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.mf-table table {
    width: 100%;
    border-collapse: collapse;
}
.mf-table th {
    background: #333;
    color: white;
    padding: 15px;
    text-align: left;
}
.mf-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
}
.mf-table tr:hover {
    background: #f8f9fa;
}
.mf-form {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-width: 600px;
}
.mf-form-group {
    margin-bottom: 20px;
}
.mf-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}
.mf-form-group input,
.mf-form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.mf-form-group input[type="checkbox"] {
    width: auto;
    margin-right: 10px;
}
.mf-status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}
.mf-status-active {
    background: #d4edda;
    color: #155724;
}
.mf-status-inactive {
    background: #f8d7da;
    color: #721c24;
}
.mf-status-maintenance {
    background: #fff3cd;
    color: #856404;
}
.mf-message {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}
.mf-message-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.mf-message-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.mf-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.mf-stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}
.mf-stat-card .value {
    font-size: 36px;
    font-weight: bold;
    color: #76B82A;
}
.mf-stat-card .label {
    font-size: 14px;
    color: #666;
    margin-top: 5px;
}
</style>

<div class="mf-domains">
    <div class="mf-header">
        <h1>🌐 Domain-Verwaltung</h1>
        {if $action == 'list'}
            <a href="?plugin=ModernFrontendPlugin&page=domains&do=create" class="mf-btn">+ Neue Domain</a>
        {else}
            <a href="?plugin=ModernFrontendPlugin&page=domains" class="mf-btn-secondary mf-btn">← Zurück zur Liste</a>
        {/if}
    </div>
    
    {if $message}
        <div class="mf-message mf-message-{$messageType}">
            {$message}
        </div>
    {/if}
    
    {if $action == 'list'}
        <!-- Statistik -->
        <div class="mf-stats">
            <div class="mf-stat-card">
                <div class="value">{$domainsCount}</div>
                <div class="label">Domains gesamt</div>
            </div>
        </div>
        
        <!-- Domain-Liste -->
        <div class="mf-table">
            <table>
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Design</th>
                        <th>Sprache</th>
                        <th>Status</th>
                        <th>SSL</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    {if $domains && count($domains) > 0}
                        {foreach from=$domains item=domain}
                            <tr>
                                <td><strong>{$domain.domain}</strong></td>
                                <td>{$domain.design_name|default:'-'}</td>
                                <td>{$domain.language_name|default:'-'} ({$domain.language_code|default:'-'})</td>
                                <td>
                                    <span class="mf-status-badge mf-status-{$domain.status}">
                                        {$domain.status}
                                    </span>
                                </td>
                                <td>
                                    {if $domain.ssl_enabled == 1}
                                        ✅ Ja
                                    {else}
                                        ❌ Nein
                                    {/if}
                                </td>
                                <td>
                                    <a href="?plugin=ModernFrontendPlugin&page=domains&do=edit&id={$domain.id}" class="mf-btn" style="padding:5px 10px;font-size:12px;">Bearbeiten</a>
                                    <a href="?plugin=ModernFrontendPlugin&page=domains&do=delete&id={$domain.id}" class="mf-btn mf-btn-danger" style="padding:5px 10px;font-size:12px;" onclick="return confirm('Domain wirklich löschen?');">Löschen</a>
                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        <tr>
                            <td colspan="6" style="text-align:center;padding:30px;color:#999;">
                                Keine Domains vorhanden. Erstellen Sie die erste Domain!
                            </td>
                        </tr>
                    {/if}
                </tbody>
            </table>
        </div>
    {elseif $action == 'create' || $action == 'edit'}
        <!-- Formular -->
        <div class="mf-form">
            <h2>{if $action == 'create'}Neue Domain erstellen{else}Domain bearbeiten{/if}</h2>
            
            <form method="POST" action="?plugin=ModernFrontendPlugin&page=domains&do={$action}">
                {if $action == 'edit'}
                    <input type="hidden" name="id" value="{$editDomain.id}">
                {/if}
                
                <div class="mf-form-group">
                    <label>Domain / Hostname *</label>
                    <input type="text" name="domain" value="{$editDomain.domain|default:''}" required placeholder="z.B. mail.example.com">
                </div>
                
                <div class="mf-form-group">
                    <label>Design</label>
                    <select name="design_id">
                        <option value="">- Kein Design -</option>
                        {foreach from=$designs item=design}
                            <option value="{$design.id}" {if $editDomain.design_id == $design.id}selected{/if}>
                                {$design.name}
                            </option>
                        {/foreach}
                    </select>
                </div>
                
                <div class="mf-form-group">
                    <label>Standard-Sprache *</label>
                    <select name="default_language_id" required>
                        {foreach from=$languages item=language}
                            <option value="{$language.id}" {if $editDomain.default_language_id == $language.id || (!$editDomain && $language.id == 1)}selected{/if}>
                                {$language.native_name} ({$language.name})
                            </option>
                        {/foreach}
                    </select>
                </div>
                
                <div class="mf-form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active" {if $editDomain.status == 'active' || !$editDomain}selected{/if}>Aktiv</option>
                        <option value="inactive" {if $editDomain.status == 'inactive'}selected{/if}>Inaktiv</option>
                        <option value="maintenance" {if $editDomain.status == 'maintenance'}selected{/if}>Wartung</option>
                    </select>
                </div>
                
                <div class="mf-form-group">
                    <label>
                        <input type="checkbox" name="ssl_enabled" {if $editDomain.ssl_enabled == 1 || !$editDomain}checked{/if}>
                        SSL aktiviert
                    </label>
                </div>
                
                <div class="mf-form-group">
                    <button type="submit" name="submit" class="mf-btn">
                        {if $action == 'create'}Domain erstellen{else}Änderungen speichern{/if}
                    </button>
                    <a href="?plugin=ModernFrontendPlugin&page=domains" class="mf-btn mf-btn-secondary">Abbrechen</a>
                </div>
            </form>
        </div>
    {/if}
</div>
