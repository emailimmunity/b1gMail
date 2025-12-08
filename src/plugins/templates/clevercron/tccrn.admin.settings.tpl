<form method="post">
	<fieldset>
		<legend>{lng p="tccrn.logging"}</legend>
	
		<table width="100%">
			<tr>
				<td align="left" rowspan="4" valign="top" width="40"><img src="../plugins/templates/images/tccrn_logging.png" border="0" alt="" width="32" height="32" /></td>
				<td class="td1" width="200"><label for="id_loglevel_8">{lng p="tccrn.logging_debug"}?</label></td>
				<td class="td2"><input id="id_loglevel_8" type="checkbox" name="loglevel[]" value="8"{if ($tccrn_prefs.loglevel&8)!=0} checked="checked"{/if} /></td>
			</tr>
			<tr>
				<td class="td1"><label for="id_loglevel_1">{lng p="tccrn.logging_notices"}?</label></td>
				<td class="td2"><input id="id_loglevel_1" type="checkbox" name="loglevel[]" value="1"{if ($tccrn_prefs.loglevel&1)!=0} checked="checked"{/if} /></td>
			</tr>
			<tr>
				<td class="td1"><label for="id_loglevel_2">{lng p="tccrn.logging_warnings"}?</label></td>
				<td class="td2"><input id="id_loglevel_2" type="checkbox" name="loglevel[]" value="2"{if ($tccrn_prefs.loglevel&2)!=0} checked="checked"{/if} /></td>
			</tr>
			<tr>
				<td class="td1"><label for="id_loglevel_4">{lng p="tccrn.logging_errors"}?</label></td>
				<td class="td2"><input id="id_loglevel_4" type="checkbox" name="loglevel[]" value="4"{if ($tccrn_prefs.loglevel&4)!=0} checked="checked"{/if} /></td>
			</tr>
		</table>
	</fieldset>
	<p>
		<div style="float:right">
			<input type="submit" class="button" value=" {lng p="save"} " />
		</div>
	</p>
</form>
