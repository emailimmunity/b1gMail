<table class="nliTable">
	<tr>
		<td class="nliIconTD"><img src="./plugins/templates/images/smstan.logo2.png" border="0" alt="" /></td>
		<td class="nliTD">
			<h3>{lng p="smstan"} {lng p="login"}</h3>
			{lng p="smstan_text2"}<br/><br/>

			<form action="index.php?action=smstan" method="post" id="loginForm">
			<input type="hidden" name="do" value="smslogin">
			<table>
				<tr>
					<td class="formCaption"><label for="code">{lng p="smstan_tan"}:</label></td>
					<td class="formField"><input type="text" name="code" size="45" id="code" />
					</td>
				</tr>
				<tr>
					<td class="formCaption">&nbsp;</td>
					<td class="formField"><center><input type="submit" class="goldsubmit width150" value=" &nbsp; {lng p="login"} &nbsp; " /></center></td>
				</tr>
			</table>
			</form>
			<br />
		</td>
	</tr>
</table>