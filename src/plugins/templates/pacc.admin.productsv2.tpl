{* 
 * PremiumAccount V2 Products Template
 * Template: pacc.admin.productsv2.tpl
 * 
 * Zeigt die neue Produktverwaltung (V2) im Admin-Bereich
 *}

<div class="product-v2-admin">
	
	{* Header mit Statistiken *}
	<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 12px; color: white; margin-bottom: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
		<h1 style="margin: 0 0 20px 0; font-size: 28px;">🎁 Produkte V2 (Neues System)</h1>
		
		<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
			<div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px;">
				<div style="font-size: 32px; font-weight: bold;">{$stats.products}</div>
				<div style="opacity: 0.9;">Aktive Produkte</div>
			</div>
			<div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px;">
				<div style="font-size: 32px; font-weight: bold;">{$stats.features}</div>
				<div style="opacity: 0.9;">Verfügbare Features</div>
			</div>
			<div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px;">
				<div style="font-size: 32px; font-weight: bold;">{$stats.subscriptions}</div>
				<div style="opacity: 0.9;">Aktive Abos (V2)</div>
			</div>
			<div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px;">
				<div style="font-size: 32px; font-weight: bold;">{$stats.legacy_subs}</div>
				<div style="opacity: 0.9;">Legacy Abos (V1)</div>
			</div>
		</div>
	</div>
	
	{* Info-Banner *}
	<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
		<h3 style="margin: 0 0 10px 0; color: #856404;">⚠️ Hinweis: V2-System in Entwicklung</h3>
		<p style="margin: 0; color: #856404;">
			Das neue Produktsystem (V2) ist parallel zum Legacy-System aktiv.<br>
			<strong>Legacy-Pakete:</strong> {$stats.legacy_packages} | 
			<strong>Neue Produkte:</strong> {$stats.products}<br>
			<a href="../admin/products.php" target="_blank" style="color: #007bff;">➜ Zur vollständigen Produktverwaltung</a>
		</p>
	</div>
	
	{* Produktliste *}
	{if $products|@count > 0}
	<div class="product-list">
		<h2>Produktübersicht</h2>
		
		<table class="border" style="width: 100%; margin-top: 10px;">
			<thead>
				<tr>
					<th width="5%">ID</th>
					<th width="25%">Produktname</th>
					<th width="15%">Kategorie</th>
					<th width="10%">Preis</th>
					<th width="10%">Billing</th>
					<th width="10%">Gruppe</th>
					<th width="10%">Features</th>
					<th width="10%">Status</th>
					<th width="5%">Aktionen</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$products item=product}
				<tr class="{if $product.enabled == 0}disabled{/if} {if $product.featured}featured{/if}">
					<td>{$product.product_id}</td>
					<td>
						<strong>{$product.product_name}</strong><br>
						<small style="color: #666;">{$product.product_key}</small>
						{if $product.featured}<span style="color: #ffc107;">⭐</span>{/if}
					</td>
					<td>{$product.product_category}</td>
					<td>{$product.base_price} {$product.currency}</td>
					<td>{$product.billing_period}</td>
					<td>Gruppe {$product.target_group}</td>
					<td>
						<span class="badge">{$product.feature_count} Features</span>
					</td>
					<td>
						{if $product.enabled}
							<span style="color: green;">✓ Aktiv</span>
						{else}
							<span style="color: red;">✗ Deaktiviert</span>
						{/if}
					</td>
					<td>
						<a href="../admin/products.php?action=edit&id={$product.product_id}" title="Bearbeiten">
							<img src="../admin/images/edit.png" border="0" alt="Edit" />
						</a>
					</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	{else}
	<div style="background: #f8f9fa; padding: 40px; text-align: center; border-radius: 8px;">
		<h3>Keine V2-Produkte gefunden</h3>
		<p>Es wurden noch keine Produkte im neuen System angelegt.</p>
		<a href="../admin/products.php?action=add" class="button">
			<img src="../admin/images/add.png" border="0" alt="Add" /> Neues Produkt anlegen
		</a>
	</div>
	{/if}
	
	{* Schnellzugriff *}
	<div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
		<h3>Schnellzugriff</h3>
		<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
			<a href="../admin/products.php" class="button" style="display: block; text-align: center; padding: 15px;">
				📦 Produktverwaltung (Vollansicht)
			</a>
			<a href="../admin/products.php?action=features" class="button" style="display: block; text-align: center; padding: 15px;">
				⚙️ Feature-Verwaltung
			</a>
			<a href="{$pageURL}&action=packages" class="button" style="display: block; text-align: center; padding: 15px;">
				📋 Legacy-Pakete (V1)
			</a>
		</div>
	</div>
	
	{* Info-Box *}
	<div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-left: 4px solid #007bff; border-radius: 4px;">
		<h4 style="margin: 0 0 10px 0; color: #004085;">ℹ️ V1 vs V2 Unterschiede</h4>
		<ul style="margin: 0; padding-left: 20px;">
			<li><strong>V1 (Legacy):</strong> Alte Pakete in <code>mod_premium_packages</code>, gruppiert nach Preisen</li>
			<li><strong>V2 (Neu):</strong> Flexible Produkte in <code>products</code>, Feature-basiert, konfigurierbar</li>
			<li><strong>Kompatibilität:</strong> Beide Systeme laufen parallel, Bestellungen werden automatisch dem richtigen System zugeordnet</li>
		</ul>
	</div>
	
</div>

<style>
.product-v2-admin .disabled {
	opacity: 0.5;
}
.product-v2-admin .featured {
	background: #fffbf0;
}
.product-v2-admin .badge {
	background: #007bff;
	color: white;
	padding: 3px 8px;
	border-radius: 12px;
	font-size: 11px;
}
.product-v2-admin table.border th {
	background: #f8f9fa;
	font-weight: bold;
}
</style>
