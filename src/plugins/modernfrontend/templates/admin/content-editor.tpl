{include file='_navigation.tpl' currentPage='content' pageTitle='Content Editor'}

<style>
.mf-content-editor {
    padding: 20px;
    background: #f5f5f5;
}
.mf-section {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.mf-section h2 {
    margin: 0 0 20px 0;
    font-size: 20px;
    color: #333;
    border-bottom: 2px solid #76B82A;
    padding-bottom: 10px;
}
.mf-field-group {
    margin-bottom: 25px;
}
.mf-field-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #555;
}
.mf-field-group input[type="text"],
.mf-field-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s;
}
.mf-field-group input[type="text"]:focus,
.mf-field-group textarea:focus {
    outline: none;
    border-color: #76B82A;
}
.mf-field-group textarea {
    min-height: 100px;
    resize: vertical;
    font-family: inherit;
}
.mf-lang-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
}
.mf-lang-tab {
    padding: 10px 20px;
    cursor: pointer;
    border: none;
    background: none;
    color: #666;
    font-weight: 500;
    transition: all 0.3s;
}
.mf-lang-tab.active {
    color: #76B82A;
    border-bottom: 2px solid #76B82A;
    margin-bottom: -2px;
}
.mf-lang-content {
    display: none;
}
.mf-lang-content.active {
    display: block;
}
.mf-save-btn {
    background: #76B82A;
    color: white;
    padding: 15px 40px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}
.mf-save-btn:hover {
    background: #5D9321;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
.mf-success-msg {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}
.mf-help-text {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}
</style>

<div class="mf-content-editor">
    <h1>📝 Content Editor</h1>
    <p style="color: #666; margin-bottom: 30px;">
        Bearbeiten Sie alle Texte Ihrer Landing Page. Änderungen werden sofort auf der Website sichtbar.
    </p>
    
    {if $saved}
        <div class="mf-success-msg">
            ✓ Änderungen erfolgreich gespeichert!
        </div>
    {/if}
    
    <form method="POST" action="">
        {foreach from=$sections key=section_key item=section}
            <div class="mf-section">
                <h2>{$section.title}</h2>
                
                {foreach from=$section.fields key=field_key item=field_label}
                    <div class="mf-field-group">
                        <label>{$field_label}</label>
                        
                        <!-- Language Tabs -->
                        <div class="mf-lang-tabs">
                            <button type="button" class="mf-lang-tab active" onclick="switchLang(this, '{$section_key}_{$field_key}_de')">
                                🇩🇪 Deutsch
                            </button>
                            <button type="button" class="mf-lang-tab" onclick="switchLang(this, '{$section_key}_{$field_key}_en')">
                                🇬🇧 English
                            </button>
                        </div>
                        
                        <!-- German Content -->
                        <div class="mf-lang-content active" id="{$section_key}_{$field_key}_de">
                            {if $field_key == 'meta_description' || $field_key == 'subtitle' || $field_key == 'company_description'}
                                <textarea name="content[{$section_key}][{$field_key}][de]" rows="3">{if isset($content[$section_key][$field_key])}{$content[$section_key][$field_key].content_de}{/if}</textarea>
                            {else}
                                <input type="text" name="content[{$section_key}][{$field_key}][de]" value="{if isset($content[$section_key][$field_key])}{$content[$section_key][$field_key].content_de}{/if}">
                            {/if}
                        </div>
                        
                        <!-- English Content -->
                        <div class="mf-lang-content" id="{$section_key}_{$field_key}_en">
                            {if $field_key == 'meta_description' || $field_key == 'subtitle' || $field_key == 'company_description'}
                                <textarea name="content[{$section_key}][{$field_key}][en]" rows="3">{if isset($content[$section_key][$field_key])}{$content[$section_key][$field_key].content_en}{/if}</textarea>
                            {else}
                                <input type="text" name="content[{$section_key}][{$field_key}][en]" value="{if isset($content[$section_key][$field_key])}{$content[$section_key][$field_key].content_en}{/if}">
                            {/if}
                        </div>
                        
                        {if $field_key == 'cta_url'}
                            <div class="mf-help-text">Relativer Link (z.B. /register) oder absoluter Link (https://...)</div>
                        {/if}
                        {if $field_key == 'meta_description'}
                            <div class="mf-help-text">Optimale Länge: 150-160 Zeichen. Wird in Suchmaschinen angezeigt.</div>
                        {/if}
                    </div>
                {/foreach}
            </div>
        {/foreach}
        
        <!-- Save Button -->
        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" name="save_content" class="mf-save-btn">
                💾 Alle Änderungen speichern
            </button>
        </div>
        
        <!-- Preview Button -->
        <div style="text-align: center; margin-top: 15px;">
            <a href="/index.php" target="_blank" style="color: #76B82A; text-decoration: underline;">
                👁️ Landing Page im neuen Tab ansehen
            </a>
        </div>
    </form>
</div>

<script>
function switchLang(btn, contentId) {
    // Get parent container
    const container = btn.closest('.mf-field-group');
    
    // Remove active from all tabs
    container.querySelectorAll('.mf-lang-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active from all content
    container.querySelectorAll('.mf-lang-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Add active to clicked tab
    btn.classList.add('active');
    
    // Show corresponding content
    document.getElementById(contentId).classList.add('active');
}
</script>
