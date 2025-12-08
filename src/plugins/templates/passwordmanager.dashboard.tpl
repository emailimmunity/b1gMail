<div class="container-fluid">
	<h1><i class="fa fa-key"></i> Password Management Pro</h1>
	
{if $success}
	<div class="alert alert-success">{$success}</div>
{/if}
{if $info}
	<div class="alert alert-info">{$info}</div>
{/if}

{* PROMINENTE MODUS-ANZEIGE *}
<div style="background: {if $current_mode == 'md5'}#fff3cd{elseif $current_mode == 'hybrid'}#cfe2ff{else}#d1e7dd{/if}; border: 2px solid {if $current_mode == 'md5'}#ffca2c{elseif $current_mode == 'hybrid'}#0d6efd{else}#198754{/if}; border-radius: 8px; padding: 20px; margin-bottom: 25px; text-align: center;">
	<div style="font-size: 14px; color: #666; margin-bottom: 8px;">
		<i class="fa fa-info-circle"></i> AKTUELLER PASSWORT-MODUS
	</div>
	<div style="font-size: 32px; font-weight: bold; color: {if $current_mode == 'md5'}#856404{elseif $current_mode == 'hybrid'}#084298{else}#0f5132{/if}; margin-bottom: 8px;">
		{if $current_mode == 'md5'}
			<i class="fa fa-exclamation-triangle"></i> MD5 ONLY
		{elseif $current_mode == 'hybrid'}
			<i class="fa fa-exchange"></i> HYBRID
		{else}
			<i class="fa fa-check-circle"></i> BCRYPT MODERN
		{/if}
	</div>
	<div style="font-size: 16px; color: #666;">
		{if $current_mode == 'md5'}
			Veralteter Modus - Nur MD5 Hashes (Sicherheitsrisiko!)
		{elseif $current_mode == 'hybrid'}
			Migrations-Modus - MD5 wird bei Login zu bcrypt konvertiert
		{else}
			Moderner Modus - Alle neuen Passwörter verwenden bcrypt
		{/if}
	</div>
</div>

	<!-- Aktueller Modus -->
	<div class="card mb-4">
		<div class="card-header">
			<h3>Aktueller Modus: 
				{if $current_mode == 'md5'}
					<span class="badge badge-warning">MD5 Legacy</span>
				{elseif $current_mode == 'hybrid'}
					<span class="badge badge-info">Hybrid (Migration aktiv)</span>
				{else}
					<span class="badge badge-success">bcrypt Modern</span>
				{/if}
			</h3>
		</div>
	</div>

	<!-- Statistiken -->
	<div class="row mb-4">
		<div class="col-md-3">
			<div class="card text-center">
				<div class="card-body">
					<h2>{$stats.total}</h2>
					<p class="text-muted">Gesamt-User</p>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card text-center bg-danger text-white">
				<div class="card-body">
					<h2>{$stats.md5} ({$stats.md5_percent}%)</h2>
					<p>MD5 (veraltet)</p>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card text-center bg-success text-white">
				<div class="card-body">
					<h2>{$stats.bcrypt} ({$stats.bcrypt_percent}%)</h2>
					<p>bcrypt (modern)</p>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card text-center">
				<div class="card-body">
					<h2>{$stats.migrations_7d}</h2>
					<p class="text-muted">Migriert (7 Tage)</p>
				</div>
			</div>
		</div>
	</div>

	<!-- Fortschrittsbalken -->
	<div class="card mb-4">
		<div class="card-header">
			<h3>Migrations-Fortschritt</h3>
		</div>
		<div class="card-body">
			<div class="progress" style="height: 30px;">
				<div class="progress-bar bg-danger" style="width: {$stats.md5_percent}%">
					MD5: {$stats.md5_percent}%
				</div>
				<div class="progress-bar bg-success" style="width: {$stats.bcrypt_percent}%">
					bcrypt: {$stats.bcrypt_percent}%
				</div>
			</div>
		</div>
	</div>

	<!-- Modus ändern -->
	<div class="card">
		<div class="card-header">
			<h3>Modus ändern</h3>
		</div>
		<div class="card-body">
			<form method="post">
				<input type="hidden" name="do" value="set_mode">
				<div class="form-group">
					<label><strong>Passwort-Modus:</strong></label>
					
					<div class="form-check">
						<input type="radio" name="mode" value="md5" 
						       class="form-check-input" id="mode_md5"
						       {if $current_mode == 'md5'}checked{/if}>
						<label class="form-check-label" for="mode_md5">
							<strong>MD5 only</strong> (Legacy) - Nur für alte Systeme!
						</label>
					</div>
					
					<div class="form-check">
						<input type="radio" name="mode" value="hybrid" 
						       class="form-check-input" id="mode_hybrid"
						       {if $current_mode == 'hybrid'}checked{/if}>
						<label class="form-check-label" for="mode_hybrid">
							<strong>Hybrid</strong> (Empfohlen) - MD5 + bcrypt mit Auto-Migration
						</label>
					</div>
					
					<div class="form-check">
						<input type="radio" name="mode" value="bcrypt" 
						       class="form-check-input" id="mode_bcrypt"
						       {if $current_mode == 'bcrypt'}checked{/if}>
						<label class="form-check-label" for="mode_bcrypt">
							<strong>bcrypt only</strong> (Modern) - MD5-User müssen Passwort zurücksetzen!
						</label>
					</div>
				</div>
				
				<div class="form-group">
					<div class="form-check">
						<input type="checkbox" name="migration_enabled" value="1" 
						       class="form-check-input" id="migration_enabled"
						       {if $migration_enabled}checked{/if}>
						<label class="form-check-label" for="migration_enabled">
							<strong>Auto-Migration aktivieren</strong> - User werden beim Login automatisch migriert
						</label>
					</div>
				</div>
				
				<button type="submit" name="submit" class="btn btn-primary">
					<i class="fa fa-save"></i> Speichern
				</button>
			</form>
		</div>
	</div>

	<!-- Empfehlungen -->
	<div class="card mt-4">
		<div class="card-header bg-info text-white">
			<h3><i class="fa fa-lightbulb"></i> Empfohlener Migrations-Plan</h3>
		</div>
		<div class="card-body">
			<ol>
				<li><strong>Phase 1:</strong> Modus auf "Hybrid" + Auto-Migration aktivieren</li>
				<li><strong>Phase 2:</strong> Einige Wochen warten (User loggen sich ein, werden migriert)</li>
				<li><strong>Phase 3:</strong> Wenn 95%+ migriert: Auf "bcrypt only" umstellen</li>
				<li><strong>Phase 4:</strong> Verbleibende MD5-User müssen Passwort zurücksetzen</li>
			</ol>
			
			<div class="alert alert-warning">
				<i class="fa fa-exclamation-triangle"></i> <strong>Wichtig:</strong><br>
				Im "bcrypt only" Modus können MD5-User sich NICHT mehr einloggen!<br>
				Sie müssen ihr Passwort über "Passwort vergessen" zurücksetzen.
			</div>
		</div>
	</div>

</div>
