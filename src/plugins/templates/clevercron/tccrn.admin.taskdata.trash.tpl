<tr>
	<td class="td2" colspan="2">
		{if $tccrn_data.task == 'tccrn.tr_sp_delete'}
			{lng p="tccrn.spam_desc"}
		{else}
			{lng p="tccrn.trash_desc"}
		{/if}
	</td>
</tr>
<tr>
	<td class="td1" width="220">...{lng p="whobelongtogrps"}</td>
	<td class="td2">
		{foreach from=$groups item=group key=groupID}
			<input type="checkbox" name="taskdata[groups][]" value="{$groupID}" id="group_{$groupID}"{if !is_array($tccrn_data.taskdata.groups) || array_search($groupID, $tccrn_data.taskdata.groups) !== false} checked="checked"{/if} />
			<label for="group_{$groupID}"><b>{text value=$group.title}</b></label><br />
		{/foreach}
	</td>
</tr>
<tr>
	<td class="td2" colspan="2">
		{lng p="trash_only"}...
	</td>
</tr>
<tr>
	<td class="td1" width="220">...{lng p="trash_daysonly"}</td>
	<td class="td2">
		<input type="checkbox" value="1" name="taskdata[daysOnly]"{if !is_array($tccrn_data.taskdata) || isset($tccrn_data.taskdata.daysOnly)} checked="checked"{/if} /><input type="text" size="6" name="taskdata[days]" value="{if !isset($tccrn_data.taskdata.days)}30{else}{$tccrn_data.taskdata.days}{/if}" /> {lng p="days"}
	</td>
</tr>
<tr>
	<td class="td1" width="220">...{lng p="trash_sizesonly"}</td>
	<td class="td2">
		<input type="checkbox" value="1" name="taskdata[sizesOnly]"{if isset($tccrn_data.taskdata.sizesOnly)} checked="checked"{/if} /><input type="text" size="6" name="taskdata[size]" value="{if !isset($tccrn_data.taskdata.size)}512{else}{$tccrn_data.taskdata.size}{/if}" /> KB
	</td>
</tr>