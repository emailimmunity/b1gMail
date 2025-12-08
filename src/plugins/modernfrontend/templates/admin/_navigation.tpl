{* ModernFrontend Plugin Navigation *}
<style>
.mf-plugin-nav {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 15px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}
.mf-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}
.mf-breadcrumb a {
    color: #0066cc;
    text-decoration: none;
}
.mf-breadcrumb a:hover {
    text-decoration: underline;
}
.mf-breadcrumb-separator {
    color: #6c757d;
}
.mf-breadcrumb-current {
    color: #495057;
    font-weight: 500;
}
.mf-nav-links {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.mf-nav-link {
    padding: 6px 12px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    color: #495057;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s;
}
.mf-nav-link:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}
.mf-nav-link.active {
    background: #76B82A;
    color: white;
    border-color: #76B82A;
}
</style>

<div class="mf-plugin-nav">
    <div class="mf-breadcrumb">
        <a href="index.php">🏠 Admin</a>
        <span class="mf-breadcrumb-separator">›</span>
        <a href="plugins.php">Plugins</a>
        <span class="mf-breadcrumb-separator">›</span>
        <a href="{$pageURL}">ModernFrontend CMS</a>
        {if isset($currentPage) && $currentPage != 'dashboard'}
            <span class="mf-breadcrumb-separator">›</span>
            <span class="mf-breadcrumb-current">{$pageTitle|default:$currentPage}</span>
        {/if}
    </div>
    
    <div class="mf-nav-links">
        <a href="{$pageURL}" class="mf-nav-link {if !isset($currentPage) || $currentPage == 'dashboard'}active{/if}">
            📊 Dashboard
        </a>
        <a href="{$pageURL}&page=content" class="mf-nav-link {if isset($currentPage) && $currentPage == 'content'}active{/if}">
            📝 Content
        </a>
        <a href="{$pageURL}&page=theme" class="mf-nav-link {if isset($currentPage) && $currentPage == 'theme'}active{/if}">
            🎨 Theme
        </a>
        <a href="{$pageURL}&page=media" class="mf-nav-link {if isset($currentPage) && $currentPage == 'media'}active{/if}">
            🖼️ Media
        </a>
        <a href="{$pageURL}&page=pagebuilder" class="mf-nav-link {if isset($currentPage) && $currentPage == 'pagebuilder'}active{/if}">
            🔨 Pages
        </a>
        <a href="{$pageURL}&page=settings" class="mf-nav-link {if isset($currentPage) && $currentPage == 'settings'}active{/if}">
            ⚙️ Settings
        </a>
    </div>
</div>
