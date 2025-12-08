<fieldset>
	<legend>{lng p="surbl_name"}</legend>

	<table>
		<tr>
			<td width="48"><img src="../plugins/templates/images/surbl_logo.png" width="48" height="48" border="0" alt="" /></td>
			<td width="10">&nbsp;</td>
			<td><b>{lng p="surbl_name"}</b><br>{lng p="surbl_text"}</td>
		</tr>
	</table>
</fieldset>

<form action="plugin.page.php?plugin=surbl&action=page1&do=save&sid={$sid}" method="post" onsubmit="spin(this)">	
	<fieldset>
		<legend>{lng p="surbl_filter"}</legend>
	
		<table width="90%">
			<tr>
				<td align="left" rowspan="4" valign="top" width="40"><img src="{$tpldir}images/antispam_dnsbl.png" border="0" alt="" width="32" height="32" /></td>
				<td class="td1" width="220">{lng p="enable"}?</td>
				<td class="td2"><input name="surbl_aktiv"{if $surbl_aktiv} checked="checked"{/if} type="checkbox" /></td>
			</tr>
			<tr>
				<td class="td1">{lng p="surbl_server"}:</td>
				<td class="td2">
					<textarea style="width:100%;height:80px;" name="surbl_bl">{text value=$surbl_bl allowEmpty=true}</textarea>
					<small>{lng p="sepby"}</small>
				</td>
			</tr>
			<tr>
				<td class="td1">{lng p="surbl_required"}:</td>
				<td class="td2"><input type="text" name="surbl_required" value="{$surbl_required}" size="6" /></td>
			</tr>
			<tr>
				<td class="td1">{lng p="entries"}:</td>
				<td class="td2">{$surblCount} {lng p="entries"} <input{if $surblCount==0} disabled="disabled"{/if} class="button" type="button" value=" {lng p="reset"} " onclick="if(confirm('{lng p="realdel"}')) document.location.href='plugin.page.php?plugin=surbl&action=page1&do=reset&sid={$sid}';" /></td>
			</tr>
		</table>
	</fieldset>
	
	<p>
		<div style="float:right">
			<input class="button" type="submit" value=" {lng p="save"} " />
		</div>
	</p>
</form>
