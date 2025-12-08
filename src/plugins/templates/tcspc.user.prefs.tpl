<h1><img src="plugins/templates/images/tcspc_icon16.png" width="16" height="16" border="0" alt="" align="absmiddle" /> {lng p="tcspc_mod2"}</h1>
{if $tcspc_forced}
	{if $tcspc_firstStart}
		{lng p="tcspc_erster_start"}
	{/if}
{else}
	{lng p="prefs_d_tcspc_mod"}
{/if}
<br /><br />

{*Start: {$tcspc_space_start}<br />
Step: {$tcspc_space_step}<br />
{assign var="tcspc_stop" value=$tcspc_space_total-$tcspc_webdiskspace_spaceUsed}
Stop: {$tcspc_space_total} - {$tcspc_webdiskspace_spaceUsed} = {$tcspc_stop}<br />*}

<form name="f1" method="post" action="{$smarty.server.REQUEST_URI|escape:'html'}">
	<table class="listTable">
		<tr>
			<th class="listTableHead" colspan="3"> {lng p="tcspc_mod2"}</th>
		</tr>
		<tr>
			<td class="listTableLeft"><label for="id_automatisch">{lng p="tcspc_automatisch_verteilen"}:</label></td>
			<td class="listTableRight" colspan="2">
				<input type="checkbox" name="automatisch" id="id_automatisch" {if $tcspc_automatisch}checked="checked" {/if}value="1" onchange="tcspc_switchAuto();" />
			</td>
		</tr>
		<tr style="font-weight: bold;">
			<td class="listTableLeftDesc">&nbsp;</td>
			<td class="listTableRightDesc" width="200">{lng p="tcspc_belegt"}</td>
			<td class="listTableRightDesc">{lng p="tcspc_zugewiesen"}</td>
		</tr>
		<tr>
			<td class="listTableLeft"><label for="id_space">{lng p="space"} ({lng p="email"}):</label></td>
			<td class="listTableRight">
				{size bytes=$tcspc_mailspace_spaceUsed}
			</td>
			<td class="listTableRight">
				<select name="space" id="id_space" {if $tcspc_automatisch}style="display: none" {/if}onchange="tcspc_updateWebdiskSpace();">
					{foreach from=$tcspc_steps item=step}
						<option value="{$step}"{if $tcspc_mailspace_spaceLimit==$step} selected="selected"{/if}>{size bytes=$step}</option>
					{/foreach}
					{if $tcspc_webdiskspace_spaceUsed == 0}
						<option value="{$tcspc_space_total}"{if $tcspc_mailspace_spaceLimit==$tcspc_space_total} selected="selected"{/if}>{size bytes=$tcspc_space_total}</option>
					{/if}
				</select>
			</td>
		</tr>
		<tr>
			<td class="listTableLeft">{lng p="space"} ({lng p="webdisk"}):</td>
			<td class="listTableRight">
				{size bytes=$tcspc_webdiskspace_spaceUsed}
			</td>
			<td class="listTableRight">
				<span {if $tcspc_automatisch}style="display: none" {/if}id="webdiskspace_spaceLimit">{size bytes=$tcspc_webdiskspace_spaceLimit}</span>
			</td>
		</tr>
		<tr style="font-weight: bold;">
			<td class="listTableLeft">{lng p="tcspc_gesamt"}:</td>
			<td class="listTableRight">
				{size bytes=$tcspc_webdiskspace_spaceUsed+$tcspc_mailspace_spaceUsed}
			</td>
			<td class="listTableRight">
				{size bytes=$tcspc_space_total}
			</td>
		</tr>
		<tr>
			<td class="listTableLeft">&nbsp;</td>
			<td class="listTableRight" colspan="2">
				<input type="submit" value="{lng p='ok'}" />
				<input type="reset" value="{lng p='reset'}" onclick="tcspc_updateWebdiskSpace();" />
			</td>
		</tr>
	</table>
</form>

<script type="text/javascript">
function tcspc_updateWebdiskSpace() {ldelim}
	EBID('webdiskspace_spaceLimit').innerHTML = (NiceSize({$tcspc_space_total} - parseInt(EBID('id_space').value)));
{rdelim}

function tcspc_switchAuto() {ldelim}
	EBID('id_space').style.display = '';
	EBID('webdiskspace_spaceLimit').style.display = '';
	if(EBID('id_automatisch').checked) {ldelim}
		EBID('id_space').style.display = 'none';
		EBID('webdiskspace_spaceLimit').style.display = 'none';
	{rdelim}
{rdelim}

tcspc_updateWebdiskSpace();
tcspc_switchAuto();
</script>