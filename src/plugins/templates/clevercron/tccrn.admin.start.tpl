<fieldset id="reg_opt">
	<legend>{lng p="tccrn.angelegte_crons"}</legend>
	{if !count($tccrn_tasks)}
	<i>{lng p="tccrn.keine_crons"}</i>
	{else}
	<table class="list">
		<tr>
			<th width="55">{lng p="tccrn.aktiviert"}</th>
			<th>{lng p="tccrn.cron"}</th>
			<th>{lng p="tccrn.naechster_aufruf"}</th>
			<th>{lng p="tccrn.status"}</th>
			<th width="75">&nbsp;</th>
		</tr>
		{foreach from=$tccrn_tasks item=task}
		{assign var="details" value=""}
		{cycle name=class values="td1,td2" assign=class}
		<tr class="{$class}">
			<td>
				<a href="{$pageURL}&amp;do=switch&amp;id={$task.cronid}&amp;active={$task.active}&amp;sid={$sid}" title="{if !$task.active}{lng p='tccrn.aktivieren'}{else}{lng p='tccrn.deaktivieren'}{/if}"><img src="../plugins/templates/images/tccrn_{if !$task.active}not{/if}active.png" border="0" alt="" width="16" height="16" /></a>
			</td>
			<td>
				{lng p=$task.task}
				{if substr($task.task, 0, 8) == 'tccrn.db'}
					{foreach from=$task.taskdata.table item=table name=f_task}
						{assign var="details" value="$details$table"}
						{if !$smarty.foreach.f_task.last}{assign var="details" value="$details, "}{/if}
					{/foreach}
				{elseif substr($task.task, 0, 8) == 'tccrn.lg'}
					{capture assign="details"}{lng p="savearc"}: {if $task.taskdata.save}{lng p="yes"}{else}{lng p="no"}{/if}{if $task.taskdata.keepDays}; {lng p="tccrn.log_behalte"} {$task.taskdata.days} {lng p="days"}{/if}{/capture}
				{elseif substr($task.task, 0, 8) == 'tccrn.us'}
					{capture assign="details"}{lng p="days"}: {$task.taskdata.days}; {lng p="groups"}: {foreach from=$task.taskdata.groups item=gid name=f_groups}{$groups[$gid].title}{if !$smarty.foreach.f_groups.last}, {/if}{/foreach}{if $task.task == 'tccrn.us_move'}; {lng p="tccrn.nach_gruppe"}: {$groups[$task.taskdata.moveGroup].title}{/if}{/capture}
				{elseif substr($task.task, 0, 8) == 'tccrn.tr'}
					{capture assign="details"}{lng p="groups"}: {foreach from=$task.taskdata.groups item=gid name=f_groups}{$groups[$gid].title}{if !$smarty.foreach.f_groups.last}, {/if}{/foreach}{if $task.taskdata.daysOnly}; {lng p="days"}: {$task.taskdata.days}{/if}{if $task.taskdata.sizesOnly}; {lng p="size"}: {$task.taskdata.size} KB{/if}{/capture}
				{elseif in_array(substr($task.task, 0, 8), array('tccrn.se', 'tccrn.st'))}
					{capture assign="details"}{lng p="tccrn.log_behalte"} {$task.taskdata.days} {lng p="days"}{/capture}
				{/if}
				{if $details}
				<br />
				<small title="{text value=$details noentities=1}">
					{text value=$details cut=55 noentities=1}
				</small>
				{/if}
			</td>
			<td{if !$task.active} style="color: #666666;"{/if}>{if $task.nextcall == 0}-{else}{date timestamp=$task.nextcall}<br /><small>{tccrn_countdown timestamp=$task.nextcall}{/if}</small></td>
			<td>{if $task.lastcall == 0 || !$task.active}-{else}<img src="{$tpldir}images/{if $task.active && $task.status == 'started'}{if $task.lastcall + 30 > time()}warning{else}error{/if}{else}ok{/if}.png" border="0" alt="" width="16" height="16" align="absmiddle" title="{if $task.active && $task.status == 'started'}{if $task.lastcall + 30 > time()}{lng p='tccrn.cron_gestartet'}{else}{lng p='tccrn.cron_fehler'}{/if}{else}{lng p='tccrn.cron_ok'}{/if}" /> ({date timestamp=$task.lastcall nice=1}){/if}</td>
			<td>
				<a href="{$pageURL}&amp;do=execute&amp;id={$task.cronid}&amp;sid={$sid}" title="{lng p="execute"}"><img src="{$tpldir}images/go.png" border="0" alt="{lng p="execute"}" width="16" height="16" /></a>
				<a href="{$pageURL}&amp;action=task&amp;id={$task.cronid}&amp;sid={$sid}" title="{lng p="edit"}"><img src="{$tpldir}images/edit.png" border="0" alt="{lng p="edit"}" width="16" height="16" /></a>
				<a href="{$pageURL}&amp;do=delete&amp;id={$task.cronid}&amp;sid={$sid}" onclick="return confirm('{lng p="realdel"}');" title="{lng p="delete"}"><img src="{$tpldir}images/delete.png" border="0" alt="{lng p="delete"}" width="16" height="16" /></a>
			</td>
		</tr>
		{/foreach}
	</table>
	{/if}
	<small>{lng p="tccrn.serverzeit"}: {date timestamp=$smarty.now}</small>
</fieldset>

<fieldset>
	<legend>{lng p="notices"}</legend>
	
	<table width="100%" id="noticeTable">
	{foreach from=$notices item=notice}
		<tr>
			<td width="20" valign="top"><img src="{$tpldir}images/{$notice.type}.png" width="16" height="16" border="0" alt="" align="absmiddle" /></td>
			<td valign="top">{$notice.text}</td>
			<td align="right" valign="top" width="20">{if $notice.link}<a href="{$notice.link}sid={$sid}"><img src="{$tpldir}images/go.png" border="0" alt="" width="16" height="16" /></a>{else}&nbsp;{/if}</td>
		</tr>
	{/foreach}
	</table>
</fieldset>

<script language="javascript" src="{$updateURL}"></script>
<img src="../cron.php?out=img" width="1" height="1" border="0" alt="" />
<script type="text/javascript">setTimeout("location.reload(true);", 60000);</script>