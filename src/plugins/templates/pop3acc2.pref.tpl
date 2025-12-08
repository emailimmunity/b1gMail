<fieldset>
	<legend>{lng p="pop3acc_name"}</legend>
	<table>
		<tr>
			<td width="48"><img src="../plugins/templates/images/pop3acc_logo.png" width="48" height="48" border="0" alt="" /></td>
			<td width="10">&nbsp;</td>
			<td><b>{lng p="pop3acc_name"}</b><br />{lng p="pop3acc_text"}</td>
		</tr>
	</table>
</fieldset>

<form action="plugin.page.php?plugin=pop3acc&action=page2&sid={$sid}" method="post" onsubmit="spin(this)" name="f1">
<input type="hidden" name="sortBy" id="sortBy" value="{$sortBy}" />
<input type="hidden" name="sortOrder" id="sortOrder" value="{$sortOrder}" />
<fieldset>
	<legend>{lng p="pop3acc_name"}</legend>

	<table class="list">
		<tr>
			<th width="3%">&nbsp;</th>
			<th width="3%"><a href="javascript:updateSort('id');">{lng p="id"} {if $sortBy=='id'}<img src="{$tpldir}images/sort_{$sortOrder}.png" border="0" alt="" width="7" height="6" align="absmiddle" />{/if}</a></th>
			<th width="25%"><a href="javascript:updateSort('email');">{lng p="username"} {if $sortBy=='email'}<img src="{$tpldir}images/sort_{$sortOrder}.png" border="0" alt="" width="7" height="6" align="absmiddle" />{/if}</th>
			<th width="28%">{lng p="host"}</th>
			<th width="3%">{lng p="ssl"}</th>
			<th width="16%">{lng p="keepmails"}?</th>
			<th width="17%">{lng p="lastfetch"}</th>
			<th width="5%">&nbsp;</th>
		</tr>

		{foreach from=$pop3acc item=pop3}
		{cycle name=class values="td1,td2" assign=class}
		<tr class="{$class}">
			<td><center><img src="./templates/images/ok.png" border="0" alt="{lng p="ok"}" width="16" height="16" /></center></td>
			<td align="center"><center>{$pop3.id}</center></td>
			<td><a href="users.php?do=edit&id={$pop3.id}&sid={$sid}">{$pop3.email}</a></td>
			<td align="center"><center>{$pop3.host}:{$pop3.port}</center></td>
			<td>{if $pop3.ssl==yes}<center><img src="./templates/images/ok.png" border="0" alt="{lng p="ok"}" width="16" height="16" /></center>{else}
				<center><img src="./templates/images/error.png" border="0" alt="{lng p="no"}" width="16" height="16" /></center>{/if}
				</td>
			<td>{if $pop3.keep==yes}<center><img src="./templates/images/ok.png" border="0" alt="{lng p="ok"}" width="16" height="16" /></center>{/if}
				{if $pop3.keep==no}<center><img src="./templates/images/error.png" border="0" alt="{lng p="no"}" width="16" height="16" /></center>{/if}
				</td>
			<td align="center"><center>{date timestamp=$pop3.last}</center></td>
			<td><center>
				<a href="plugin.page.php?plugin=pop3acc&action=page1&do=delete&id={$pop3.pop3_id}&sid={$sid}" onclick="return confirm('{lng p="realdel"}');"><img src="./templates/images/delete.png" border="0" alt="{lng p="delete"}" width="16" height="16" /></a>
			</center></td>
		</tr>
		{/foreach}

	</table>
</fieldset>
</form>