<style>
.mf-settings {
    padding: 20px;
    background: #f5f5f5;
}
.mf-settings-section {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.mf-settings-section h2 {
    margin: 0 0 20px 0;
    font-size: 20px;
    color: #333;
    border-bottom: 2px solid #76B82A;
    padding-bottom: 10px;
}
.mf-setting-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #f5f5f5;
}
.mf-setting-row:last-child {
    border-bottom: none;
}
.mf-setting-label {
    flex: 1;
}
.mf-setting-label label {
    font-weight: 600;
    color: #555;
    cursor: pointer;
}
.mf-setting-label .help {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}
.mf-setting-control {
    flex: 0 0 auto;
    min-width: 200px;
    text-align: right;
}
.mf-setting-control input[type="text"],
.mf-setting-control input[type="number"],
.mf-setting-control textarea,
.mf-setting-control select {
    width: 100%;
    padding: 8px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
}
.mf-setting-control textarea {
    min-height: 80px;
    resize: vertical;
}
.mf-toggle {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 30px;
}
.mf-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}
.mf-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 30px;
}
.mf-toggle-slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}
.mf-toggle input:checked + .mf-toggle-slider {
    background-color: #76B82A;
}
.mf-toggle input:checked + .mf-toggle-slider:before {
    transform: translateX(30px);
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
.mf-danger-zone {
    background: #fff5f5;
    border-left: 4px solid #E74C3C;
}
.mf-info-box {
    background: #f0f9ff;
    border-left: 4px solid #3498DB;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
</style>

<div class="mf-settings">
    <h1>⚙️ Einstellungen</h1>
    <p style="color: #666; margin-bottom: 30px;">
        Passen Sie das Plugin nach Ihren Bedürfnissen an
    </p>
    
    {if $saved}
        <div class="mf-success-msg">
            ✓ Einstellungen erfolgreich gespeichert!
        </div>
    {/if}
    
    <form method="POST" action="">
        {foreach from=$settings_config key=group_key item=group}
            <div class="mf-settings-section {if $group_key == 'cache'}mf-danger-zone{/if}">
                <h2>{$group.title}</h2>
                
                {foreach from=$group.settings key=setting_key item=setting}
                    <div class="mf-setting-row">
                        <div class="mf-setting-label">
                            <label for="setting_{$setting_key}">{$setting.label}</label>
                            {if isset($setting.help)}
                                <div class="help">{$setting.help}</div>
                            {/if}
                        </div>
                        
                        <div class="mf-setting-control">
                            {if $setting.type == 'boolean'}
                                <label class="mf-toggle">
                                    <input type="checkbox" 
                                           id="setting_{$setting_key}"
                                           name="settings[{$setting_key}]" 
                                           value="1"
                                           {if isset($settings[$setting_key]) && $settings[$setting_key].setting_value == '1'}checked{elseif !isset($settings[$setting_key]) && $setting.default == '1'}checked{/if}>
                                    <span class="mf-toggle-slider"></span>
                                </label>
                                <input type="hidden" name="settings[{$setting_key}]" value="0">
                            {elseif $setting.type == 'select'}
                                <select id="setting_{$setting_key}" name="settings[{$setting_key}]">
                                    {foreach from=$setting.options key=opt_key item=opt_label}
                                        <option value="{$opt_key}" 
                                                {if isset($settings[$setting_key]) && $settings[$setting_key].setting_value == $opt_key}selected{elseif !isset($settings[$setting_key]) && $setting.default == $opt_key}selected{/if}>
                                            {$opt_label}
                                        </option>
                                    {/foreach}
                                </select>
                            {elseif $setting.type == 'textarea'}
                                <textarea id="setting_{$setting_key}" 
                                          name="settings[{$setting_key}]" 
                                          rows="3">{if isset($settings[$setting_key])}{$settings[$setting_key].setting_value}{else}{$setting.default}{/if}</textarea>
                            {elseif $setting.type == 'number'}
                                <input type="number" 
                                       id="setting_{$setting_key}"
                                       name="settings[{$setting_key}]" 
                                       value="{if isset($settings[$setting_key])}{$settings[$setting_key].setting_value}{else}{$setting.default}{/if}"
                                       min="0">
                            {else}
                                <input type="text" 
                                       id="setting_{$setting_key}"
                                       name="settings[{$setting_key}]" 
                                       value="{if isset($settings[$setting_key])}{$settings[$setting_key].setting_value}{else}{$setting.default}{/if}"
                                       placeholder="{$setting.default}">
                            {/if}
                        </div>
                    </div>
                {/foreach}
            </div>
        {/foreach}
        
        <!-- Info Box -->
        <div class="mf-info-box">
            <h3 style="margin: 0 0 10px 0;">💡 Hinweise</h3>
            <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
                <li><strong>Cache:</strong> Nach Änderungen wird der Cache automatisch geleert</li>
                <li><strong>Analytics:</strong> IP-Adressen werden nur anonymisiert gespeichert (DSGVO-konform)</li>
                <li><strong>Performance:</strong> CSS/JS Minifizierung verbessert Ladezeiten</li>
                <li><strong>Wartungsmodus:</strong> Zeigt Besuchern eine Wartungsseite an</li>
            </ul>
        </div>
        
        <!-- Save Button -->
        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" name="save_settings" class="mf-save-btn">
                💾 Einstellungen speichern
            </button>
        </div>
    </form>
    
    <!-- System Info -->
    <div class="mf-settings-section" style="margin-top: 30px;">
        <h2>ℹ️ System-Informationen</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #f5f5f5;">
                <td style="padding: 12px; font-weight: bold;">Plugin Version</td>
                <td style="padding: 12px;">{$smarty.const.MODERNFRONTEND_VERSION}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f5f5f5;">
                <td style="padding: 12px; font-weight: bold;">PHP Version</td>
                <td style="padding: 12px;">{$smarty.const.PHP_VERSION}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f5f5f5;">
                <td style="padding: 12px; font-weight: bold;">b1gMail Version</td>
                <td style="padding: 12px;">{$smarty.const.B1GMAIL_VERSION}</td>
            </tr>
            <tr>
                <td style="padding: 12px; font-weight: bold;">Upload Max Filesize</td>
                <td style="padding: 12px;">{$smarty.server.upload_max_filesize|default:'?'}</td>
            </tr>
        </table>
    </div>
    
    <!-- Cache Actions -->
    <div class="mf-settings-section mf-danger-zone" style="margin-top: 20px;">
        <h2>🧹 Cache & Wartung</h2>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <button type="button" onclick="clearCache()" style="padding: 12px 24px; background: #F39C12; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                🗑️ Cache leeren
            </button>
            <button type="button" onclick="regenerateThumbnails()" style="padding: 12px 24px; background: #3498DB; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                🖼️ Thumbnails neu generieren
            </button>
            <button type="button" onclick="exportSettings()" style="padding: 12px 24px; background: #27AE60; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                📥 Einstellungen exportieren
            </button>
        </div>
    </div>
</div>

<script>
function clearCache() {
    if(confirm('Cache wirklich leeren?')) {
        // TODO: Implement via AJAX
        alert('Cache wird geleert...');
    }
}

function regenerateThumbnails() {
    if(confirm('Alle Thumbnails neu generieren? Dies kann einige Minuten dauern.')) {
        // TODO: Implement via AJAX
        alert('Thumbnails werden regeneriert...');
    }
}

function exportSettings() {
    // TODO: Implement download
    alert('Export-Funktion wird implementiert...');
}
</script>
