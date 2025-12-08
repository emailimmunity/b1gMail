{* Include Navigation *}
{include file='_navigation.tpl' currentPage='media' pageTitle='Media Library'}

<style>
.mf-media-library {
    padding: 20px;
    background: #f5f5f5;
}
.mf-toolbar {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
}
.mf-upload-btn {
    background: #76B82A;
    color: white;
    padding: 12px 24px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}
.mf-upload-btn:hover {
    background: #5D9321;
}
.mf-breadcrumb {
    display: flex;
    gap: 10px;
    align-items: center;
    color: #666;
}
.mf-breadcrumb a {
    color: #76B82A;
    text-decoration: none;
}
.mf-breadcrumb a:hover {
    text-decoration: underline;
}
.mf-media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}
.mf-media-item {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s;
    cursor: pointer;
}
.mf-media-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.mf-media-preview {
    width: 100%;
    height: 150px;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.mf-media-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
}
.mf-media-preview .icon {
    font-size: 48px;
    color: #999;
}
.mf-media-info {
    padding: 15px;
}
.mf-media-name {
    font-weight: 600;
    font-size: 14px;
    color: #333;
    margin-bottom: 5px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.mf-media-size {
    font-size: 12px;
    color: #999;
}
.mf-media-actions {
    padding: 10px 15px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
}
.mf-action-btn {
    flex: 1;
    padding: 8px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.3s;
}
.mf-action-copy {
    background: #3498DB;
    color: white;
}
.mf-action-copy:hover {
    background: #2980B9;
}
.mf-action-delete {
    background: #E74C3C;
    color: white;
}
.mf-action-delete:hover {
    background: #C0392B;
}
.mf-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.mf-modal.active {
    display: flex;
}
.mf-modal-content {
    background: white;
    padding: 30px;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
}
.mf-modal-content h3 {
    margin: 0 0 20px 0;
}
.mf-modal-content input {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    margin-bottom: 15px;
}
.mf-modal-buttons {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
.mf-success-msg {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}
.mf-error-msg {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}
</style>

<div class="mf-media-library">
    <h1>🖼️ Media Library</h1>
    
    {if $success}
        <div class="mf-success-msg">✓ {$success}</div>
    {/if}
    
    {if $error}
        <div class="mf-error-msg">✗ {$error}</div>
    {/if}
    
    <!-- Toolbar -->
    <div class="mf-toolbar">
        <button class="mf-upload-btn" onclick="document.getElementById('upload-input').click()">
            📤 Datei hochladen
        </button>
        
        <button class="mf-upload-btn" style="background: #3498DB;" onclick="openFolderModal()">
            📁 Ordner erstellen
        </button>
        
        <form method="POST" enctype="multipart/form-data" id="upload-form" style="display: none;">
            <input type="file" id="upload-input" name="media_upload" accept="image/*,application/pdf" 
                   onchange="this.form.submit()" multiple>
            <input type="hidden" name="folder_id" value="{$current_folder}">
        </form>
        
        <!-- Breadcrumb -->
        {if $breadcrumb || $current_folder}
        <div class="mf-breadcrumb" style="margin-left: auto;">
            <a href="{$pageURL}&page=media">🏠 Root</a>
            {foreach from=$breadcrumb item=folder}
                <span>→</span>
                <a href="{$pageURL}&page=media&folder={$folder.id}">{$folder.name}</a>
            {/foreach}
        </div>
        {/if}
    </div>
    
    <!-- Folders -->
    {if $folders && count($folders) > 0}
    <h2>📁 Ordner</h2>
    <div class="mf-media-grid" style="margin-bottom: 30px;">
        {foreach from=$folders item=folder}
            {if ($current_folder && $folder.parent_id == $current_folder) || (!$current_folder && !$folder.parent_id)}
            <div class="mf-media-item" onclick="window.location='{$pageURL}&page=media&folder={$folder.id}'">
                <div class="mf-media-preview">
                    <div class="icon">📁</div>
                </div>
                <div class="mf-media-info">
                    <div class="mf-media-name">{$folder.name}</div>
                    <div class="mf-media-size">Ordner</div>
                </div>
            </div>
            {/if}
        {/foreach}
    </div>
    {/if}
    
    <!-- Media Files -->
    <h2>📄 Dateien</h2>
    {if $media_files && count($media_files) > 0}
    <div class="mf-media-grid">
        {foreach from=$media_files item=file}
            <div class="mf-media-item">
                <div class="mf-media-preview">
                    {if $file.is_image}
                        <img src="{$file.filepath}" alt="{$file.original_filename}">
                    {else}
                        <div class="icon">📄</div>
                    {/if}
                </div>
                <div class="mf-media-info">
                    <div class="mf-media-name" title="{$file.original_filename}">{$file.original_filename}</div>
                    <div class="mf-media-size">{$file.size_formatted}</div>
                    {if $file.width}
                        <div class="mf-media-size">{$file.width}×{$file.height} px</div>
                    {/if}
                </div>
                <div class="mf-media-actions">
                    <button class="mf-action-btn mf-action-copy" onclick="copyURL('{$file.filepath}')" title="URL kopieren">
                        📋 URL
                    </button>
                    <button class="mf-action-btn mf-action-delete" onclick="deleteMedia({$file.id}, '{$file.original_filename}')" title="Löschen">
                        🗑️
                    </button>
                </div>
            </div>
        {/foreach}
    </div>
    {else}
        <div style="text-align: center; padding: 60px; background: white; border-radius: 8px;">
            <p style="color: #999; font-size: 18px;">📂 Keine Dateien vorhanden</p>
            <p style="color: #999;">Lade deine erste Datei hoch!</p>
        </div>
    {/if}
</div>

<!-- Folder Create Modal -->
<div class="mf-modal" id="folder-modal">
    <div class="mf-modal-content">
        <h3>📁 Neuer Ordner</h3>
        <form method="POST">
            <input type="text" name="folder_name" placeholder="Ordnername" required>
            <input type="hidden" name="parent_id" value="{$current_folder}">
            <div class="mf-modal-buttons">
                <button type="button" onclick="closeFolderModal()" style="background: #999; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">
                    Abbrechen
                </button>
                <button type="submit" name="create_folder" class="mf-upload-btn">
                    Erstellen
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Form (hidden) -->
<form method="POST" id="delete-form" style="display: none;">
    <input type="hidden" name="media_id" id="delete-media-id">
    <input type="hidden" name="delete_media" value="1">
</form>

<script>
function copyURL(url) {
    const fullURL = window.location.origin + url;
    navigator.clipboard.writeText(fullURL).then(() => {
        alert('URL kopiert: ' + fullURL);
    });
}

function deleteMedia(id, name) {
    if(confirm('Datei "' + name + '" wirklich löschen?')) {
        document.getElementById('delete-media-id').value = id;
        document.getElementById('delete-form').submit();
    }
}

function openFolderModal() {
    document.getElementById('folder-modal').classList.add('active');
}

function closeFolderModal() {
    document.getElementById('folder-modal').classList.remove('active');
}

// Close modal on outside click
document.getElementById('folder-modal').addEventListener('click', function(e) {
    if(e.target === this) {
        closeFolderModal();
    }
});
</script>
