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

	<form action="plugin.page.php?plugin=smstan_plugin&action=page1&do=save&sid={$sid}" method="post" onsubmit="editor.submit();spin(this);">

		<table width="100%">
			<tr>
			<td class="td1" width="150">{lng p="groups"}:</td>
			<td class="td2"><select name="smstype" style="width: 458px;">
				<option value="0" {if $smstype == 0}selected="selected" {/if}>{lng p="defaulttype"}</option>
				{foreach from=$allsmstype item=allsms}
					<option value="{$allsms.id}" {if $smstype == $allsms.id}selected="selected" {/if}>{text value=$allsms.title}</option>
				{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<td class="td1" width="300">{lng p="smsfrom"}:</td>
			<td class="td2"><input type="text" value="{$fromno}" name="fromno" style="width: 450px;"></td>
		</tr>
		<tr>
			<td class="td1" width="300">{lng p="smstan_abbuchen"}?</td>
			<td class="td2"><input type="checkbox" name="chargefromuser" {if $chargefromuser}checked="checked" {/if}/></td>
		</tr>
		</table>
		<p align="right">
			<input class="button" type="submit" value=" {lng p="save"} "/>
		</p>	
	</form>
</fieldset>