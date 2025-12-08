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

<form action="plugin.page.php?plugin=surbl&action=page3&do=save&sid={$sid}" method="post" onsubmit="spin(this)">	
	<fieldset>
		<legend>{lng p="surbl_blacklist"}</legend>
	
		<table width="90%">
			<tr>
				<td align="left" rowspan="3" valign="top" width="40"><img src="{$tpldir}images/antispam_dnsbl.png" border="0" alt="" width="32" height="32" /></td>
				<td class="td1" width="220">{lng p="surbl_blacklist"}:</td>
				<td class="td2">
					<textarea style="width:100%;height:200px;" name="surbl_bl">{text value=$surbl_bl allowEmpty=true}</textarea>
					<small>{lng p="sepby"}</small>
				</td>
			</tr>
		</table>
	</fieldset>
	
	<p>
		<div style="float:right">
			<input class="button" type="submit" value=" {lng p="save"} " />
		</div>
	</p>
</form>
