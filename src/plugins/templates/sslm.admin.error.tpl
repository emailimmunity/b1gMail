<div class="alert alert-danger">
	<h4><i class="fa fa-exclamation-triangle"></i> Fehler</h4>
	<p>{$error}</p>
	
	<hr>
	
	<h5>Mögliche Lösungen:</h5>
	<ul>
		<li>Stelle sicher, dass die Datei <code>serverlib/ssl-manager.inc.php</code> existiert</li>
		<li>Stelle sicher, dass die Datei <code>serverlib/acme-client.inc.php</code> existiert</li>
		<li>Prüfe die Dateirechte (sollten lesbar sein)</li>
		<li>Prüfe die Logs in <code>/var/log/apache2/error.log</code></li>
	</ul>
	
	<a href="index.php?sid={$sid}" class="btn btn-primary">
		<i class="fa fa-arrow-left"></i> Zurück zum Dashboard
	</a>
</div>
