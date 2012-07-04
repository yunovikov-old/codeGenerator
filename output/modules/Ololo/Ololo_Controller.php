<?php

class Ololo_Controller extends Controller {
	
	/** имя модуля */
	const MODULE = 'ololo';
	
	/** элемент, отображаемый во view по умолчанию */
	const DEFAULT_VIEW = 1;
	
	/** путь к шаблонам (относительно FS_ROOT) */
	const TPL_PATH = 'modules/Ololo/templates/';
	
	/** метод, отображаемый по умолачанию */
	protected $_displayIndex = 'list';
	
	/** ассоциация методов контроллера с ресурсами */
	public $methodResources = array(
		'display_list' 			=> 'view',
		'display_view' 			=> 'view',
	);
	
	
	/** ПРОВЕРКА ПРАВ НА ВЫПОЛНЕНИЕ РЕСУРСА */
	public function checkResourcePermission($resource){
		
		return User_Acl::get()->isResourceAllowed(self::MODULE, $resource);
	}
	
	/** ПОЛУЧИТЬ ИМЯ КЛАССА */
	public function getClass(){
		return __CLASS__;
	}
	
	
	/////////////////////
	////// DISPLAY //////
	/////////////////////
	
	/** DISPLAY LIST */
	public function display_list(){
		
		$collection = new Ololo_Collection();
		$variables = array(
			'collection' => $collection->getPaginated(),
			'pagination' => $collection->getPagination(),
			'sorters' => $collection->getSortableLinks(),
		);
		
		FrontendLayout::get()
			->setTitle('Коллекция')
			->setLinkTags($collection->getLinkTags())
			->setContentPhpFile(self::TPL_PATH.'list.php', $variables)
			->render();
	}
	
	/** DISPLAY VIEW */
	public function display_view($instanceId = null){
		
		$instanceId = (int)$instanceId;
		
		$variables = Ololo_Model::load($instanceId)->getAllFieldsPrepared();
		FrontendLayout::get()
			->setTitle('Детально')
			->setContentPhpFile(self::TPL_PATH.'view.php', $variables)
			->render();
	}
	
	
}

?>