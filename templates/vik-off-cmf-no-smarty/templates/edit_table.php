
<ul id="submit-box-floating"></ul>

<form id="edit-form" action="" method="post">
	<?= '<?= FORMCODE; ?>'; ?>
	
	<input type="hidden" name="id" value="<?= '<?= $this->instanceId; ?>'; ?>" />
	
	<table id="edit-form-table">
	<tr>
		<td class="title" colspan="2"><?= '<?= $this->pageTitle; ?>'; ?></td>
	</tr>
<?
foreach($FIELDS_TITLES as $field => $title){
	if(!empty($ALLOWED_FIELDS[$field]) && $field != 'id'){
		echo
'	<tr>
		<th>'.$title.'</th>
		<td>'.$this->getEditTplInput($INPUT_TYPES[$field], $field).'</td>
	</tr>
';	}
}
?>

	<tr id="submit-box">
		<td class="actions" colspan="2">
			<input id="submit-save" class="button" type="submit" name="action[<?=$MODEL_NAME_LOW;?>/save][admin/content/<?=$MODEL_NAME_LOW;?>/list]" value="Сохранить" title="Созхранить изменения и вернуться к списку" />
			<input id="submit-apply" class="button" type="submit" name="action[<?=$MODEL_NAME_LOW;?>/save][admin/content/<?=$MODEL_NAME_LOW;?>/edit/<?= '<?= $this->instanceId ? $this->instanceId : \'(%id%)\' ; ?>'; ?>]" value="Применить" title="Сохранить изменения и продолжить редактирование" />
			<a id="submit-cancel" class="button" href="<?= "<?= href('admin/content/$MODEL_NAME_LOW/list'); ?>"; ?>" title="Отменить все изменения и вернуться к списку">отмена</a>
			<?= '<? if($this->instanceId): ?>'; ?>
			
				<a id="submit-delete" class="button" href="<?= "<?= href('admin/content/$MODEL_NAME_LOW/delete/'.\$this->instanceId); ?>"; ?>" title="Удалить запись">удалить</a>
				<a id="submit-copy" class="button" href="<?= "<?= href('admin/content/$MODEL_NAME_LOW/copy/'.\$this->instanceId); ?>"; ?>" title="Сделать копию записи">копировать</a>
			<?= '<? endif; ?>'; ?>
		</td>
	</tr>
	</table>
</form>

<script type="text/javascript">

$(function(){
	$("#edit-form").validate( { <?= '<?= $this->validation; ?>'; ?> } );
	enableFloatingSubmits();
});

</script>
