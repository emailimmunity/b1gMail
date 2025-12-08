<style>
.mf-translations {
    padding: 20px;
    background: #f5f5f5;
}
.mf-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
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
.mf-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #ddd;
}
.mf-tab {
    padding: 10px 20px;
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 14px;
    color: #666;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s;
    text-decoration: none;
}
.mf-tab:hover {
    color: #76B82A;
}
.mf-tab.active {
    color: #76B82A;
    border-bottom-color: #76B82A;
}
.mf-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.mf-form-group {
    margin-bottom: 15px;
}
.mf-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}
.mf-form-group input,
.mf-form-group select,
.mf-form-group textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.mf-form-group textarea {
    min-height: 100px;
    resize: vertical;
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
    font-size: 14px;
}
.mf-btn:hover {
    background: #5D9321;
}
.mf-btn-secondary {
    background: #6c757d;
}
.mf-btn-secondary:hover {
    background: #5a6268;
}
.mf-btn-danger {
    background: #dc3545;
}
.mf-btn-danger:hover {
    background: #c82333;
}
.mf-btn-small {
    padding: 5px 10px;
    font-size: 12px;
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
.mf-table {
    width: 100%;
    border-collapse: collapse;
}
.mf-table th,
.mf-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
.mf-table th {
    background: #f8f9fa;
    font-weight: bold;
}
.mf-table tr:hover {
    background: #f8f9fa;
}
.mf-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: bold;
}
.mf-badge-active {
    background: #d4edda;
    color: #155724;
}
.mf-badge-inactive {
    background: #f8d7da;
    color: #721c24;
}
.mf-badge-google {
    background: #e3f2fd;
    color: #1976d2;
}
.mf-badge-deepl {
    background: #f3e5f5;
    color: #7b1fa2;
}
.mf-api-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.mf-api-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}
.mf-api-title {
    font-size: 18px;
    font-weight: bold;
}
.mf-status-ok {
    color: #28a745;
    font-weight: bold;
}
.mf-status-error {
    color: #dc3545;
    font-weight: bold;
}
.mf-test-result {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    border-left: 4px solid #76B82A;
    margin-top: 20px;
}
.mf-test-result h4 {
    margin-top: 0;
}
</style>

<div class="mf-translations">
    <div class="mf-header">
        <h1>🌐 Übersetzungs-Verwaltung</h1>
    </div>
    
    {if $message}
        <div class="mf-message mf-message-{$messageType}">
            {$message}
        </div>
    {/if}
    
    <!-- Tabs -->
    <div class="mf-tabs">
        <a href="?plugin=ModernFrontendPlugin&page=translations&do=api" class="mf-tab {if $action == 'api'}active{/if}">🔑 API Konfiguration</a>
        <a href="?plugin=ModernFrontendPlugin&page=translations&do=list" class="mf-tab {if $action == 'list'}active{/if}">📝 Übersetzungen</a>
        <a href="?plugin=ModernFrontendPlugin&page=translations&do=test" class="mf-tab {if $action == 'test'}active{/if}">🧪 Testen</a>
    </div>
    
    {if $action == 'api'}
        <!-- API Konfiguration -->
        <div class="mf-content">
            <h2>API Konfiguration</h2>
            <p>Konfiguriere Google Translate und DeepL API-Keys für automatische Übersetzungen.</p>
            
            {foreach from=$providers item=provider}
                <div class="mf-api-card">
                    <div class="mf-api-header">
                        <div class="mf-api-title">
                            {if $provider.provider == 'google_translate'}
                                🔵 Google Translate API
                            {elseif $provider.provider == 'deepl'}
                                🟣 DeepL API
                            {else}
                                {$provider.provider}
                            {/if}
                            {if $provider.is_active == 1}
                                <span class="mf-badge mf-badge-active">Aktiv</span>
                            {else}
                                <span class="mf-badge mf-badge-inactive">Inaktiv</span>
                            {/if}
                        </div>
                        {if $provider.api_key}
                            <a href="?plugin=ModernFrontendPlugin&page=translations&do=toggle_api&provider={$provider.provider}" class="mf-btn mf-btn-small">
                                {if $provider.is_active == 1}Deaktivieren{else}Aktivieren{/if}
                            </a>
                        {/if}
                    </div>
                    
                    {if $statusChecks[$provider.provider]}
                        <div class="mf-status-{$statusChecks[$provider.provider].status}">
                            Status: {$statusChecks[$provider.provider].message}
                        </div>
                        {if $statusChecks[$provider.provider].usage}
                            <div style="margin-top:10px; color:#666;">
                                Verbrauch: {$statusChecks[$provider.provider].usage.characters_used|number_format:0:",":"."} / {$statusChecks[$provider.provider].usage.characters_limit|number_format:0:",":"."} Zeichen ({$statusChecks[$provider.provider].usage.percentage}%)
                            </div>
                        {/if}
                    {/if}
                    
                    <form method="POST" action="?plugin=ModernFrontendPlugin&page=translations&do=save_api" style="margin-top:15px;">
                        <input type="hidden" name="provider" value="{$provider.provider}">
                        
                        <div class="mf-form-group">
                            <label>API Key:</label>
                            <input type="text" name="api_key" value="{$provider.api_key|default:''}" placeholder="API Key eingeben" required>
                        </div>
                        
                        {if $provider.provider == 'deepl'}
                            <div class="mf-form-group">
                                <label>
                                    <input type="checkbox" name="is_pro" value="1" {if $provider.settings.is_pro}checked{/if}> Pro Account
                                </label>
                            </div>
                        {/if}
                        
                        <button type="submit" name="submit" class="mf-btn">Speichern</button>
                    </form>
                </div>
            {/foreach}
            
            <div style="margin-top:30px; padding:15px; background:#fff3cd; border-radius:4px;">
                <strong>💡 Hinweise:</strong>
                <ul style="margin:10px 0 0 20px;">
                    <li><strong>Google Translate:</strong> <a href="https://cloud.google.com/translate" target="_blank">API Key erstellen</a></li>
                    <li><strong>DeepL:</strong> <a href="https://www.deepl.com/pro-api" target="_blank">API Key erstellen</a></li>
                    <li>Beide APIs bieten kostenlose Kontingente für den Einstieg</li>
                </ul>
            </div>
        </div>
        
    {elseif $action == 'list'}
        <!-- Übersetzungen Liste -->
        <div class="mf-content">
            {if $statistics}
                <div class="mf-stats">
                    <div class="mf-stat-card">
                        <div class="value">{$statistics.total|number_format:0:",":"."}</div>
                        <div class="label">Übersetzungen</div>
                    </div>
                    {foreach from=$statistics.by_provider key=provider item=count}
                        <div class="mf-stat-card">
                            <div class="value">{$count|number_format:0:",":"."}</div>
                            <div class="label">{$provider}</div>
                        </div>
                    {/foreach}
                </div>
            {/if}
            
            <!-- Filter & Cache -->
            <div style="display:flex; gap:20px; margin-bottom:20px;">
                <form method="GET" style="flex:1;">
                    <input type="hidden" name="plugin" value="ModernFrontendPlugin">
                    <input type="hidden" name="page" value="translations">
                    <input type="hidden" name="do" value="list">
                    <input type="text" name="search" placeholder="Suchen..." value="{$filters.search|default:''}" style="padding:8px; border:1px solid #ddd; border-radius:4px; width:100%;">
                </form>
                
                <form method="POST" action="?plugin=ModernFrontendPlugin&page=translations&do=clear_cache">
                    <button type="submit" name="clear_submit" class="mf-btn mf-btn-danger" onclick="return confirm('Cache wirklich löschen?')">🗑️ Cache leeren</button>
                </form>
            </div>
            
            {if $translations|@count > 0}
                <table class="mf-table">
                    <thead>
                        <tr>
                            <th>Original</th>
                            <th>Übersetzung</th>
                            <th>Sprachen</th>
                            <th>API</th>
                            <th>Datum</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$translations item=trans}
                            <tr>
                                <td>{$trans.original_text|truncate:50}</td>
                                <td>{$trans.translated_text|truncate:50}</td>
                                <td>{$trans.source_language} → {$trans.target_language}</td>
                                <td>
                                    {if $trans.api_provider == 'google_translate'}
                                        <span class="mf-badge mf-badge-google">Google</span>
                                    {elseif $trans.api_provider == 'deepl'}
                                        <span class="mf-badge mf-badge-deepl">DeepL</span>
                                    {else}
                                        {$trans.api_provider}
                                    {/if}
                                </td>
                                <td>{$trans.updated_at|date_format:"%d.%m.%Y %H:%M"}</td>
                                <td>
                                    <a href="?plugin=ModernFrontendPlugin&page=translations&do=edit&id={$trans.id}" class="mf-btn mf-btn-small">Bearbeiten</a>
                                    <a href="?plugin=ModernFrontendPlugin&page=translations&do=delete&id={$trans.id}" class="mf-btn mf-btn-danger mf-btn-small" onclick="return confirm('Löschen?')">Löschen</a>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
                
                <!-- Pagination -->
                {if $totalCount > $limit}
                    <div style="margin-top:20px; text-align:center;">
                        {if $offset > 0}
                            <a href="?plugin=ModernFrontendPlugin&page=translations&do=list&offset={$offset-$limit}" class="mf-btn mf-btn-secondary">← Zurück</a>
                        {/if}
                        {if $offset + $limit < $totalCount}
                            <a href="?plugin=ModernFrontendPlugin&page=translations&do=list&offset={$offset+$limit}" class="mf-btn mf-btn-secondary">Weiter →</a>
                        {/if}
                    </div>
                {/if}
            {else}
                <p>Noch keine Übersetzungen vorhanden.</p>
            {/if}
        </div>
        
    {elseif $action == 'test'}
        <!-- Übersetzung testen -->
        <div class="mf-content">
            <h2>🧪 Übersetzung testen</h2>
            
            <form method="POST" action="?plugin=ModernFrontendPlugin&page=translations&do=test">
                <div class="mf-form-group">
                    <label>Text:</label>
                    <textarea name="text" required placeholder="Text eingeben...">{$testResult.original|default:''}</textarea>
                </div>
                
                <div class="mf-form-group">
                    <label>Von Sprache:</label>
                    <select name="source_lang">
                        <option value="auto">Automatisch erkennen</option>
                        {foreach from=$languages item=lang}
                            <option value="{$lang.code}">{$lang.name} ({$lang.code})</option>
                        {/foreach}
                    </select>
                </div>
                
                <div class="mf-form-group">
                    <label>Nach Sprache:</label>
                    <select name="target_lang" required>
                        {foreach from=$languages item=lang}
                            <option value="{$lang.code}">{$lang.name} ({$lang.code})</option>
                        {/foreach}
                    </select>
                </div>
                
                <div class="mf-form-group">
                    <label>API Provider:</label>
                    <select name="provider">
                        {foreach from=$providers item=prov}
                            {if $prov.is_active == 1}
                                <option value="{$prov.provider}">
                                    {if $prov.provider == 'google_translate'}Google Translate{elseif $prov.provider == 'deepl'}DeepL{else}{$prov.provider}{/if}
                                </option>
                            {/if}
                        {/foreach}
                    </select>
                </div>
                
                <button type="submit" name="test_submit" class="mf-btn">Übersetzen</button>
            </form>
            
            {if $testResult}
                <div class="mf-test-result">
                    <h4>✅ Ergebnis:</h4>
                    <p><strong>Original ({$testResult.source_lang}):</strong><br>{$testResult.original}</p>
                    <p><strong>Übersetzung ({$testResult.target_lang}):</strong><br>{$testResult.translated}</p>
                    <p><strong>API:</strong> {$testResult.provider}</p>
                </div>
            {/if}
        </div>
        
    {elseif $action == 'edit'}
        <!-- Übersetzung bearbeiten -->
        <div class="mf-content">
            <h2>Übersetzung bearbeiten</h2>
            
            <form method="POST" action="?plugin=ModernFrontendPlugin&page=translations&do=update">
                <input type="hidden" name="id" value="{$translation.id}">
                
                <div class="mf-form-group">
                    <label>Original ({$translation.source_language}):</label>
                    <textarea readonly>{$translation.original_text}</textarea>
                </div>
                
                <div class="mf-form-group">
                    <label>Übersetzung ({$translation.target_language}):</label>
                    <textarea name="translated_text" required>{$translation.translated_text}</textarea>
                </div>
                
                <button type="submit" name="update_submit" class="mf-btn">Speichern</button>
                <a href="?plugin=ModernFrontendPlugin&page=translations&do=list" class="mf-btn mf-btn-secondary">Abbrechen</a>
            </form>
        </div>
    {/if}
</div>
