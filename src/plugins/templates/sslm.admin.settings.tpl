<form action="{$pageURL}" method="post">

{* Success Message *}
{if isset($saveResult) && $saveResult.success}
<div class="alert alert-success alert-dismissible">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<h4><i class="fa fa-check"></i> Erfolg</h4>
	<p>{$saveResult.message}</p>
</div>
{/if}

<fieldset>
	<legend>🔐 Let's Encrypt ACME Einstellungen</legend>
	
	<div class="form-group">
		<label for="acme_email">E-Mail-Adresse für ACME Account:</label>
		<input type="email" 
		       name="acme_email" 
		       id="acme_email" 
		       class="form-control" 
		       value="{$config.ssl_acme_email|default:''}"
		       placeholder="admin@example.com">
		<small class="form-text text-muted">
			Diese E-Mail wird für den Let's Encrypt Account verwendet und erhält wichtige Benachrichtigungen.
		</small>
	</div>
	
	<div class="form-check">
		<input type="checkbox" 
		       name="acme_production" 
		       id="acme_production" 
		       class="form-check-input" 
		       value="1"
		       {if $config.ssl_acme_production}checked{/if}>
		<label class="form-check-label" for="acme_production">
			<strong>Produktions-Modus verwenden</strong>
		</label>
		<small class="form-text text-muted">
			⚠️ Wenn deaktiviert, wird der Staging-Server verwendet (empfohlen für Tests!)<br>
			Let's Encrypt hat ein Rate-Limit von 50 Zertifikaten pro Woche für die Produktion.
		</small>
	</div>
</fieldset>

<fieldset class="mt-4">
	<legend>🔄 Auto-Renewal Einstellungen</legend>
	
	<div class="form-group">
		<label for="auto_renew_days">Zertifikate erneuern X Tage vor Ablauf:</label>
		<input type="number" 
		       name="auto_renew_days" 
		       id="auto_renew_days" 
		       class="form-control" 
		       min="1" 
		       max="89" 
		       value="{$config.ssl_auto_renew_days|default:30}">
		<small class="form-text text-muted">
			Standardwert: 30 Tage. Let's Encrypt Zertifikate sind 90 Tage gültig.
		</small>
	</div>
</fieldset>

<fieldset class="mt-4">
	<legend>ℹ️ System-Informationen</legend>
	
	<table class="table table-sm">
		<tr>
			<th>ACME Directory URL (Staging):</th>
			<td><code>https://acme-staging-v02.api.letsencrypt.org/directory</code></td>
		</tr>
		<tr>
			<th>ACME Directory URL (Production):</th>
			<td><code>https://acme-v02.api.letsencrypt.org/directory</code></td>
		</tr>
		<tr>
			<th>Rate Limits (Staging):</th>
			<td>Keine strengen Limits - ideal für Tests</td>
		</tr>
		<tr>
			<th>Rate Limits (Production):</th>
			<td>50 Zertifikate pro Woche pro Haupt-Domain</td>
		</tr>
	</table>
</fieldset>

{* Buttons *}
<div class="mt-4">
	<button type="submit" name="save" class="btn btn-primary">
		<i class="fa fa-save"></i> Einstellungen speichern
	</button>
	<a href="{$pageURL|replace:'&action=settings':''}" class="btn btn-secondary">
		<i class="fa fa-arrow-left"></i> Zurück
	</a>
</div>

</form>

{* Help Box *}
<div class="alert alert-info mt-4">
	<h5><i class="fa fa-lightbulb-o"></i> 💡 Wichtige Hinweise</h5>
	<ul class="mb-0">
		<li><strong>E-Mail-Adresse:</strong> Wird für wichtige Benachrichtigungen von Let's Encrypt verwendet</li>
		<li><strong>Staging-Modus:</strong> Nutze den Staging-Server zum Testen - keine Rate-Limits!</li>
		<li><strong>Rate-Limits:</strong> Produktions-Zertifikate sind limitiert - plane voraus!</li>
		<li><strong>Auto-Renewal:</strong> Zertifikate werden automatisch erneuert, wenn aktiviert</li>
		<li><strong>Cronjob:</strong> Stelle sicher, dass der b1gMail Cronjob läuft für Auto-Renewal</li>
	</ul>
</div>
