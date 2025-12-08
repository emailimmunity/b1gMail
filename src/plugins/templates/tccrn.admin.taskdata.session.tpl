<tr>
	<td class="td1" width="220">{lng p="tccrn.log_behalte"}:</td>
	<td class="td2">
		<input type="text" size="6" name="taskdata[days]" value="{if !isset($tccrn_data.taskdata.days)}30{else}{$tccrn_data.taskdata.days}{/if}" /> {lng p="days"}
	</td>
</tr>