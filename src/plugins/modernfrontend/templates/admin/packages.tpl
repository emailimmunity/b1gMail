{include file="_header.tpl" title="Package Builder" icon="package"}

<div class="modern-admin-container">
    {if $success}
    <div class="alert alert-success">{$success}</div>
    {/if}
    
    {if $error}
    <div class="alert alert-danger">{$error}</div>
    {/if}
    
    <div class="admin-header">
        <h1>📦 Package Builder</h1>
        <button class="btn btn-primary" onclick="showPackageModal()">
            <i class="lucide-plus"></i> Neues Package
        </button>
    </div>
    
    <div class="packages-grid">
        {foreach from=$packages item=package}
        <div class="package-card">
            <div class="package-header">
                <h3>{$package.name}</h3>
                <span class="badge badge-{if $package.status=='active'}success{else}secondary{/if}">
                    {$package.status}
                </span>
            </div>
            
            <div class="package-price">
                <span class="price">{$package.price}€</span>
                <span class="duration">/ {$package.duration} Tage</span>
            </div>
            
            <div class="package-description">
                {$package.description}
            </div>
            
            <div class="package-features">
                <h4>Features:</h4>
                <ul>
                    {foreach from=$package.features item=feature}
                    <li>{$feature}</li>
                    {/foreach}
                </ul>
            </div>
            
            <div class="package-actions">
                <button class="btn btn-sm btn-primary" onclick="editPackage({$package.id})">
                    <i class="lucide-edit"></i> Bearbeiten
                </button>
                <button class="btn btn-sm btn-danger" onclick="deletePackage({$package.id})">
                    <i class="lucide-trash"></i> Löschen
                </button>
            </div>
        </div>
        {/foreach}
    </div>
</div>

<style>
.packages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.package-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
}

.package-price {
    font-size: 24px;
    font-weight: bold;
    color: #76B82A;
    margin: 15px 0;
}

.package-features ul {
    list-style: none;
    padding: 0;
}

.package-features li:before {
    content: "✓ ";
    color: #76B82A;
}
</style>

{include file="_footer.tpl"}
