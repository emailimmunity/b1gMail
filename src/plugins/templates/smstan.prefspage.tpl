<div id="contentHeader">
	<div class="left">
		<img width="16" height="16" border="0" align="absmiddle" alt="" src="./plugins/templates/images/smstan.logo.16.png"> {lng p="smstan"}
	</div>
</div>

<div class="scrollContainer"><div class="pad">

<form name="f1" method="post" action="prefs.php?action=smstan&do=save&sid={$sid}">
	<table class="listTable">
		<tr>
			<th class="listTableHead" colspan="2"> {lng p="smstan"}</th>
		</tr>
		
		<tr>
			<td class="listTableLeftDesc"><img src="./plugins/templates/images/smstan.logo.16.png" width="16" height="16" border="0" alt="" /></td>
			<td class="listTableRightDesc">{lng p="smstan"}</td>
		</tr>
		<tr>
			<td class="listTableLeft"><label for="smstan_allow">{lng p="enable"}?</label></td>
			<td class="listTableRight">
				<input type="checkbox" name="smstan_allow" id="smstan_allow"{if $smstan_allow} checked="checked"{/if} />
			</td>
		</tr>
		{if $chargefromuser}
		<tr>
			<td class="listTableLeft"></td>
			<td class="listTableRight">{$pricetext}</td>
		</tr>
		{/if}		
		<tr>
			<td class="listTableLeft">&nbsp;</td>
			<td class="listTableRight">
				<input type="submit" value="{lng p="ok"}" />
				<input type="reset" value="{lng p="reset"}" />
			</td>
		</tr>
</table>
</form>
<br/>
{if $logs}
<table class="listTable">
	<tr>
		<th class="listTableHead" width="55">&nbsp;</th>
		<th class="listTableHead">{lng p="date"}</th>
		<th class="listTableHead">{lng p="ip"}</th>
		<th class="listTableHead" width="55">&nbsp;</th>
	</tr>

	<tbody class="listTBody">
	{foreach from=$logs item=log}
	{cycle values="listTableTD,listTableTD2" assign="class"}
	<tr>
		<td class="{$class}">&nbsp;</td>
		<td class="{$class}"><center>{date timestamp=$log.time}</center></td>
		<td class="{$class}"><center>{$log.IP}</center></td>
		<td class="{$class}">&nbsp;</td>
	</tr>
	{/foreach}
	</tbody>
</table>
{/if}

	</div>
</div>