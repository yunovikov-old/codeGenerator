
<div class="options-row">
	<a href="<?= "<?= href('admin/content/$MODEL_NAME_LOW/new'); ?>"; ?>">Добавить запись</a>
</div>

<?= '<?= $this->pagination; ?>'; ?>


<?= '<? if($this->collection): ?>'; ?>

	<table class="std-grid tr-highlight">
	<tr>
<? foreach($FIELDS_TITLES as $field => $title){
	if(!empty($ALLOWED_FIELDS[$field]))
		echo "\t\t".'<th>'.(!empty($SORTABLE_FIELDS[$field]) ? "<?= \$this->sorters['".$field."']; ?>" : $title).'</th>'."\r\n";
	} ?>
		<th>Опции</th>
	</tr>
	<?= '<? foreach($this->collection as $item): ?>' ?>
	
	<tr>
<? foreach($FIELDS_TITLES as $field => $title)
		if(!empty($ALLOWED_FIELDS[$field]))
			echo "\t\t<td><?= \$item['".$field."']; ?></td>\r\n"; ?>
			
		<td class="center">
			<div class="tr-hover-visible options">
				<a href="<?= "<?= href('$MODEL_NAME_LOW/view/'.\$item['id']); ?>"; ?>" title="Просмотреть"><img src="images/backend/icon-view.png" alt="Просмотреть" /></a>
				<a href="<?= "<?= href('admin/content/$MODEL_NAME_LOW/edit/'.\$item['id']); ?>"; ?>" title="Редактировать"><img src="images/backend/icon-edit.png" alt="Редактировать" /></a>
				<a href="<?= "<?= href('admin/content/$MODEL_NAME_LOW/delete/'.\$item['id']); ?>"; ?>" title="Удалить"><img src="images/backend/icon-delete.png" alt="Удалить" /></a>
			</div>
		</td>
	</tr>
	<?= '<? endforeach; ?>'; ?>
	
	</table>
<?= '<? else: ?>'; ?>

	<p>Сохраненных записей пока нет.</p>
<?= '<? endif; ?>'; ?>


<?= '<?= $this->pagination; ?>'; ?>
