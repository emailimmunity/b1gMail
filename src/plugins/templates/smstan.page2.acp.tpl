<fieldset>
	<legend>{lng p="smstan_name"}</legend>
	<table>
		<tr>
			<td width="32"><img src="../plugins/templates/images/smstan.logo.png" width="32" height="32" border="0" alt="" /></td>
			<td width="10">&nbsp;</td>
			<td><b>{lng p="smstan_name"}</b><br />{lng p="smstan_text"}</td>
		</tr>
	</table>
</fieldset>

<fieldset>
	<legend>{lng p="smstan_name"}</legend>

	<table class="list">
		<tr>
			<th width="20">&nbsp;</th>
			<th width="100">{lng p="value"}</th>
			<th width="300">{lng p="date"}</th>
			<th width="300">{lng p="ip"}</th>
			<th width="50">&nbsp;</th>
		</tr>

	{foreach from=$logs item=log}
	{cycle values="td1,td2" assign="class"}
	<tr>
		<td class="{$class}">&nbsp;</td>
		<td class="{$class}"><center>{$log.userid}</center></td>
		<td class="{$class}"><center>{date timestamp=$log.time}</center></td>
		<td class="{$class}"><center>{$log.IP}</center></td>
		<td class="{$class}">&nbsp;</td>
	</tr>
	{/foreach}

	</table>
</fieldset>