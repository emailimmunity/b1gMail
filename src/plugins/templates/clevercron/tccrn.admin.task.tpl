<form method="post">
	<input type="hidden" name="id" value="{if $tccrn_data.cronid}{$tccrn_data.cronid}{else}{$smarty.post.id}{/if}" />
	<fieldset>
		<legend>{lng p="tccrn.cron"}</legend>
		<table>
			<tr>
				<td rowspan="100" width="40" align="center" valign="top"><img src="../plugins/templates/images/tccrn_task.png" border="0" alt="" width="32" height="32" /></td>
				<td class="td1" width="220">{lng p="tccrn.aktiviert"}:</td>
				<td class="td2">
					<input type="checkbox" value="1" name="active"{if empty($tccrn_data) || $tccrn_data.active} checked="checked"{/if} />
				</td>
			</tr>
			<tr>
				<td class="td1" width="220">{lng p="tccrn.logging"}:</td>
				<td class="td2">
					<input type="checkbox" value="1" name="log"{if empty($tccrn_data) || $tccrn_data.log} checked="checked"{/if} />
				</td>
			</tr>
			<tr>
				<td class="td1" width="220">{lng p="tccrn.cron"}:</td>
				<td class="td2">
					<input type="hidden" name="task" value="{$tccrn_data.task}" />
					<select id="tccrn_task" name="task" onchange="EBID('tccrn_button_next').click();"{if !empty($tccrn_data)} disabled="disabled"{/if}>
						<option>--</option>
						{foreach from=$tccrn_tasks item=task key=key}
							<option value="{$key}"{if $tccrn_data.task == $key} selected="selected"{/if}>{$task}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			{if $tccrn_task_data}
				{include file="$tccrn_task_data"}
			{/if}
		</table>
	</fieldset>
	<div style="float:right;{if !empty($tccrn_data)} display: none;{/if}">
		<input type="submit" class="button" id="tccrn_button_next" value="{lng p="next"} &raquo;" name="next" />&nbsp;
	</div>
	{if !empty($tccrn_data)}
	<script type="text/javascript">
	EBID('tccrn_task').disabled = false;
	</script>
	<fieldset>
		<legend>{lng p="tccrn.zeit"}</legend>
		<table>
			<tr>
				<td rowspan="5" width="40" align="center" valign="top"><img src="../plugins/templates/images/tccrn_time.png" border="0" alt="" width="32" height="32" /></td>
				<td class="td1" width="220">{lng p="tccrn.monatstage"}:</td>
				<td class="td2">
					<select name="crondata[day][]" multiple="multiple" size="4" style="width: 100px;">
						{section name=day start=1 loop=32 step=1}
							<option{if is_array($tccrn_data.crondata.day) && (array_search($smarty.section.day.index, $tccrn_data.crondata.day) !== false)} selected="selected"{/if}>{$smarty.section.day.index}</option>
						{/section}
					</select>
				</td>
			</tr>
			<tr>
				<td class="td1" width="220">{lng p="tccrn.wochentage"}:</td>
				<td class="td2">
					<select name="crondata[weekday][]" multiple="multiple" size="4" style="width: 100px;">
						{section name=weekday start=0 loop=7 step=1}
							<option value="{$smarty.section.weekday.index}"{if is_array($tccrn_data.crondata.weekday) && (array_search($smarty.section.weekday.index, $tccrn_data.crondata.weekday) !== false)} selected="selected"{/if}>{$tccrn_wochentage_array[$smarty.section.weekday.index]}</option>
						{/section}
					</select>
				</td>
			</tr>
			<tr>
				<td class="td1" width="220">{lng p="tccrn.monate"}:</td>
				<td class="td2">
					<select name="crondata[month][]" multiple="multiple" size="4" style="width: 100px;">
						{section name=month start=1 loop=13 step=1}
							<option{if is_array($tccrn_data.crondata.month) && (array_search($smarty.section.month.index, $tccrn_data.crondata.month) !== false)} selected="selected"{/if}>{$smarty.section.month.index}</option>
						{/section}
					</select>
				</td>
			</tr>
			<tr>
				<td class="td1" width="220">{lng p="tccrn.stunden"}:</td>
				<td class="td2">
					<select name="crondata[hour][]" multiple="multiple" size="4" style="width: 100px;">
						{section name=hour start=0 loop=24 step=1}
							<option{if is_array($tccrn_data.crondata.hour) && (array_search($smarty.section.hour.index, $tccrn_data.crondata.hour) !== false)} selected="selected"{/if}>{$smarty.section.hour.index}</option>
						{/section}
					</select>
				</td>
			</tr>
			<tr>
				<td class="td1" width="220">{lng p="tccrn.minuten"}:</td>
				<td class="td2">
					<select name="crondata[minute][]" multiple="multiple" size="4" style="width: 100px;">
						{section name=minute start=0 loop=60 step=1}
							<option{if is_array($tccrn_data.crondata.minute) && (array_search($smarty.section.minute.index, $tccrn_data.crondata.minute) !== false)} selected="selected"{/if}>{$smarty.section.minute.index}</option>
						{/section}
					</select>
				</td>
			</tr>
		</table>
	</fieldset>
	<p>
		<div style="float:right;">
			<input type="submit" class="button" value="{lng p="save"}" name="crondata[save" />&nbsp;
		</div>
	</p>
	{/if}
</form>