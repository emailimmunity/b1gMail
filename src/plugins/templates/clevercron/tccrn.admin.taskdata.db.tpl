<tr>
	<td class="td1" width="220">{lng p="tables"}:</td>
	<td class="td2">
		<select size="10" name="taskdata[table][]" multiple="multiple">
		{foreach from=$tccrn_tables item=table}
			<option value="{$table}"{if !is_array($tccrn_data.taskdata.table) || (array_search($table, $tccrn_data.taskdata.table) !== false)} selected="selected"{/if}>{$table}</option>
		{/foreach}
		</select>
	</td>
</tr>