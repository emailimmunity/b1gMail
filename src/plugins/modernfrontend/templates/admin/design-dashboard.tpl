{* Enhanced Design Dashboard *}
{include file='_navigation.tpl' currentPage='design-dashboard' pageTitle='Design Dashboard'}

<style>
.design-dashboard { padding: 20px; max-width: 1400px; }
.design-hero { background: linear-gradient(135deg, #76B82A 0%, #5D9321 100%); color: white; padding: 40px; border-radius: 12px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
.design-hero-info h1 { margin: 0 0 10px 0; font-size: 28px; }
.design-hero-info p { margin: 0; opacity: 0.9; font-size: 16px; }
.design-hero-preview { background: white; padding: 20px; border-radius: 8px; text-align: center; min-width: 200px; }
.design-color-swatches { display: flex; gap: 10px; margin-top: 10px; }
.color-swatch { width: 50px; height: 50px; border-radius: 8px; border: 2px solid white; }
.design-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
.stat-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
.stat-card h3 { margin: 0 0 10px 0; color: #666; font-size: 14px; text-transform: uppercase; }
.stat-card .value { font-size: 42px; font-weight: bold; color: #76B82A; }
.aikq-showcase { background: linear-gradient(to right, #f8f9fa, #ffffff); padding: 30px; border-radius: 12px; border: 2px solid #76B82A; margin-bottom: 30px; }
.aikq-showcase h2 { margin: 0 0 20px 0; color: #76B82A; display: flex; align-items: center; gap: 10px; }
.aikq-showcase h2::before { content: "⭐"; font-size: 24px; }
.design-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
.design-card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s; }
.design-card:hover { transform: translateY(-5px); box-shadow: 0 4px 16px rgba(0,0,0,0.15); }
.design-card-preview { height: 150px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 48px; }
.design-card-info { padding: 15px; }
.design-card-info h4 { margin: 0 0 5px 0; color: #333; }
.design-card-info p { margin: 0 0 10px 0; color: #666; font-size: 13px; }
.design-card-colors { display: flex; gap: 5px; margin-bottom: 10px; }
.mini-swatch { width: 30px; height: 30px; border-radius: 4px; }
.design-card-actions { display: flex; gap: 5px; }
.btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; transition: all 0.2s; }
.btn-primary { background: #76B82A; color: white; }
.btn-primary:hover { background: #5D9321; }
.btn-secondary { background: #f0f0f0; color: #333; }
.btn-secondary:hover { background: #e0e0e0; }
.badge { display: inline-block; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; }
.badge-active { background: #28a745; color: white; }
.badge-aikq { background: #76B82A; color: white; }
</style>

<div class="design-dashboard">
    {if $activeDesign}
    <div class="design-hero">
        <div class="design-hero-info">
            <h1>🎨 {$activeDesign.name}</h1>
            <p>Currently active design across {$domainsCount} domain(s)</p>
            <div class="design-color-swatches">
                <div class="color-swatch" style="background: {$activeDesign.primary_color};" title="Primary: {$activeDesign.primary_color}"></div>
                <div class="color-swatch" style="background: {$activeDesign.secondary_color};" title="Secondary: {$activeDesign.secondary_color}"></div>
            </div>
        </div>
        <div class="design-hero-preview">
            <div style="color: #333; margin-bottom: 15px;"><strong>{$activeDesign.name}</strong></div>
            <button class="btn btn-secondary" onclick="window.open('/', '_blank')">👁️ Preview Live</button>
            <button class="btn btn-primary" onclick="location.href='{$pageURL}&page=theme'">✏️ Edit Theme</button>
        </div>
    </div>
    {/if}

    <div class="design-stats-grid">
        <div class="stat-card"><h3>Total Designs</h3><div class="value">{$designStats.total}</div></div>
        <div class="stat-card"><h3>⭐ aikQ Designs</h3><div class="value" style="color:#76B82A;">{$designStats.aikq_designs}</div></div>
        <div class="stat-card"><h3>Custom Designs</h3><div class="value" style="color:#0066cc;">{$designStats.custom_designs}</div></div>
        <div class="stat-card"><h3>Active Designs</h3><div class="value" style="color:#28a745;">{$designStats.active}</div></div>
    </div>

    <div class="aikq-showcase">
        <h2>aikQ Official Designs - Premium Quality</h2>
        <p style="margin-bottom: 20px; color: #666;">Professionally crafted designs by the aikQ team. Optimized for performance, accessibility, and conversion.</p>
        <div class="design-grid">
            {foreach from=$aikqDesigns item=design}
            <div class="design-card">
                <div class="design-card-preview" style="background: {$design.primary_color}20;">🎨</div>
                <div class="design-card-info">
                    <h4>{$design.name}
                        {if $design.is_active}<span class="badge badge-active">ACTIVE</span>{/if}
                        <span class="badge badge-aikq">aikQ</span>
                    </h4>
                    <p>{$design.description|default:'Professional aikQ design'}</p>
                    <div class="design-card-colors">
                        <div class="mini-swatch" style="background: {$design.primary_color};"></div>
                        <div class="mini-swatch" style="background: {$design.secondary_color};"></div>
                    </div>
                    <div class="design-card-actions">
                        <button class="btn btn-primary btn-sm" onclick="location.href='{$pageURL}&page=designs&do=edit&id={$design.id}'">✏️ Edit</button>
                        <button class="btn btn-secondary btn-sm" onclick="window.open('/?design_preview={$design.id}', '_blank')">👁️ Preview</button>
                    </div>
                </div>
            </div>
            {/foreach}
        </div>
    </div>

    <div style="margin-top: 40px;">
        <h2 style="margin-bottom: 20px;">📁 All Designs ({$designStats.total})</h2>
        <div class="design-grid">
            {foreach from=$allDesigns item=design}
            <div class="design-card">
                <div class="design-card-preview" style="background: {$design.primary_color}20;">{if strpos($design.template_path,'aikq')!==false}⭐{else}🎨{/if}</div>
                <div class="design-card-info">
                    <h4>{$design.name}{if $design.is_active}<span class="badge badge-active">ACTIVE</span>{/if}</h4>
                    <p>{$design.description|truncate:60}</p>
                    <div class="design-card-colors">
                        <div class="mini-swatch" style="background: {$design.primary_color};"></div>
                        <div class="mini-swatch" style="background: {$design.secondary_color};"></div>
                    </div>
                    <div class="design-card-actions">
                        <button class="btn btn-primary btn-sm" onclick="activateDesign({$design.id})">{if $design.is_active}✓ Active{else}Activate{/if}</button>
                        <button class="btn btn-secondary btn-sm" onclick="location.href='{$pageURL}&page=designs&do=edit&id={$design.id}'">✏️ Edit</button>
                    </div>
                </div>
            </div>
            {/foreach}
        </div>
    </div>
</div>

<script>
function activateDesign(designId){ if(confirm('Activate this design?')){ location.href='{$pageURL}&page=designs&do=activate&id='+designId; } }
</script>
