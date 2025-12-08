<table class="nliTable">
	<tr>
		<td class="nliIconTD"><img src="./plugins/templates/images/smstan.logo.png" border="0" alt="" /></td>
		<td class="nliTD">
			<h3>{lng p="smstan_requesttan"}</h3>
			{lng p="smstan_text"}
			{$pricetext}<br/><br/>

			<form action="index.php?action=smstan" method="post" id="loginForm">
			<input type="hidden" name="do" value="send">
			<table>
				<tr>
					<td class="formCaption"><label for="email_local">{lng p="email"}:</label></td>
					<td class="formField">
						{if $domain_combobox}
						<input type="text" name="email_local" size="30" id="email_local" />
						<select name="email_domain">
							{foreach from=$domainList item=domain}<option value="{$domain}">@{$domain}</option>{/foreach}
						</select>
						{else}
						<input type="text" name="email_full" size="45" id="email_local" />
						{/if}
					</td>
					<td></td>
				</tr>
				<tr>
					<td class="formCaption"><img border="0" alt="" style="vertical-align: middle;" src="{$tpldir}images/main/ip.gif"/></td>
					<td colspan="2">{lng p="iprecord"}</td>
				</tr>
				<tr>
					<td class="formCaption">&nbsp;</td>
					<td class="formField"><center><input type="submit" class="goldsubmit width150" value=" &nbsp; {lng p="smstan_requesttan"} &nbsp; " /></center></td>
					<td width="100px"></td>
				</tr>
			</table>
			</form>
			<br />
		</td>
	</tr>
	<tr>
		<td class="nliIconTD"><img src="./plugins/templates/images/smstan.logo2.png" border="0" alt="" /></td>
		<td class="nliTD">
			<h3>{lng p="smstan_tan"}</h3>
			{lng p="smstan_text2"}<br/><br/>

			<form action="index.php?action=smstan" method="post" id="loginForm2">
			<input type="hidden" name="do" value="smslogin">
			<table>
				<tr>
					<td class="formCaption"><label for="code">{lng p="smstan_tan"}:</label></td>
					<td class="formField"><input type="text" name="code" size="45" id="code" />
					</td>
					<td></td>
				</tr>
				<tr>
					<td class="formCaption">&nbsp;</td>
					<td class="formField"><center><input type="submit" class="goldsubmit width150" value=" &nbsp; {lng p="login"} &nbsp; " /></center></td>
					<td></td>
				</tr>
			</table>
			</form>
			<br />
		</td>
	</tr>
</table>