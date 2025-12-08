{* Include Navigation *}
{include file='_navigation.tpl' currentPage='theme' pageTitle='Theme'}

<style>
.mf-theme-editor {
    padding: 20px;
    background: #f5f5f5;
}
.mf-theme-section {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.mf-theme-section h2 {
    margin: 0 0 20px 0;
    font-size: 20px;
    color: #333;
    border-bottom: 2px solid #76B82A;
    padding-bottom: 10px;
}
.mf-theme-field {
    margin-bottom: 25px;
}
.mf-theme-field label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #555;
}
.mf-theme-field input[type="text"],
.mf-theme-field select {
    width: 100%;
    max-width: 500px;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
}
.mf-theme-field input[type="color"] {
    width: 80px;
    height: 50px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    cursor: pointer;
}
.mf-color-preview {
    display: inline-block;
    margin-left: 15px;
    padding: 8px 15px;
    border-radius: 4px;
    font-size: 12px;
    color: white;
    font-weight: 600;
    vertical-align: middle;
}
.mf-help-text {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
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
.mf-preview-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 2px dashed #ddd;
    margin-top: 30px;
}
.mf-preview-box h3 {
    margin: 0 0 15px 0;
    font-size: 18px;
    color: #333;
}
.mf-preview-demo {
    display: flex;
    gap: 15px;
    align-items: center;
}
.mf-preview-btn {
    padding: 12px 24px;
    border-radius: 6px;
    font-weight: 600;
    border: none;
    cursor: pointer;
}
</style>

<div class="mf-theme-editor">
    <h1>🎨 Theme Editor</h1>
    <p style="color: #666; margin-bottom: 30px;">
        Passen Sie das Design Ihrer Website an. Änderungen werden sofort übernommen.
    </p>
    
    {if $saved}
        <div class="mf-success-msg">
            ✓ Theme erfolgreich gespeichert! Cache wurde geleert.
        </div>
    {/if}
    
    <form method="POST" action="">
        {foreach from=$theme_config key=group_key item=group}
            <div class="mf-theme-section">
                <h2>{$group.title}</h2>
                
                {foreach from=$group.settings key=setting_key item=setting}
                    <div class="mf-theme-field">
                        <label>{$setting.label}</label>
                        
                        {if $setting.type == 'color'}
                            <div>
                                <input type="color" 
                                       name="theme[{$setting_key}]" 
                                       value="{if isset($theme[$setting_key])}{$theme[$setting_key].setting_value}{else}{$setting.default}{/if}"
                                       onchange="updateColorPreview(this, '{$setting_key}_preview')">
                                <span class="mf-color-preview" 
                                      id="{$setting_key}_preview" 
                                      style="background: {if isset($theme[$setting_key])}{$theme[$setting_key].setting_value}{else}{$setting.default}{/if}">
                                    {if isset($theme[$setting_key])}{$theme[$setting_key].setting_value}{else}{$setting.default}{/if}
                                </span>
                            </div>
                        {elseif $setting.type == 'select'}
                            <select name="theme[{$setting_key}]">
                                {foreach from=$setting.options item=option}
                                    <option value="{$option}" 
                                            {if isset($theme[$setting_key]) && $theme[$setting_key].setting_value == $option}selected{elseif !isset($theme[$setting_key]) && $option == $setting.default}selected{/if}>
                                        {$option}
                                    </option>
                                {/foreach}
                            </select>
                        {else}
                            <input type="text" 
                                   name="theme[{$setting_key}]" 
                                   value="{if isset($theme[$setting_key])}{$theme[$setting_key].setting_value}{else}{$setting.default}{/if}"
                                   placeholder="{$setting.default}">
                        {/if}
                        
                        {if isset($setting.help)}
                            <div class="mf-help-text">{$setting.help}</div>
                        {/if}
                    </div>
                {/foreach}
            </div>
        {/foreach}
        
        <!-- Preview -->
        <div class="mf-preview-box">
            <h3>👁️ Vorschau</h3>
            <p style="color: #666; margin-bottom: 15px;">So sehen Ihre Buttons und Elemente mit den gewählten Farben aus:</p>
            <div class="mf-preview-demo">
                <button type="button" class="mf-preview-btn" 
                        style="background: {if isset($theme.primary_color)}{$theme.primary_color.setting_value}{else}#76B82A{/if}; color: white;"
                        id="preview_primary">
                    Primärer Button
                </button>
                <button type="button" class="mf-preview-btn" 
                        style="background: {if isset($theme.secondary_color)}{$theme.secondary_color.setting_value}{else}#2C3E50{/if}; color: white;"
                        id="preview_secondary">
                    Sekundärer Button
                </button>
                <button type="button" class="mf-preview-btn" 
                        style="background: {if isset($theme.accent_color)}{$theme.accent_color.setting_value}{else}#3498DB{/if}; color: white;"
                        id="preview_accent">
                    Akzent Button
                </button>
            </div>
        </div>
        
        <!-- Save Button -->
        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" name="save_theme" class="mf-save-btn">
                💾 Theme speichern
            </button>
        </div>
        
        <!-- Preview Link -->
        <div style="text-align: center; margin-top: 15px;">
            <a href="/index.php" target="_blank" style="color: #76B82A; text-decoration: underline;">
                👁️ Website im neuen Tab ansehen
            </a>
        </div>
    </form>
</div>

<script>
function updateColorPreview(input, previewId) {
    const preview = document.getElementById(previewId);
    preview.style.background = input.value;
    preview.textContent = input.value;
    
    // Update preview buttons
    if(input.name.includes('primary_color')) {
        document.getElementById('preview_primary').style.background = input.value;
    }
    if(input.name.includes('secondary_color')) {
        document.getElementById('preview_secondary').style.background = input.value;
    }
    if(input.name.includes('accent_color')) {
        document.getElementById('preview_accent').style.background = input.value;
    }
}

// Update preview buttons on page load
document.addEventListener('DOMContentLoaded', function() {
    const colorInputs = document.querySelectorAll('input[type="color"]');
    colorInputs.forEach(input => {
        input.addEventListener('change', function() {
            updateColorPreview(this, this.name.split('[')[1].split(']')[0] + '_preview');
        });
    });
});
</script>
