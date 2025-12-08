<style>
.mf-forms {
    padding: 20px;
    background: #f5f5f5;
}
.mf-form-list {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.mf-form-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #f5f5f5;
}
.mf-form-item:hover {
    background: #f9f9f9;
}
.mf-form-info h4 {
    margin: 0 0 5px 0;
}
.mf-form-info p {
    margin: 0;
    font-size: 12px;
    color: #999;
}
.mf-unread-badge {
    display: inline-block;
    background: #E74C3C;
    color: white;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 10px;
}
.mf-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}
.mf-btn-edit { background: #3498DB; color: white; }
.mf-btn-submissions { background: #27AE60; color: white; }
.mf-btn-delete { background: #E74C3C; color: white; }
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
}
.mf-form-group input,
.mf-form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
}
.mf-field-builder {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}
.mf-field-item {
    background: white;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 10px;
    border: 2px solid #e0e0e0;
}
.mf-field-item:hover {
    border-color: #76B82A;
}
.mf-field-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.mf-field-controls {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.mf-field-controls input,
.mf-field-controls select {
    padding: 8px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
}
.mf-add-field-btn {
    background: #76B82A;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    width: 100%;
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
    text-decoration: none;
    display: inline-block;
}
.mf-submissions-table {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.mf-table {
    width: 100%;
    border-collapse: collapse;
}
.mf-table th {
    text-align: left;
    padding: 12px;
    background: #f5f5f5;
    font-weight: 600;
    border-bottom: 2px solid #e0e0e0;
}
.mf-table td {
    padding: 12px;
    border-bottom: 1px solid #f5f5f5;
}
.mf-table tr:hover {
    background: #f9f9f9;
}
.mf-status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}
.mf-status-badge.new { background: #fff3cd; color: #856404; }
.mf-status-badge.read { background: #cfe2ff; color: #084298; }
.mf-status-badge.replied { background: #d4edda; color: #155724; }
.mf-status-badge.archived { background: #f5f5f5; color: #999; }
</style>

<div class="mf-forms">
    {if $view == 'submissions'}
        <!-- Submissions View -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1>📥 Einsendungen: {$form.form_name}</h1>
                <p style="color: #666; margin: 0;">Formular-Key: {$form.form_key}</p>
            </div>
            <a href="{$pageURL}&page=forms" class="mf-create-btn">
                ← Zurück zur Übersicht
            </a>
        </div>
        
        {if $success}
            <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                ✓ {$success}
            </div>
        {/if}
        
        <div class="mf-submissions-table">
            {if $submissions && count($submissions) > 0}
                <table class="mf-table">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Daten</th>
                            <th>Status</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$submissions item=submission}
                            <tr>
                                <td>{$submission.created_at}</td>
                                <td>
                                    {foreach from=$submission.form_data key=field item=value}
                                        <strong>{$field}:</strong> {$value}<br>
                                    {/foreach}
                                </td>
                                <td>
                                    <span class="mf-status-badge {$submission.status}">{$submission.status}</span>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="submission_id" value="{$submission.id}">
                                        <select name="new_status" onchange="this.form.submit()" style="padding: 6px; border-radius: 4px; border: 1px solid #e0e0e0;">
                                            <option value="new" {if $submission.status == 'new'}selected{/if}>Neu</option>
                                            <option value="read" {if $submission.status == 'read'}selected{/if}>Gelesen</option>
                                            <option value="replied" {if $submission.status == 'replied'}selected{/if}>Beantwortet</option>
                                            <option value="archived" {if $submission.status == 'archived'}selected{/if}>Archiviert</option>
                                        </select>
                                        <input type="hidden" name="change_submission_status" value="1">
                                    </form>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            {else}
                <div style="text-align: center; padding: 40px; color: #999;">
                    <p>Noch keine Einsendungen vorhanden</p>
                </div>
            {/if}
        </div>
        
    {elseif $edit_form !== null}
        <!-- Form Editor -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1>📋 {if $edit_form.id > 0}Formular bearbeiten{else}Neues Formular{/if}</h1>
            <a href="{$pageURL}&page=forms" class="mf-create-btn" style="background: #999;">
                Abbrechen
            </a>
        </div>
        
        <div class="mf-editor-form">
            <form method="POST" id="form-editor">
                <input type="hidden" name="form_id" value="{$edit_form.id}">
                
                <div class="mf-form-group">
                    <label>Formular-Name *</label>
                    <input type="text" name="form_name" value="{$edit_form.form_name}" required>
                </div>
                
                <div class="mf-form-group">
                    <label>Formular-Key *</label>
                    <input type="text" name="form_key" value="{$edit_form.form_key}" required {if $edit_form.id > 0}readonly{/if}>
                    <p style="font-size: 12px; color: #999; margin: 5px 0 0 0;">Eindeutiger Identifier (z.B. contact_form)</p>
                </div>
                
                <div class="mf-form-group">
                    <label>Benachrichtigungs-E-Mail</label>
                    <input type="email" name="notification_email" value="{$edit_form.notification_email}" placeholder="info@example.com">
                    <p style="font-size: 12px; color: #999; margin: 5px 0 0 0;">Empfänger für Formular-Einsendungen</p>
                </div>
                
                <!-- Field Builder -->
                <div class="mf-field-builder">
                    <h3>🔨 Formular-Felder</h3>
                    <div id="fields-container">
                        {foreach from=$edit_form.fields item=field name=loop}
                            <div class="mf-field-item" data-index="{$smarty.foreach.loop.index}">
                                <div class="mf-field-header">
                                    <strong>Feld #{$smarty.foreach.loop.iteration}</strong>
                                    <button type="button" onclick="removeField(this)" style="background: #E74C3C; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer;">
                                        🗑️ Entfernen
                                    </button>
                                </div>
                                <div class="mf-field-controls">
                                    <div>
                                        <label style="font-size: 12px; margin-bottom: 5px;">Feldname:</label>
                                        <input type="text" name="fields[{$smarty.foreach.loop.index}][name]" value="{$field.name}" required>
                                    </div>
                                    <div>
                                        <label style="font-size: 12px; margin-bottom: 5px;">Typ:</label>
                                        <select name="fields[{$smarty.foreach.loop.index}][type]">
                                            {foreach from=$field_types key=type item=label}
                                                <option value="{$type}" {if $field.type == $type}selected{/if}>{$label}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size: 12px; margin-bottom: 5px;">Label (DE):</label>
                                        <input type="text" name="fields[{$smarty.foreach.loop.index}][label_de]" value="{$field.label_de}">
                                    </div>
                                    <div>
                                        <label style="font-size: 12px; margin-bottom: 5px;">Label (EN):</label>
                                        <input type="text" name="fields[{$smarty.foreach.loop.index}][label_en]" value="{$field.label_en}">
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <input type="checkbox" name="fields[{$smarty.foreach.loop.index}][required]" value="1" {if $field.required}checked{/if} id="required_{$smarty.foreach.loop.index}">
                                        <label for="required_{$smarty.foreach.loop.index}" style="margin: 0;">Pflichtfeld</label>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                    <button type="button" onclick="addField()" class="mf-add-field-btn">
                        ➕ Feld hinzufügen
                    </button>
                </div>
                
                <div class="mf-form-group">
                    <label>Erfolgsmeldung (DE)</label>
                    <textarea name="success_message_de" rows="2">{$edit_form.success_message_de}</textarea>
                </div>
                
                <div class="mf-form-group">
                    <label>Success Message (EN)</label>
                    <textarea name="success_message_en" rows="2">{$edit_form.success_message_en}</textarea>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" name="save_form" class="mf-create-btn">
                        💾 Formular speichern
                    </button>
                </div>
            </form>
        </div>
        
    {else}
        <!-- Form List -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1>📋 Kontaktformulare</h1>
                <p style="color: #666; margin: 0;">Erstelle und verwalte Kontaktformulare</p>
            </div>
            <a href="{$pageURL}&page=forms&edit=0" class="mf-create-btn">
                ➕ Neues Formular
            </a>
        </div>
        
        {if $success}
            <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                ✓ {$success}
            </div>
        {/if}
        
        <div class="mf-form-list">
            {if $forms && count($forms) > 0}
                {foreach from=$forms item=form}
                    <div class="mf-form-item">
                        <div class="mf-form-info">
                            <h4>
                                {$form.form_name}
                                {if $form.unread_count > 0}
                                    <span class="mf-unread-badge">{$form.unread_count} neu</span>
                                {/if}
                            </h4>
                            <p>Key: {$form.form_key}</p>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <a href="{$pageURL}&page=forms&submissions={$form.id}" class="mf-btn mf-btn-submissions">
                                📥 Einsendungen
                            </a>
                            <a href="{$pageURL}&page=forms&edit={$form.id}" class="mf-btn mf-btn-edit">
                                ✏️ Bearbeiten
                            </a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Formular wirklich löschen?')">
                                <input type="hidden" name="form_id" value="{$form.id}">
                                <button type="submit" name="delete_form" class="mf-btn mf-btn-delete">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                {/foreach}
            {else}
                <div style="text-align: center; padding: 40px; color: #999;">
                    <p>Noch keine Formulare vorhanden</p>
                </div>
            {/if}
        </div>
    {/if}
</div>

<script>
let fieldIndex = {if $edit_form && $edit_form.fields}{count($edit_form.fields)}{else}0{/if};

function addField() {
    const container = document.getElementById('fields-container');
    const fieldHTML = `
        <div class="mf-field-item" data-index="${fieldIndex}">
            <div class="mf-field-header">
                <strong>Feld #${fieldIndex + 1}</strong>
                <button type="button" onclick="removeField(this)" style="background: #E74C3C; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer;">
                    🗑️ Entfernen
                </button>
            </div>
            <div class="mf-field-controls">
                <div>
                    <label style="font-size: 12px; margin-bottom: 5px;">Feldname:</label>
                    <input type="text" name="fields[${fieldIndex}][name]" required>
                </div>
                <div>
                    <label style="font-size: 12px; margin-bottom: 5px;">Typ:</label>
                    <select name="fields[${fieldIndex}][type]">
                        <option value="text">Text (einzeilig)</option>
                        <option value="email">E-Mail</option>
                        <option value="tel">Telefon</option>
                        <option value="textarea">Text (mehrzeilig)</option>
                        <option value="select">Dropdown</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="radio">Radio-Buttons</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; margin-bottom: 5px;">Label (DE):</label>
                    <input type="text" name="fields[${fieldIndex}][label_de]">
                </div>
                <div>
                    <label style="font-size: 12px; margin-bottom: 5px;">Label (EN):</label>
                    <input type="text" name="fields[${fieldIndex}][label_en]">
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="fields[${fieldIndex}][required]" value="1" id="required_${fieldIndex}">
                    <label for="required_${fieldIndex}" style="margin: 0;">Pflichtfeld</label>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', fieldHTML);
    fieldIndex++;
}

function removeField(btn) {
    if(confirm('Feld wirklich entfernen?')) {
        btn.closest('.mf-field-item').remove();
    }
}
</script>
