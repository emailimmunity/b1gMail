{if substr($tccrn_data.task, -6) == 'delete'}
<tr>
	<td class="td1" width="220">{lng p="tccrn.user_wirklich_loeschen"}:</td>
	<td class="td2">
		<input type="checkbox" name="taskdata[realdel]" value="1"{if !empty($tccrn_data.taskdata.realdel)} checked="checked"{/if} />
	</td>
</tr>
{/if}
<tr>
	<td class="td2" colspan="2">
		{lng p="tccrn.benutzer_anwenden"}
	</td>
</tr>
<tr>
	<td class="td1" width="220">...{if $tccrn_data.task == 'tccrn.us_na_delete' || $tccrn_data.task == 'tccrn.us_nl_delete'}{lng p="trash_daysonly"}{else}{lng p="notloggedinsince"}{/if}</td>
	<td class="td2">
		<input type="text" size="6" name="taskdata[days]" value="{if !isset($tccrn_data.taskdata.days)}90{else}{$tccrn_data.taskdata.days}{/if}" /> {lng p="days"}
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