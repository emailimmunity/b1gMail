<style>
.mf-languages {
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
}
.mf-tab:hover {
    color: #76B82A;
}
.mf-tab.active {
    color: #76B82A;
    border-bottom-color: #76B82A;
}
.mf-tab-content {
    display: none;
}
.mf-tab-content.active {
    display: block;
}
.mf-language-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}
.mf-language-card {
    background: white;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: transform 0.2s;
}
.mf-language-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.mf-language-info {
    flex: 1;
}
.mf-language-name {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 5px;
}
.mf-language-code {
    font-size: 12px;
    color: #999;
    text-transform: uppercase;
}
.mf-language-flag {
    font-size: 32px;
    margin-right: 10px;
}
.mf-language-actions {
    display: flex;
    gap: 5px;
}
.mf-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
    margin-top: 5px;
}
.mf-badge-active {
    background: #d4edda;
    color: #155724;
}
.mf-badge-inactive {
    background: #f8d7da;
    color: #721c24;
}
.mf-badge-usage {
    background: #cce5ff;
    color: #004085;
}
.mf-btn {
    display: inline-block;
    padding: 6px 12px;
    background: #76B82A;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 12px;
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
.mf-btn-small {
    padding: 4px 8px;
    font-size: 11px;
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
.mf-region-section {
    margin-bottom: 30px;
}
.mf-region-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
    color: #333;
    padding-bottom: 10px;
    border-bottom: 2px solid #76B82A;
}
.mf-top-languages {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.mf-top-languages h3 {
    margin-top: 0;
    margin-bottom: 15px;
}
.mf-top-language-item {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #eee;
}
.mf-top-language-item:last-child {
    border-bottom: none;
}
.mf-top-language-rank {
    font-size: 20px;
    font-weight: bold;
    color: #76B82A;
    width: 40px;
}
.mf-top-language-details {
    flex: 1;
}
.mf-top-language-count {
    font-size: 14px;
    color: #666;
}
.mf-form {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-width: 500px;
}
.mf-form-group {
    margin-bottom: 15px;
}
.mf-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}
.mf-form-group input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>

<div class="mf-languages">
    <div class="mf-header">
        <h1>🌍 Sprach-Verwaltung</h1>
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
                <div class="value">{$totalCount}</div>
                <div class="label">Sprachen gesamt</div>
            </div>
            <div class="mf-stat-card">
                <div class="value">{$activeCount}</div>
                <div class="label">Aktive Sprachen</div>
            </div>
            <div class="mf-stat-card">
                <div class="value">{$mostUsed|@count}</div>
                <div class="label">In Verwendung</div>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="mf-tabs">
            <button class="mf-tab active" onclick="switchTab('all')">Alle Sprachen</button>
            <button class="mf-tab" onclick="switchTab('regions')">Nach Region</button>
            <button class="mf-tab" onclick="switchTab('popular')">Meistgenutzt</button>
        </div>
        
        <!-- Tab: Alle Sprachen -->
        <div id="tab-all" class="mf-tab-content active">
            <div class="mf-language-grid">
                {foreach from=$languages item=lang}
                    <div class="mf-language-card">
                        {if $lang.flag_icon}
                            <span class="mf-language-flag">{$lang.flag_icon}</span>
                        {/if}
                        <div class="mf-language-info">
                            <div class="mf-language-name">{$lang.name}</div>
                            <div class="mf-language-code">{$lang.code}</div>
                            {if $lang.is_active == 1}
                                <span class="mf-badge mf-badge-active">Aktiv</span>
                            {else}
                                <span class="mf-badge mf-badge-inactive">Inaktiv</span>
                            {/if}
                            {if $lang.usage_count > 0}
                                <span class="mf-badge mf-badge-usage">{$lang.usage_count}x</span>
                            {/if}
                        </div>
                        <div class="mf-language-actions">
                            <a href="?plugin=ModernFrontendPlugin&page=languages&do=toggle&id={$lang.id}" class="mf-btn mf-btn-small">
                                {if $lang.is_active == 1}Deaktivieren{else}Aktivieren{/if}
                            </a>
                            <a href="?plugin=ModernFrontendPlugin&page=languages&do=edit&id={$lang.id}" class="mf-btn mf-btn-secondary mf-btn-small">Bearbeiten</a>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
        
        <!-- Tab: Nach Region -->
        <div id="tab-regions" class="mf-tab-content">
            {foreach from=$languagesByRegion key=region item=langs}
                {if $langs|@count > 0}
                    <div class="mf-region-section">
                        <div class="mf-region-title">{$region} ({$langs|@count})</div>
                        <div class="mf-language-grid">
                            {foreach from=$langs item=lang}
                                <div class="mf-language-card">
                                    {if $lang.flag_icon}
                                        <span class="mf-language-flag">{$lang.flag_icon}</span>
                                    {/if}
                                    <div class="mf-language-info">
                                        <div class="mf-language-name">{$lang.name}</div>
                                        <div class="mf-language-code">{$lang.code}</div>
                                        {if $lang.is_active == 1}
                                            <span class="mf-badge mf-badge-active">Aktiv</span>
                                        {else}
                                            <span class="mf-badge mf-badge-inactive">Inaktiv</span>
                                        {/if}
                                    </div>
                                    <div class="mf-language-actions">
                                        <a href="?plugin=ModernFrontendPlugin&page=languages&do=toggle&id={$lang.id}" class="mf-btn mf-btn-small">
                                            {if $lang.is_active == 1}Deaktivieren{else}Aktivieren{/if}
                                        </a>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/if}
            {/foreach}
        </div>
        
        <!-- Tab: Meistgenutzt -->
        <div id="tab-popular" class="mf-tab-content">
            <div class="mf-top-languages">
                <h3>🏆 Top 10 Meistgenutzte Sprachen</h3>
                {if $mostUsed|@count > 0}
                    {foreach from=$mostUsed item=lang name=toploop}
                        <div class="mf-top-language-item">
                            <div class="mf-top-language-rank">#{$smarty.foreach.toploop.iteration}</div>
                            {if $lang.flag_icon}
                                <span class="mf-language-flag">{$lang.flag_icon}</span>
                            {/if}
                            <div class="mf-top-language-details">
                                <div class="mf-language-name">{$lang.name}</div>
                                <div class="mf-top-language-count">Verwendet auf {$lang.usage_count} Domain(s)</div>
                            </div>
                        </div>
                    {/foreach}
                {else}
                    <p>Noch keine Sprachen in Verwendung.</p>
                {/if}
            </div>
        </div>
        
    {elseif $action == 'edit'}
        <!-- Flagge bearbeiten -->
        <div class="mf-form">
            <h2>Flagge bearbeiten: {$editLanguage.name}</h2>
            
            <form method="POST" action="?plugin=ModernFrontendPlugin&page=languages&do=update_flag">
                <input type="hidden" name="id" value="{$editLanguage.id}">
                
                <div class="mf-form-group">
                    <label>Aktuelle Flagge:</label>
                    <div style="font-size: 48px;">{$editLanguage.flag_icon|default:'(keine)'}</div>
                </div>
                
                <div class="mf-form-group">
                    <label>Neue Flagge (Emoji):</label>
                    <input type="text" name="flag_icon" value="{$editLanguage.flag_icon|default:''}" placeholder="z.B. 🇩🇪">
                    <small style="display:block; margin-top:5px; color:#666;">
                        Verwende ein Flaggen-Emoji (z.B. 🇩🇪, 🇬🇧, 🇫🇷, 🇪🇸, etc.)
                    </small>
                </div>
                
                <div class="mf-form-group">
                    <button type="submit" name="submit" class="mf-btn">Flagge aktualisieren</button>
                    <a href="?plugin=ModernFrontendPlugin&page=languages" class="mf-btn mf-btn-secondary">Abbrechen</a>
                </div>
            </form>
        </div>
    {/if}
</div>

<script>
function switchTab(tab) {
    // Hide all tabs
    document.querySelectorAll('.mf-tab-content').forEach(function(content) {
        content.classList.remove('active');
    });
    document.querySelectorAll('.mf-tab').forEach(function(button) {
        button.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById('tab-' + tab).classList.add('active');
    event.target.classList.add('active');
}
</script>
