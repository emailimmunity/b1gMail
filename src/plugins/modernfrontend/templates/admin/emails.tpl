<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<style>
.mf-emails {
    padding: 20px;
    background: #f5f5f5;
}
.mf-template-list {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.mf-template-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #f5f5f5;
}
.mf-template-item:hover {
    background: #f9f9f9;
}
.mf-template-info h4 {
    margin: 0 0 5px 0;
    font-size: 16px;
}
.mf-template-info p {
    margin: 0;
    font-size: 12px;
    color: #999;
}
.mf-template-actions {
    display: flex;
    gap: 10px;
}
.mf-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
}
.mf-btn-edit { background: #3498DB; color: white; }
.mf-btn-delete { background: #E74C3C; color: white; }
.mf-btn-toggle { background: #F39C12; color: white; }
.mf-btn:hover { opacity: 0.8; }
.mf-editor-form {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.mf-form-group {
    margin-bottom: 20px;
}
.mf-form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #555;
}
.mf-form-group input[type="text"],
.mf-form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
}
.mf-form-group textarea {
    min-height: 100px;
    font-family: monospace;
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
.mf-variables-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 6px;
    border: 2px dashed #ddd;
    margin-bottom: 20px;
}
.mf-variables-box h4 {
    margin: 0 0 15px 0;
}
.mf-variable-tag {
    display: inline-block;
    background: #e0e0e0;
    padding: 5px 10px;
    border-radius: 4px;
    margin: 5px;
    cursor: pointer;
    font-family: monospace;
    font-size: 12px;
}
.mf-variable-tag:hover {
    background: #76B82A;
    color: white;
}
.mf-create-btn {
    background: #76B82A;
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 16px;
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
}
.mf-status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}
.mf-status-badge.active { background: #d4edda; color: #155724; }
.mf-status-badge.inactive { background: #f8d7da; color: #721c24; }
</style>

<div class="mf-emails">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1>📧 Email Templates</h1>
            <p style="color: #666; margin: 0;">Verwalte E-Mail-Vorlagen für automatische Benachrichtigungen</p>
        </div>
        {if !$edit_template}
        <a href="{$pageURL}&page=emails&edit=0" class="mf-create-btn">
            ➕ Neues Template
        </a>
        {/if}
    </div>
    
    {if $success}
        <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            ✓ {$success}
        </div>
    {/if}
    
    {if $edit_template !== null}
        <!-- Template Editor -->
        <div class="mf-editor-form">
            <h2>{if $edit_template}Template bearbeiten{else}Neues Template{/if}</h2>
            
            <form method="POST">
                <input type="hidden" name="template_id" value="{if $edit_template}{$edit_template.id}{/if}">
                
                <div class="mf-form-group">
                    <label>Template-Name *</label>
                    <input type="text" name="template_name" value="{if $edit_template}{$edit_template.template_name}{/if}" required>
                </div>
                
                <div class="mf-form-group">
                    <label>Template-Key *</label>
                    <input type="text" name="template_key" value="{if $edit_template}{$edit_template.template_key}{else}template_{/if}" required {if $edit_template}readonly{/if}>
                    <p style="font-size: 12px; color: #999; margin: 5px 0 0 0;">
                        Eindeutiger Identifier (z.B. welcome_email, order_confirmation)
                    </p>
                </div>
                
                <!-- Variables -->
                <div class="mf-variables-box">
                    <h4>📋 Verfügbare Variablen</h4>
                    <p style="color: #666; font-size: 14px; margin-bottom: 10px;">Klicke auf eine Variable, um sie zu kopieren:</p>
                    {foreach from=$default_variables key=var item=desc}
                        <span class="mf-variable-tag" onclick="copyToClipboard('{$var}')" title="{$desc}">
                            {$var}
                        </span>
                    {/foreach}
                </div>
                
                <!-- Language Tabs -->
                <div class="mf-lang-tabs">
                    <button type="button" class="mf-lang-tab active" onclick="switchLang(this, 'de')">
                        🇩🇪 Deutsch
                    </button>
                    <button type="button" class="mf-lang-tab" onclick="switchLang(this, 'en')">
                        🇬🇧 English
                    </button>
                </div>
                
                <!-- German Version -->
                <div class="mf-lang-content active" id="lang-de">
                    <div class="mf-form-group">
                        <label>Betreff (DE) *</label>
                        <input type="text" name="subject_de" value="{if $edit_template}{$edit_template.subject_de}{/if}" required>
                    </div>
                    
                    <div class="mf-form-group">
                        <label>HTML Version (DE)</label>
                        <textarea name="body_html_de" id="body_html_de" class="tinymce">{if $edit_template}{$edit_template.body_html_de}{/if}</textarea>
                    </div>
                    
                    <div class="mf-form-group">
                        <label>Text Version (DE)</label>
                        <textarea name="body_text_de" rows="8">{if $edit_template}{$edit_template.body_text_de}{/if}</textarea>
                        <p style="font-size: 12px; color: #999; margin: 5px 0 0 0;">
                            Fallback für E-Mail-Clients ohne HTML-Support
                        </p>
                    </div>
                </div>
                
                <!-- English Version -->
                <div class="mf-lang-content" id="lang-en">
                    <div class="mf-form-group">
                        <label>Subject (EN)</label>
                        <input type="text" name="subject_en" value="{if $edit_template}{$edit_template.subject_en}{/if}">
                    </div>
                    
                    <div class="mf-form-group">
                        <label>HTML Version (EN)</label>
                        <textarea name="body_html_en" id="body_html_en" class="tinymce">{if $edit_template}{$edit_template.body_html_en}{/if}</textarea>
                    </div>
                    
                    <div class="mf-form-group">
                        <label>Text Version (EN)</label>
                        <textarea name="body_text_en" rows="8">{if $edit_template}{$edit_template.body_text_en}{/if}</textarea>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
                    <a href="{$pageURL}&page=emails" style="padding: 15px 40px; background: #999; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                        Abbrechen
                    </a>
                    <button type="submit" name="save_template" class="mf-save-btn">
                        💾 Template speichern
                    </button>
                </div>
            </form>
        </div>
    {else}
        <!-- Template List -->
        <div class="mf-template-list">
            <h2 style="margin: 0 0 20px 0;">📋 Alle Templates</h2>
            
            {if $templates && count($templates) > 0}
                {foreach from=$templates item=template}
                    <div class="mf-template-item">
                        <div class="mf-template-info">
                            <h4>
                                {$template.template_name}
                                <span class="mf-status-badge {$template.status}">{$template.status}</span>
                            </h4>
                            <p>Key: {$template.template_key}</p>
                        </div>
                        <div class="mf-template-actions">
                            <a href="{$pageURL}&page=emails&edit={$template.id}" class="mf-btn mf-btn-edit">
                                ✏️ Bearbeiten
                            </a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="template_id" value="{$template.id}">
                                <input type="hidden" name="new_status" value="{if $template.status == 'active'}inactive{else}active{/if}">
                                <button type="submit" name="change_status" class="mf-btn mf-btn-toggle">
                                    {if $template.status == 'active'}⏸️{else}▶️{/if}
                                </button>
                            </form>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Template wirklich löschen?')">
                                <input type="hidden" name="template_id" value="{$template.id}">
                                <button type="submit" name="delete_template" class="mf-btn mf-btn-delete">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                {/foreach}
            {else}
                <div style="text-align: center; padding: 40px; color: #999;">
                    <p>Noch keine Templates vorhanden</p>
                </div>
            {/if}
        </div>
    {/if}
</div>

<script>
// Initialize TinyMCE
{if $edit_template !== null}
tinymce.init({
    selector: '.tinymce',
    height: 400,
    menubar: false,
    plugins: 'link image code table lists',
    toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
    content_style: 'body { font-family: Inter, sans-serif; font-size: 14px; }'
});
{/if}

function switchLang(btn, lang) {
    document.querySelectorAll('.mf-lang-tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.mf-lang-content').forEach(content => content.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('lang-' + lang).classList.add('active');
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Variable kopiert: ' + text);
    });
}
</script>
