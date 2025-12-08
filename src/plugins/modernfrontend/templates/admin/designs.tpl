{include file='_navigation.tpl' currentPage='designs' pageTitle='Design Management'}

<style>
.mf-designs {
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
    font-size: 14px;
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
.mf-btn-small {
    padding: 5px 10px;
    font-size: 12px;
    margin-right: 5px;
}
.mf-design-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.mf-design-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}
.mf-design-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.mf-design-preview {
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}
.mf-color-bar {
    display: flex;
    height: 40px;
}
.mf-color-primary {
    flex: 1;
}
.mf-color-secondary {
    flex: 1;
}
.mf-design-body {
    padding: 15px;
}
.mf-design-name {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 5px;
}
.mf-design-desc {
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
}
.mf-design-meta {
    font-size: 12px;
    color: #999;
    margin-bottom: 10px;
}
.mf-design-actions {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}
.mf-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
    margin-right: 5px;
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
.mf-form {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-width: 700px;
}
.mf-form-group {
    margin-bottom: 20px;
}
.mf-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}
.mf-form-group input[type="text"],
.mf-form-group input[type="color"],
.mf-form-group textarea,
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
.mf-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
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
.mf-color-preview {
    display: inline-block;
    width: 30px;
    height: 30px;
    border-radius: 4px;
    border: 2px solid #ddd;
    vertical-align: middle;
    margin-right: 10px;
}
</style>

<div class="mf-designs">
    <div class="mf-header">
        <h1>🎨 Design-Verwaltung</h1>
        {if $action == 'list'}
            <a href="?plugin=ModernFrontendPlugin&page=designs&do=create" class="mf-btn">+ Neues Design</a>
        {else}
            <a href="?plugin=ModernFrontendPlugin&page=designs" class="mf-btn-secondary mf-btn">← Zurück zur Liste</a>
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
                <div class="value">{$designsCount}</div>
                <div class="label">Designs gesamt</div>
            </div>
            <div class="mf-stat-card">
                <div class="value">{$activeCount}</div>
                <div class="label">Aktive Designs</div>
            </div>
            <div class="mf-stat-card">
                <div class="value">{$inUseCount}</div>
                <div class="label">In Verwendung</div>
            </div>
        </div>
        
        <!-- Design-Grid -->
        <div class="mf-design-grid">
            {foreach from=$designs item=design}
                <div class="mf-design-card">
                    <div class="mf-design-preview">
                        <div class="mf-color-bar" style="width:100%;">
                            <div class="mf-color-primary" style="background:{$design.primary_color};"></div>
                            <div class="mf-color-secondary" style="background:{$design.secondary_color};"></div>
                        </div>
                    </div>
                    <div class="mf-design-body">
                        <div class="mf-design-name">{$design.name}</div>
                        <div class="mf-design-desc">{$design.description|default:'-'}</div>
                        <div class="mf-design-meta">
                            {if $design.is_active == 1}
                                <span class="mf-badge mf-badge-active">Aktiv</span>
                            {else}
                                <span class="mf-badge mf-badge-inactive">Inaktiv</span>
                            {/if}
                            {if $design.usage_count > 0}
                                <span class="mf-badge mf-badge-usage">{$design.usage_count} Domain(s)</span>
                            {/if}
                        </div>
                        <div class="mf-design-meta">
                            <span class="mf-color-preview" style="background:{$design.primary_color};"></span>
                            {$design.primary_color}
                            <br>
                            <span class="mf-color-preview" style="background:{$design.secondary_color};"></span>
                            {$design.secondary_color}
                        </div>
                        <div class="mf-design-actions">
                            <a href="?plugin=ModernFrontendPlugin&page=designs&do=edit&id={$design.id}" class="mf-btn mf-btn-small">Bearbeiten</a>
                            <a href="?plugin=ModernFrontendPlugin&page=designs&do=duplicate&id={$design.id}" class="mf-btn mf-btn-secondary mf-btn-small">Duplizieren</a>
                            {if $design.usage_count == 0}
                                <a href="?plugin=ModernFrontendPlugin&page=designs&do=delete&id={$design.id}" class="mf-btn mf-btn-danger mf-btn-small" onclick="return confirm('Design wirklich löschen?');">Löschen</a>
                            {/if}
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    {elseif $action == 'create' || $action == 'edit'}
        <!-- Formular -->
        <div class="mf-form">
            <h2>{if $action == 'create'}Neues Design erstellen{else}Design bearbeiten{/if}</h2>
            
            <form method="POST" action="?plugin=ModernFrontendPlugin&page=designs&do={$action}">
                {if $action == 'edit'}
                    <input type="hidden" name="id" value="{$editDesign.id}">
                {/if}
                
                <div class="mf-form-group">
                    <label>Name *</label>
                    <input type="text" name="name" value="{$editDesign.name|default:''}" required placeholder="z.B. Corporate Blue">
                </div>
                
                <div class="mf-form-group">
                    <label>Beschreibung</label>
                    <textarea name="description" rows="3" placeholder="Kurze Beschreibung des Designs">{$editDesign.description|default:''}</textarea>
                </div>
                
                <div class="mf-form-group">
                    <label>Template-Pfad *</label>
                    <input type="text" name="template_path" value="{$editDesign.template_path|default:'designs/my-design/'}" required placeholder="designs/my-design/">
                    <small style="color:#666;">Pfad relativ zu plugins/modernfrontend/</small>
                </div>
                
                <div class="mf-form-row">
                    <div class="mf-form-group">
                        <label>Primary Color *</label>
                        <input type="color" name="primary_color" value="{$editDesign.primary_color|default:'#76B82A'}" required>
                    </div>
                    
                    <div class="mf-form-group">
                        <label>Secondary Color *</label>
                        <input type="color" name="secondary_color" value="{$editDesign.secondary_color|default:'#333333'}" required>
                    </div>
                </div>
                
                <div class="mf-form-group">
                    <label>
                        <input type="checkbox" name="is_active" {if $editDesign.is_active == 1 || !$editDesign}checked{/if}>
                        Design ist aktiv
                    </label>
                </div>
                
                <div class="mf-form-group">
                    <button type="submit" name="submit" class="mf-btn">
                        {if $action == 'create'}Design erstellen{else}Änderungen speichern{/if}
                    </button>
                    <a href="?plugin=ModernFrontendPlugin&page=designs" class="mf-btn mf-btn-secondary">Abbrechen</a>
                </div>
            </form>
        </div>
    {/if}
</div>
