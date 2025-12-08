{literal}
<script type="text/javascript">
function switchlayer(Layer_Name) {
	var GECKO = document.getElementById? 1:0 ;
	var NS = document.layers? 1:0 ;
	var IE = document.all? 1:0 ;
	if (GECKO){
		document.getElementById(Layer_Name).style.display= (document.getElementById(Layer_Name).style.display=='block') ? 'none' : 'block';
		document.getElementById('button1_'+Layer_Name).style.display= (document.getElementById(Layer_Name).style.display=='block') ? 'none' : 'block';
		document.getElementById('button2_'+Layer_Name).style.display= (document.getElementById(Layer_Name).style.display=='block') ? 'block' : 'none';
	}else if (NS){
		document.layers[Layer_Name].display=(document.layers[Layer_Name].display== 'block') ? 'none' : 'block';
		document.layers['button1_'+Layer_Name].display=(document.layers['button1_'+Layer_Name].display== 'block') ? 'none' : 'block';
		document.layers['button2_'+Layer_Name].display=(document.layers['button2_'+Layer_Name].display== 'block') ? 'block' : 'none';
	}else if (IE){
		document.all[Layer_Name].style.display=(document.all[Layer_Name].style.display== 'block') ? 'none' : 'block';
		document.all['button1_'+Layer_Name].style.display=(document.all['button1_'+Layer_Name].style.display== 'block') ? 'none' : 'block';
		document.all['button2_'+Layer_Name].style.display=(document.all['button2_'+Layer_Name].style.display== 'block') ? 'block' : 'none';
	}
}
</script>
{/literal}

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

<form action="plugin.page.php?plugin=pop3acc&action=page1&sid={$sid}" method="post" onsubmit="spin(this)" name="f1">
<input type="hidden" name="sortBy" id="sortBy" value="{$sortBy}" />
<input type="hidden" name="sortOrder" id="sortOrder" value="{$sortOrder}" />
<fieldset>
	<legend>{lng p="pop3acc_name"}</legend>
	<table class="list">
		<tr>
			<th width="3%">&nbsp;</th>
			<th width="3%"><a href="javascript:updateSort('id');">{lng p="id"} {if $sortBy=='id'}<img src="{$tpldir}images/sort_{$sortOrder}.png" border="0" alt="" width="7" height="6" align="absmiddle" />{/if}</a></th>
			<th width="25%"><a href="javascript:updateSort('email');">{lng p="username"} {if $sortBy=='email'}<img src="{$tpldir}images/sort_{$sortOrder}.png" border="0" alt="" width="7" height="6" align="absmiddle" />{/if}</th>
			<th width="25%">{lng p="host"}</th>
			<th width="3%">{lng p="ssl"}</th>
			<th width="16%">{lng p="keepmails"}?</th>
			<th width="15%">{lng p="lastfetch"}</th>
			<th width="5%">{lng p="count"}</th>
			<th width="5%">&nbsp;</th>
		</tr>
	</table>

	{foreach from=$pop3user item=user}
	{cycle name=class values="td1,td2" assign=class}

	<table class="list">
		<tr class="{$class}">
			<td width="3%"><center><a href="#" onclick="javascript:switchlayer('{$user.id}'); return false;">
			<div id="button1_{$user.id}" style="display:block;"><img src="./templates/images/expand.gif" border="0"/></div>
			<div id="button2_{$user.id}" style="display:none;"><img src="./templates/images/contract.gif" border="0"/></div>
			</a></center></td>
			<td width="3%" align="center"><center>{$user.id}</center></td>
			<td width="25%"><a href="users.php?do=edit&id={$user.id}&sid={$sid}">{$user.email}</a></td>
			<td width="25%" align="center"></td>
			<td width="3%"></td>
			<td width="16%"></td>
			<td width="15%" align="center"></td>
			<td width="5%" align="center">{$user.count} / {$user.gruppe_p}</td>
			<td width="5%"></td>
		</tr>
	</table>

	<div id="{$user.id}" style="display:none;">
		<table class="list">
		{foreach from=$pop3acc item=acc}
			{if $user.id == $acc.user_id}	
				<tr class="{$class}">
					<td width="3%"></td>
					<td width="3%" align="center"><center>{$user.id}</center></td>
					<td width="25%"><a href="users.php?do=edit&id={$user.id}&sid={$sid}">{$acc.user}</a></td>
					<td width="25%" align="center">{$acc.host} : {$acc.port}</td>
					<td width="3%">{if $acc.ssl==yes}<center><img src="./templates/images/ok.png" border="0" alt="{lng p="ok"}" width="16" height="16" /></center>{else}
						<center><img src="./templates/images/error.png" border="0" alt="{lng p="no"}" width="16" height="16" /></center>{/if}
					</td>
					<td width="16%">{if $acc.keep==yes}<center><img src="./templates/images/ok.png" border="0" alt="{lng p="ok"}" width="16" height="16" /></center>{/if}
						{if $acc.keep==no}<center><img src="./templates/images/error.png" border="0" alt="{lng p="no"}" width="16" height="16" /></center>{/if}
					</td>
					<td width="15%">{date timestamp=$acc.last}</td>
					<td width="5%"></td>
					<td width="5%"><center>
						<a href="plugin.page.php?plugin=pop3acc&action=page1&do=delete&id={$acc.id}&sid={$sid}" onclick="return confirm('{lng p="realdel"}');"><img src="./templates/images/delete.png" border="0" alt="{lng p="delete"}" width="16" height="16" /></a>
					</center></td>
				</tr>
			{/if}
		{/foreach}
		</table>
	</div>

	{/foreach}
</fieldset>
</form>