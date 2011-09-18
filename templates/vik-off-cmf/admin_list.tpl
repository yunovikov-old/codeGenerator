
<div class="options-row">
	{a href=admin/content/<?=$MODEL_NAME_LOW;?>/new text="Добавить запись"}
</div>

{$pagination}

{if $collection}
	<table class="std-grid tr-highlight">
	<tr>
<? foreach($FIELDS_TITLES as $field => $title){
	if(!empty($ALLOWED_FIELDS[$field]))
		echo "\t\t".'<th>'.(isset($SORTABLE_FIELDS[$field]) ? '{$sorters.'.$field.'}' : $title).'</th>'."\r\n";
	} ?>
		<th>Опции</th>
	</tr>
	{foreach from=$collection item='item'}
	<tr>
<? foreach($FIELDS_TITLES as $field => $title)
		if(!empty($ALLOWED_FIELDS[$field]))
			echo "\t\t".'<td>{$item.'.$field.'}</td>'."\r\n"; ?>
		<td class="center">
			<div class="tr-hover-visible options">
				<a href="{a href=<?=$MODEL_NAME_LOW;?>/view/`$item.id`}" class="item" title="Просмотреть"><img src="images/backend/icon-view.png" alt="Просмотреть" /></a>
				<a href="{a href=admin/content/<?=$MODEL_NAME_LOW;?>/edit/`$item.id`}" class="item" title="Редактировать"><img src="images/backend/icon-edit.png" alt="Редактировать" /></a>
				<a href="{a href=admin/content/<?=$MODEL_NAME_LOW;?>/delete/`$item.id`}" class="item" title="Удалить"><img src="images/backend/icon-delete.png" alt="Удалить" /></a>
			</div>
		</td>
	</tr>
	{/foreach}
	</table>
{else}
	<p>Сохраненных записей пока нет.</p>
{/if}

{$pagination}

