<tr>
	<td class="td1" width="220">{lng p="savearc"}:</td>
	<td class="td2">
		<input type="checkbox" name="taskdata[save]" value="1"{if !is_array($tccrn_data.taskdata) || $tccrn_data.taskdata.save} checked="checked"{/if} />
	</td>
</tr>
<tr>
	<td class="td1" width="220">{lng p="tccrn.log_behalte"}:</td>
	<td class="td2">
		<input type="checkbox" value="1" name="taskdata[keepDays]"{if !is_array($tccrn_data.taskdata) || isset($tccrn_data.taskdata.keepDays)} checked="checked"{/if} /><input type="text" size="6" name="taskdata[days]" value="{if !is_array($tccrn_data.taskdata)}30{else}{$tccrn_data.taskdata.days}{/if}" /> {lng p="days"}
	</td>
</tr>