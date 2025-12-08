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

<fieldset>
	<legend>{lng p="pop3acc_refresh"}</legend>

	{if $start}
	<img src="../cron.userpop3.php?out=img" width="1" height="1" border="0" alt="" />
	<center><img src="./templates/images/ok.png" border="0" alt="{lng p="ok"}" width="16" height="16" /> {lng p="success"}!</center><br/><br/>
	{/if}

	<form action="plugin.page.php?plugin=pop3acc&action=page3&start=true&sid={$sid}" method="post">
	<center>{lng p="pop3acc_starttext"}</center>
	<p align="right">
		<input type="submit" class="button" value=" {lng p="execute"} " />
	</p>	
	</form>
</fieldset>