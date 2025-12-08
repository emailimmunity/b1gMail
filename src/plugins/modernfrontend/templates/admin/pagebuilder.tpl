<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<style>
.mf-pagebuilder {
    padding: 20px;
    background: #f5f5f5;
}
.mf-page-list {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.mf-page-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #f5f5f5;
}
.mf-page-item:hover {
    background: #f9f9f9;
}
.mf-page-info h4 {
    margin: 0 0 5px 0;
}
.mf-page-info p {
    margin: 0;
    font-size: 12px;
    color: #999;
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
.mf-btn-view { background: #27AE60; color: white; }
.mf-btn-delete { background: #E74C3C; color: white; }
.mf-btn:hover { opacity: 0.8; }
.mf-editor {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 20px;
}
.mf-sidebar {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    height: fit-content;
    position: sticky;
    top: 20px;
}
.mf-section-type {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    margin-bottom: 10px;
    cursor: move;
    transition: all 0.3s;
}
.mf-section-type:hover {
    background: #76B82A;
    color: white;
    transform: translateX(5px);
}
.mf-canvas {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    min-height: 600px;
}
.mf-section-block {
    border: 2px dashed #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    background: #fafafa;
    cursor: move;
    transition: all 0.3s;
}
.mf-section-block:hover {
    border-color: #76B82A;
    background: white;
}
.mf-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}
.mf-section-header h4 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.mf-section-actions button {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-left: 5px;
}
.mf-form-group {
    margin-bottom: 15px;
}
.mf-form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    font-size: 12px;
}
.mf-form-group input,
.mf-form-group textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    font-size: 14px;
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
.mf-status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}
.mf-status-badge.draft { background: #f5f5f5; color: #999; }
.mf-status-badge.published { background: #d4edda; color: #155724; }
.mf-status-badge.archived { background: #f8d7da; color: #721c24; }
</style>

<div class="mf-pagebuilder">
    {if $edit_page !== null}
        <!-- Page Editor -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1>🏗️ {if $edit_page.id > 0}Seite bearbeiten{else}Neue Seite{/if}</h1>
            <a href="{$pageURL}&page=pagebuilder" class="mf-create-btn" style="background: #999;">
                ← Zurück
            </a>
        </div>
        
        <form method="POST" id="page-form">
            <input type="hidden" name="page_id" value="{$edit_page.id}">
            
            <!-- Page Settings -->
            <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;">
                <h3>⚙️ Seiten-Einstellungen</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="mf-form-group">
                        <label>URL-Slug *</label>
                        <input type="text" name="slug" value="{$edit_page.slug}" required>
                        <p style="font-size: 11px; color: #999; margin: 3px 0 0 0;">URL: /page/{$edit_page.slug}</p>
                    </div>
                    
                    <div class="mf-form-group">
                        <label>Status</label>
                        <select name="status" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px;">
                            <option value="draft" {if $edit_page.status == 'draft'}selected{/if}>Entwurf</option>
                            <option value="published" {if $edit_page.status == 'published'}selected{/if}>Veröffentlicht</option>
                            <option value="archived" {if $edit_page.status == 'archived'}selected{/if}>Archiviert</option>
                        </select>
                    </div>
                    
                    <div class="mf-form-group">
                        <label>Titel (DE) *</label>
                        <input type="text" name="title_de" value="{$edit_page.title_de}" required>
                    </div>
                    
                    <div class="mf-form-group">
                        <label>Title (EN)</label>
                        <input type="text" name="title_en" value="{$edit_page.title_en}">
                    </div>
                    
                    <div class="mf-form-group">
                        <label>Meta-Beschreibung (DE)</label>
                        <textarea name="meta_description_de" rows="2">{$edit_page.meta_description_de}</textarea>
                    </div>
                    
                    <div class="mf-form-group">
                        <label>Meta Description (EN)</label>
                        <textarea name="meta_description_en" rows="2">{$edit_page.meta_description_en}</textarea>
                    </div>
                </div>
            </div>
            
            <!-- Page Builder -->
            <div class="mf-editor">
                <!-- Sidebar with Section Types -->
                <div class="mf-sidebar">
                    <h3 style="margin: 0 0 15px 0;">📦 Sections</h3>
                    <p style="font-size: 12px; color: #666; margin-bottom: 15px;">
                        Ziehe Sections auf die Canvas
                    </p>
                    
                    {foreach from=$section_types key=type item=info}
                        <div class="mf-section-type" data-type="{$type}">
                            <span style="font-size: 20px;">{$info.icon}</span>
                            <span>{$info.name}</span>
                        </div>
                    {/foreach}
                </div>
                
                <!-- Canvas -->
                <div class="mf-canvas" id="canvas">
                    <h3 style="margin: 0 0 20px 0;">🎨 Page Canvas</h3>
                    
                    <div id="sections-container">
                        {if $sections && count($sections) > 0}
                            {foreach from=$sections item=section name=loop}
                                <div class="mf-section-block" data-type="{$section.section_type}" data-index="{$smarty.foreach.loop.index}">
                                    <div class="mf-section-header">
                                        <h4>
                                            <span>{$section_types[$section.section_type].icon}</span>
                                            {$section_types[$section.section_type].name}
                                        </h4>
                                        <div class="mf-section-actions">
                                            <button type="button" onclick="removeSection(this)" style="background: #E74C3C; color: white;">
                                                🗑️ Entfernen
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mf-section-content">
                                        <div class="mf-form-group">
                                            <label>Content (JSON)</label>
                                            <textarea name="sections[{$smarty.foreach.loop.index}][content]" rows="3">{$section.content|@json_encode}</textarea>
                                        </div>
                                        <input type="hidden" name="sections[{$smarty.foreach.loop.index}][type]" value="{$section.section_type}">
                                    </div>
                                </div>
                            {/foreach}
                        {else}
                            <div style="text-align: center; padding: 60px; color: #999; border: 2px dashed #e0e0e0; border-radius: 8px;">
                                <p style="font-size: 18px; margin: 0;">Noch keine Sections</p>
                                <p style="margin: 10px 0 0 0;">Ziehe Sections von der Sidebar hierher</p>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
            
            <!-- Save Button -->
            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" name="save_page" class="mf-create-btn" style="padding: 20px 60px; font-size: 18px;">
                    💾 Seite speichern
                </button>
            </div>
        </form>
        
    {else}
        <!-- Page List -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1>🏗️ Page Builder</h1>
                <p style="color: #666; margin: 0;">Erstelle und verwalte custom Pages</p>
            </div>
            <a href="{$pageURL}&page=pagebuilder&edit=0" class="mf-create-btn">
                ➕ Neue Seite
            </a>
        </div>
        
        {if $success}
            <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                ✓ {$success}
            </div>
        {/if}
        
        <div class="mf-page-list">
            {if $pages && count($pages) > 0}
                {foreach from=$pages item=page}
                    <div class="mf-page-item">
                        <div class="mf-page-info">
                            <h4>
                                {$page.title_de}
                                <span class="mf-status-badge {$page.status}">{$page.status}</span>
                            </h4>
                            <p>
                                /{$page.slug} • 
                                {$page.section_count} Sections •
                                Erstellt: {$page.created_at|date_format:"%d.%m.%Y"}
                            </p>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            {if $page.status == 'published'}
                            <a href="/index.php?page={$page.slug}" target="_blank" class="mf-btn mf-btn-view">
                                👁️ Ansehen
                            </a>
                            {/if}
                            <a href="{$pageURL}&page=pagebuilder&edit={$page.id}" class="mf-btn mf-btn-edit">
                                ✏️ Bearbeiten
                            </a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Seite wirklich löschen?')">
                                <input type="hidden" name="page_id" value="{$page.id}">
                                <button type="submit" name="delete_page" class="mf-btn mf-btn-delete">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                {/foreach}
            {else}
                <div style="text-align: center; padding: 40px; color: #999;">
                    <p>Noch keine Seiten vorhanden</p>
                </div>
            {/if}
        </div>
    {/if}
</div>

<script>
{if $edit_page !== null}
// Initialize Sortable for drag & drop
const container = document.getElementById('sections-container');
if(container) {
    new Sortable(container, {
        animation: 150,
        handle: '.mf-section-header',
        ghostClass: 'sortable-ghost'
    });
}

let sectionIndex = {if $sections}{count($sections)}{else}0{/if};

function removeSection(btn) {
    if(confirm('Section wirklich entfernen?')) {
        btn.closest('.mf-section-block').remove();
    }
}

// Drag from sidebar to canvas
const sectionTypes = document.querySelectorAll('.mf-section-type');
sectionTypes.forEach(type => {
    type.addEventListener('click', function() {
        addSection(this.dataset.type);
    });
});

function addSection(type) {
    const typeInfo = {$section_types|@json_encode};
    const info = typeInfo[type];
    
    const sectionHTML = `
        <div class="mf-section-block" data-type="${type}" data-index="${sectionIndex}">
            <div class="mf-section-header">
                <h4>
                    <span>${info.icon}</span>
                    ${info.name}
                </h4>
                <div class="mf-section-actions">
                    <button type="button" onclick="removeSection(this)" style="background: #E74C3C; color: white;">
                        🗑️ Entfernen
                    </button>
                </div>
            </div>
            <div class="mf-section-content">
                <div class="mf-form-group">
                    <label>Content (JSON)</label>
                    <textarea name="sections[${sectionIndex}][content]" rows="3">{}</textarea>
                </div>
                <input type="hidden" name="sections[${sectionIndex}][type]" value="${type}">
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', sectionHTML);
    sectionIndex++;
}
{/if}
</script>
