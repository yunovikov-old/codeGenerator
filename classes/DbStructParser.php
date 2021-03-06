<?

class DbStructParser {
	
	const SRC_DESCRIBE_ARR 	= 1;
	const SRC_DESCRIBE_EVAL	= 2;
	const SRC_CREATE 		= 3;
	
	static $regExp = array(
		'fieldname' => '`?([\w\-]+)`',
	);
	
	private $_tableName = null;
	
	// Структура таблицы - массив массивов. Содержит поля:
		// Field	 имя поля
		// Type		 тип данных
		// Null		 YES/NO
		// Key		 PRI/''
		// Default	 default value
		// Extra	 auto_increment etc
	private $_tableStruct = array();
	
	// набор общих правил
	private $_commonRules = array();
	
	// набор индивидуальных правил
	private $_individualRules = array();
	
	private $_isRulesMaked = FALSE;
	
	// КОНСТРУКТОР
	public function __construct($src, $srcType){
		
		if($srcType == self::SRC_DESCRIBE_ARR)
			$this->_tableStruct = $src;
			
		elseif($srcType == self::SRC_DESCRIBE_EVAL)
			$this->_tableStruct = $this->eval_describeArr($src);
			
		elseif($srcType == self::SRC_CREATE)
			$this->_tableStruct = $this->parse_tableCreateRow($src);
			
		else
			trigger_error('Неверный тип данных.', E_USER_ERROR);
		
	}
	
	// ПОЛУЧИТЬ ИМЯ ТАБЛИЦЫ
	public function getTableName(){
		
		return $this->_tableName;
	}
	
	// ПОЛУЧИТЬ СТУКТУРУ ТАБЛИЦЫ
	public function getStructure(){
		
		return $this->_tableStruct;
	}
	
	// ПОЛУЧИТЬ НАБОР ОБЩИХ ПРАВИЛ ВАЛИДАЦИИ
	public function getCommonRules(){
		
		if(!$this->_isRulesMaked)
			$this->makeRules();
			
		return $this->_commonRules;
	}
	
	// ПОЛУЧИТЬ НАБОР ИНДИВИДУАЛЬНЫХ ПРАВИЛ ВАЛИДАЦИИ
	public function getIndividualRules(){
		
		if(!$this->_isRulesMaked)
			$this->makeRules();
	
		return $this->_individualRules;
	}
	
	// ПРЕОБРАЗОВАТЬ СТРОКУ CREATE TABLE В МАССИВ
	public function parse_tableCreateRow($tableCreateRow){
		
		$tableCreateRow = trim(preg_replace('/\s+/', ' ', trim($tableCreateRow)));
			
		$fieldsDefStr = '';
		
		if(preg_match('/CREATE TABLE( IF NOT EXISTS)? `?(\w+)`? ?\((.+)\)/i', $tableCreateRow, $matches)){
			$this->_tableName = isset($matches[2]) ? $matches[2] : '';
			$fieldsDefStr = isset($matches[3]) ? $matches[3] : '';
		}else{
			$fieldsDefStr = $tableCreateRow;
		}
		$fieldsDefStr = trim($fieldsDefStr);
		
		if(!strlen($fieldsDefStr))
			throw new Exception('Описания полей отсутствуют в строке CREATE TABLE');
		
		$fieldsDefArrRaw = explode(',', $fieldsDefStr);
		$fieldsDefArrRich = array();
		$primary_keys = array();

		foreach ($fieldsDefArrRaw as $index => $fieldDef) {
			if (substr_count($fieldDef, '(') > substr_count($fieldDef, ')')) {
				if (isset($fieldsDefArrRaw[$index + 1])) {
					$fieldsDefArrRaw[$index] .= ','.$fieldsDefArrRaw[$index + 1];
					unset($fieldsDefArrRaw[$index + 1]);
				}
			}
		}

		foreach($fieldsDefArrRaw as $fieldDef){
			
			$fieldDef = trim($fieldDef);

			$row = array(
				'Field' => '',
				'Type' => '',
				'Null' => 'YES',
				'Key' => '',
				'Default' => NULL,
				'Extra' => '',
			);
			
			if(preg_match('/PRIMARY KEY\s*\(([^\)]+)\)/i', $fieldDef, $matches)){
				$primary_keys_list = explode(',', str_replace('`', '', $matches[1]));
				foreach($primary_keys_list as $key)
					$primary_keys[$key] = 1;
				continue;
			}
			
			if(stripos($fieldDef, 'PRIMARY KEY')){
				$fieldDef = str_ireplace(' PRIMARY KEY', '', $fieldDef);
				$row['Key'] = 'PRI';
			}
			if(stripos($fieldDef, 'AUTO_INCREMENT')){
				$fieldDef = str_ireplace(' AUTO_INCREMENT', '', $fieldDef);
				$row['Extra'] = 'auto_increment';
			}
			if(preg_match('/^INDEX\s*\(.+\)/i', $fieldDef)){
				continue; // строка, определяющая индекс
			}

			if(preg_match('/^KEY\b/i', $fieldDef)){
				continue; // строка, ключ
			}
			if(stripos($fieldDef, 'NOT NULL')){
				$fieldDef = str_ireplace(' NOT NULL', '', $fieldDef);
				$row['Null'] = 'NO';
			}
			if(preg_match("/DEFAULT (NULL|\d+|'[^']*')/i", $fieldDef, $matches)){
				$fieldDef = preg_replace("/ DEFAULT (NULL|\d+|'[^']*')/i", '', $fieldDef);
				$row['Default'] = strtoupper($matches[1]) == 'NULL' ? NULL : str_replace('\'', '', $matches[1]);
			}
			
			if(preg_match('/`?([\w\-]+)`?(.*)/i', $fieldDef, $matches)){
				$row['Field'] = $matches[1];
				$row['Type'] = $matches[2];
				
				$fieldsDefArrRich[] = $row;
			}
		}
		
		foreach($fieldsDefArrRich as $index => &$data){
			if(isset($primary_keys[$data['Field']]))
				$data['Key'] = 'PRI';
		}

		return $fieldsDefArrRich;
	}
	
	// РАСПОЗНАТЬ МАССИВ, СОДЕРЖАЩИЙ СТРУКТУРУ ТАБЛИЦЫ
	public function eval_describeArr($tableStructStr){
		
		$tableStructStr = trim($tableStructStr);
			
		if(preg_match('/^array/i', $tableStructStr)){

			$tableStructArr = '';
			eval('$tableStructArr = '.$tableStructStr.';');
			
			if(!is_array($tableStructArr))
				throw new Exception('Полученные данные не удалось распознать как массив. Полученный тип: '.gettype($tableStructArr));
				
			return $tableStructArr;
		}else{
			throw new Exception('Массив данных должен быть представлен в ввиде: <b>array( ... )</b>');
		}
	}
	
	// СГЕНЕРИРОВАТЬ ПРАВИЛА ВАЛИДАЦИИ
	public function makeRules(){
		
		if(!is_array($this->_tableStruct))
			throw new Exception('Структура таблицы должна быть массивом. Получен: '.gettype($this->_tableStruct));
			
		foreach($this->_tableStruct as $row){
		
			// значение PRIMARY KEY AUTO_INCREMENT является недопустимым для указания полем
			if(strtoupper($row['Key']) == 'PRI' && strtoupper($row['Extra']) == 'AUTO_INCREMENT')
				continue;
			
			$fieldName = $row['Field'];
			$this->_individualRules[$fieldName] = array();
			
			// если по умолчанию поле имеет значение NULL, но стоит флаг NOT NULL, то это поле обязательно.
			if(strtoupper($row['Null']) == 'NO' && $row['Default'] == NULL){
				$this->_individualRules[$fieldName]['required'] = TRUE;
				$isRequired = TRUE;
			}else{
				$isRequired = FALSE;
			}
			
			// распознавание некоторых типов данных
			if(preg_match('/^([\w\-]+)\s*(\((\d+)\))?\s*(.*)$/', trim($row['Type']), $matches)){
			
				// print_r($matches);
				// echo'+ <br />';
				$colType = strtoupper($matches[1]);
				$colLength = (int)$matches[3];
				$colFlags = explode(' ', strtoupper($matches[4]));
				
				switch($colType){
					
				case 'TINYINT': case 'SMALLINT': case 'MEDIUMINT': case 'INT': case 'INTEGER': case 'BIGINT':
					$this->_individualRules[$fieldName]['settype'] = 'int';
					break;
				
				case 'FLOAT': case 'DOUBLE': case 'REAL': case 'DECIMAL': case 'DEC': case 'NUMERIC': 
					$this->_individualRules[$fieldName]['settype'] = 'float';
					break;
				
				case 'DATE':
					$this->_individualRules[$fieldName]['dbDate'] =  TRUE;
					break;
				
				case 'TIME':
					$this->_individualRules[$fieldName]['dbTime'] = TRUE;
					break;
				
				case 'DATETIME':
					$this->_individualRules[$fieldName]['dbDateTime'] = TRUE;
					break;
				
				case 'VARCHAR': case 'CHAR':
					$this->_individualRules[$fieldName]['strip_tags'] = TRUE;
					$this->_individualRules[$fieldName]['length'] = array('max' => $colLength);
					break;
				
				case 'TINYTEXT': case 'TINYBLOB':
					$this->_individualRules[$fieldName]['strip_tags'] = TRUE;
					$this->_individualRules[$fieldName]['length'] = array('max' => 255);
					break;
				
				case 'TEXT': case 'BLOB':
					$this->_individualRules[$fieldName]['strip_tags'] = TRUE;
					$this->_individualRules[$fieldName]['length'] = array('max' => 65535);
					break;
				
				case 'MEDIUMTEXT': case 'MEDIUMBLOB':
					$this->_individualRules[$fieldName]['strip_tags'] = TRUE;
					$this->_individualRules[$fieldName]['length'] = array('max' => 16777215);
					break;
				
				case 'LONGTEXT': case 'LONGBLOB':
					$this->_individualRules[$fieldName]['strip_tags'] = TRUE;
					$this->_individualRules[$fieldName]['length'] = array('max' => 4294967295);
					break;
				
				case 'BOOLEAN':
					$this->_individualRules[$fieldName]['checkbox'] = array('on' => TRUE, 'off' => FALSE);
					break;
				}
				
			}
		}
		
		$this->_isRulesMaked = TRUE;
	}
	
	// ПРЕОБРАЗОВАТЬ МАССИВ В ВАЛИДНЫЙ PHP КОД
	public static function getArrStr($code, $rowPrefix = ""){
		
		$output = '';
		
		$lf = "\r\n";
		$t = "\t";
		
		$output = 'array('.$lf;

		foreach($code as $elm => $rules){
		
			$output .= $rowPrefix.$t."'".$elm."' => array(";
			$rulesArr = array();
			
			foreach($rules as $rule => $params){
				
				$ruleStr = '';
				
				if(!is_int($rule))
					$ruleStr .= "'".$rule."' => ";

				$ruleStr .= self::_val2str($params);
				
				$rulesArr[] = $ruleStr;
			}
			$output .= implode(", ", $rulesArr)."),".$lf;
		}
		$output .= $rowPrefix.')';
		
		return $output;
	}
	
	private static function _val2str($val){
		
			if(is_numeric($val)) {
			
				return $val;
			} elseif (is_array($val)) {

				$ruleParamsArr = array();
				foreach($val as $k => $v)
					$ruleParamsArr[] = "'".$k."' => ".self::_val2str($v);
					
				return "array(".implode(", ", $ruleParamsArr).")";
			} elseif(is_bool($val)) {
				
				return $val ? 'TRUE' : 'FALSE';
			} elseif(is_null($val)) {
				
				return 'NULL';
			} else {
				
				return "'".$val."'";
			}
	}
	
}

?>