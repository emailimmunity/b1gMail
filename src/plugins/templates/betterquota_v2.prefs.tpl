<h1><img src="plugins/templates/images/betterquota_icon16.png" width="16" height="16" border="0" alt="" align="absmiddle" /> {lng p="betterquota_v2"}</h1>

{lng p="prefs_d_betterquota_v2"}<br /><br />

<form name="f1" method="post" action="{$smarty.server.REQUEST_URI|escape:'html'}">
	<table class="listTable">
		<tr>
			<th class="listTableHead" colspan="3">{lng p="betterquota_v2"}</th>
		</tr>
		
		{if $bq2_group_mode == 2}
		<!-- Auto-Only Modus (von Admin erzwungen) -->
		<tr>
			<td class="listTableLeft" colspan="3">
				<div style="padding: 10px; background: #e8f5e9; border-left: 3px solid #4caf50;">
					<strong>Automatischer Modus:</strong><br />
					Der Speicher wird automatisch zwischen E-Mail und WebDisk aufgeteilt.
					Sie müssen nichts einstellen.
				</div>
			</td>
		</tr>
		{else}
		<!-- Benutzerdefinierter Modus -->
		<tr>
			<td class="listTableLeft"><label for="id_mode_auto">Automatisch verteilen:</label></td>
			<td class="listTableRight" colspan="2">
				<input type="checkbox" name="mode_auto" id="id_mode_auto" value="1" {if $bq2_mode_auto}checked="checked"{/if} onchange="bq2_toggleMode();" />
				<small style="color: #666;">System verwaltet Speicher dynamisch</small>
			</td>
		</tr>
		
		<tr style="font-weight: bold;">
			<td class="listTableLeftDesc">&nbsp;</td>
			<td class="listTableRightDesc" width="200">Belegt</td>
			<td class="listTableRightDesc">Zugewiesen</td>
		</tr>
		
		<tr>
			<td class="listTableLeft"><label for="id_mail_quota">📧 E-Mail:</label></td>
			<td class="listTableRight">
				{size bytes=$bq2_mail_used}
			</td>
			<td class="listTableRight">
				<select name="mail_quota" id="id_mail_quota" {if $bq2_mode_auto}style="display: none"{/if} onchange="bq2_updateWebdisk();">
					{foreach from=$bq2_steps item=step}
						<option value="{$step}"{if $bq2_mail_quota==$step} selected="selected"{/if}>{size bytes=$step}</option>
					{/foreach}
				</select>
				<span id="mail_quota_auto" {if !$bq2_mode_auto}style="display: none"{/if}>{size bytes=$bq2_total_quota}</span>
			</td>
		</tr>
		
		<tr>
			<td class="listTableLeft">💾 WebDisk:</td>
			<td class="listTableRight">
				{size bytes=$bq2_webdisk_used}
			</td>
			<td class="listTableRight">
				<span id="webdisk_quota_display">{size bytes=$bq2_webdisk_quota}</span>
			</td>
		</tr>
		
		<tr style="font-weight: bold; background: #f5f5f5;">
			<td class="listTableLeft">📊 Gesamt:</td>
			<td class="listTableRight">
				{size bytes=$bq2_mail_used+$bq2_webdisk_used}
			</td>
			<td class="listTableRight">
				{size bytes=$bq2_total_quota}
			</td>
		</tr>
		{/if}
		
		<tr>
			<td class="listTableLeft">&nbsp;</td>
			<td class="listTableRight" colspan="2">
				<input type="submit" value="{lng p='ok'}" />
				<input type="reset" value="{lng p='reset'}" onclick="bq2_updateWebdisk();" />
			</td>
		</tr>
		
		{if $bq2_group_mode != 2}
		<tr>
			<td class="listTableLeft">&nbsp;</td>
			<td class="listTableRight" colspan="2">
				<small style="color: #666;">
					💡 <strong>Tipp:</strong> Im automatischen Modus teilen sich E-Mail und WebDisk den Speicher dynamisch.
					Im manuellen Modus können Sie eine feste Aufteilung wählen.
				</small>
			</td>
		</tr>
		{/if}
	</table>
</form>

<script type="text/javascript">
var bq2_totalQuota = {$bq2_total_quota};
var bq2_webdiskUsed = {$bq2_webdisk_used};

function bq2_updateWebdisk() {
	var mailSelect = document.getElementById('id_mail_quota');
	var webdiskDisplay = document.getElementById('webdisk_quota_display');
	
	if (mailSelect && webdiskDisplay) {
		var mailQuota = parseInt(mailSelect.value);
		var webdiskQuota = bq2_totalQuota - mailQuota;
		webdiskDisplay.innerHTML = NiceSize(webdiskQuota);
	}
}

function bq2_toggleMode() {
	var checkbox = document.getElementById('id_mode_auto');
	var mailSelect = document.getElementById('id_mail_quota');
	var mailAuto = document.getElementById('mail_quota_auto');
	var webdiskDisplay = document.getElementById('webdisk_quota_display');
	
	if (checkbox.checked) {
		// Auto-Modus
		if (mailSelect) mailSelect.style.display = 'none';
		if (mailAuto) mailAuto.style.display = '';
		if (webdiskDisplay) webdiskDisplay.innerHTML = NiceSize(bq2_totalQuota);
	} else {
		// Manuell-Modus
		if (mailSelect) mailSelect.style.display = '';
		if (mailAuto) mailAuto.style.display = 'none';
		bq2_updateWebdisk();
	}
}

// Init
bq2_updateWebdisk();
bq2_toggleMode();
</script>
