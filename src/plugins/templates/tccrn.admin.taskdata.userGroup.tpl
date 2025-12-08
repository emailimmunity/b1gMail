{include file="$tccrn_task_data_user"}
<tr>
	<td class="td1" width="220">{lng p="movetogroup"}:</td>
	<td class="td2">
		<select name="taskdata[moveGroup]">
		{foreach from=$groups item=groupItem}
			<option value="{$groupItem.id}"{if is_array($tccrn_data.taskdata) && $groupItem.id == $tccrn_data.taskdata.moveGroup} selected="selected"{/if}>{text value=$groupItem.title}</option>
		{/foreach}
		</select>
	</td>
</tr>