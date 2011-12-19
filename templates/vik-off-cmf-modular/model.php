%%%	USE PLACEHOLDERS:
%%%		__CLASSNAME__
%%%		__TABLENAME__
%%%		__MODULE__
%%%		__VALIDATION_COMMON__
%%%		__VALIDATION_INDIVIDUAL__
%%%		__FIELD_TITLES__
<?php

class __CLASSNAME__ extends ActiveRecord {
	
	/** имя модуля */
	const MODULE = '__MODULE__';
	
	/** таблица БД */
	const TABLE = '__TABLENAME__';
	
	/** типы сохранения */
	const SAVE_CREATE   = 'create';
	const SAVE_EDIT     = 'edit';
	
	const NOT_FOUND_MESSAGE = 'Страница не найдена';

	
	/** ТОЧКА ВХОДА В КЛАСС (СОЗДАНИЕ НОВОГО ОБЪЕКТА) */
	public static function create(){
			
		return new __CLASSNAME__(0, self::INIT_NEW);
	}
	
	/** ТОЧКА ВХОДА В КЛАСС (ЗАГРУЗКА СУЩЕСТВУЮЩЕГО ОБЪЕКТА) */
	public static function load($id){
		
		return new __CLASSNAME__($id, self::INIT_EXISTS);
	}
	
	/** ТОЧКА ВХОДА В КЛАСС (ЗАГРУЗКА СУЩЕСТВУЮЩЕГО ОБЪЕКТА) */
	public static function forceLoad($id, $fieldvalues){
		
		return new __CLASSNAME__($id, self::INIT_EXISTS_FORCE, $fieldvalues);
	}
	
	/** ПОЛУЧИТЬ ИМЯ КЛАССА */
	public function getClass(){
		return __CLASS__;
	}
	
	/**
	 * ПРОВЕРКА ВОЗМОЖНОСТИ ДОСТУПА К ОБЪЕКТУ
	 * Вызывается автоматически при загрузке существующего объекта
	 * В случае запрета доступа генерирует нужное исключение
	 */
	protected function _accessCheck(){}
	
	/**
	 * ДОЗАГРУЗКА ДАННЫХ
	 * выполняется после основной загрузки данных из БД
	 * и только для существующих объектов
	 * @param array &$data - данные полученные основным запросом
	 * @return void
	 */
	protected function _afterLoad(&$data){}
	
	/** ПОДГОТОВКА ДАННЫХ К ОТОБРАЖЕНИЮ */
	public function beforeDisplay($data){
	
		// $data['modif_date'] = YDate::loadTimestamp($data['modif_date'])->getStrDateShortTime();
		// $data['create_date'] = YDate::loadTimestamp($data['create_date'])->getStrDateShortTime();
		return $data;
	}
	
	/** ПОЛУЧИТЬ ЭКЗЕМПЛЯР ВАЛИДАТОРА */
	public function getValidator($mode = self::SAVE_CREATE){
		
		$rules = __VALIDATION_INDIVIDUAL__;
		
		$fields = array();
		switch($mode) {
			
			case self::SAVE_CREATE:
				$fields = array(__VALIDATION_FIELDS__);
				break;
			
			case self::SAVE_EDIT:
				$fields = array(__VALIDATION_FIELDS__);
				break;
			
			default: trigger_error('Неверный ключ валидатора', E_USER_ERROR);
		}
		
		$fieldsRules = array();
		foreach($fields as $f)
			$fieldsRules[$f] = $rules[$f];
			
		$validator = new Validator($fieldsRules);
		
		$validator->setFieldTitles(array(__FIELD_TITLES__		));
		
		return $validator;
	}
		
	/** ПРЕ-ВАЛИДАЦИЯ ДАННЫХ */
	public function preValidation(&$data, $saveMode = self::SAVE_DEFAULT){}
	
	/** ПОСТ-ВАЛИДАЦИЯ ДАННЫХ */
	public function postValidation(&$data, $saveMode = self::SAVE_DEFAULT){
		
		// $data['author'] = USER_AUTH_ID;
		// $data['modif_date'] = time();
		// if($this->isNewObj)
			// $data['create_date'] = time();
	}
	
	/** ДЕЙСТВИЕ ПОСЛЕ СОХРАНЕНИЯ */
	public function afterSave($data){
		
	}
	
	/** ПОДГОТОВКА К УДАЛЕНИЮ ОБЪЕКТА */
	public function beforeDestroy(){
	
	}
	
}

class __COLLECTION_CLASS__ extends ARCollection {
	
	/**
	 * поля, по которым возможна сортировка коллекции
	 * каждый ключ должен быть корректным выражением для SQL ORDER BY
	 * var array $_sortableFieldsTitles
	 */
	protected $_sortableFieldsTitles = array(__SORTABLE_FIELDS__	);
	
	
	/** ТОЧКА ВХОДА В КЛАСС */
	public static function load($filters = array(), $options = array()){
			
		return new __COLLECTION_CLASS__($filters, $options);
	}
	
	/** КОНСТРУКТОР */
	public function __construct($filters = array(), $options = array()){
		
		$this->_filters = $filters;
		$this->_options = $options;
	}

	/** ПОЛУЧИТЬ СПИСОК С ПОСТРАНИЧНОЙ РАЗБИВКОЙ */
	public function getPaginated(){
		
		$where = $this->_getSqlFilter();
		$sorter = new Sorter('id', 'DESC', $this->_sortableFieldsTitles);
		$paginator = new Paginator('sql', array('*', 'FROM '.__CLASSNAME__::TABLE.' '.$where.' ORDER BY '.$sorter->getOrderBy()), 50);
		
		$data = db::get()->getAll($paginator->getSql(), array());
		
		foreach($data as &$row)
			$row = __CLASSNAME__::forceLoad($row['id'], $row)->getAllFieldsPrepared();
		
		$this->_sortableLinks = $sorter->getSortableLinks();
		$this->_pagination = $paginator->getButtons();
		$this->_linkTags = $paginator->getLinkTags();
		
		return $data;
	}
	
	/** ПОЛУЧИТЬ СПИСОК ВСЕХ ЭЛЕМЕНТОВ */
	public function getAll(){
		
		$where = $this->_getSqlFilter();
		$data = db::get()->getAllIndexed('SELECT * FROM '.__CLASSNAME__::TABLE.' '.$where, 'id', array());
		
		foreach($data as &$row)
			$row = __CLASSNAME__::forceLoad($row['id'], $row)->getAllFieldsPrepared();
		
		return $data;
	}
	
}

?>