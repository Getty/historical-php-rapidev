<?

require_once(DATAOBJECT2_PATH.'/Driver/Common.php');

class DB_DataObject2_Driver_mysql extends DB_DataObject2_Driver_Common 
{	
	protected $rowCount;	
	protected $lastInsertId;
	protected $DB;
	protected $writeDB;
	public $insertupdate = true;
	
	public function __construct($config)
	{
		if (isset($config['database'])) {
			$this->_database = $config['database'];
		} else {
			throw new Exception('DB_DataObject2 database not set',DB_DATAOBJECT_ERROR_INVALIDCONFIG);			
		}
		
		$this->_config = $config;
		$this->checkself();		
	}
	
	public static function factory($config)
	{
		if (!is_array($config)) {
			$config = DB_DataObject2_Driver_Common::peardsn2array($config);
		}
		
		return new DB_DataObject2_Driver_mysql($config);
		
	}

	public function rowCount()
	{
		return $this->rowCount;
	}
	
	public function query($string)
	{
		$command = strtolower(substr(trim($string),0,4));
        switch($command) {
        	case 'sele':
        	case 'desc':
        	case 'show':
        	case 'hand':
        		
        		if (!$result = mysql_unbuffered_query($string,$this->DB)) {
        			throw new Exception('database query "'.$string.'" failed in database '.$this->_config['database'].' with: '.$this->error());
        		}
        		
        		$array = Array();
        		$i = 0;
        		while ($row = mysql_fetch_assoc($result)) {
        			$i++;
        			$array[] = $row;
        		}
        		        		
        		$this->rowCount = $i;
        		
				mysql_free_result($result);

				return $array;
        	break;
		
        	default:
        		if (isset($this->writeDB)) {
        			$DB = $this->writeDB;
        		} else {
        			$DB = $this->DB;
        		}
        		if (!mysql_query($string,$DB)) {
        			throw new Exception('database manipulation "'.$string.'" failed in database '.$this->_config['database'].' with: '.$this->error($DB));
        		}
        		
        		$this->rowCount = mysql_affected_rows($DB);
        		
        		if ($command == 'inse') {
        			$ID = mysql_insert_id($DB);
        			$this->lastInsertId = $ID;
        		} else {
        			unset($this->lastInsertId);
        		}
        		return $this->rowCount;
        	break;
        	
        }
	}
	
	public function __call($name,$args)
	{
		if (method_exists($this->DB,$name)) {
			return call_user_func_array(Array($this->DB,$name),$args);
		} else {
			throw new Exception(
				"mysql: unsupported DB call: ".$name,
				DB_DATAOBJECT_ERROR_INVALID_CALL);
		}
	}
	
	public function lastInsertId() {
		return $this->lastInsertId;
	}
	
	public function quoteIdentifier($text)
	{
		return '`'.$text.'`';
	}	

	public function quote($text)
	{
		return '\''.mysql_escape_string($text).'\'';
	}	
	
	public function error($DB = NULL)
	{
		if ($DB == NULL) {
			$DB = $this->DB;
		}
		return mysql_error($DB);
	}
	
	public function checkself()
	{
      	if (is_resource($this->DB)) {
			$resource_type = get_resource_type($this->DB);	
		} else {
			$resource_type = "";
		}
		
		if ($resource_type != 'mysql link') {
			$this->DB = NULL;
			$this->start_db();
		}

		if (isset($this->writeDB)) {
			if (is_resource($this->writeDB)) {
				$resource_type = get_resource_type($this->writeDB);
			} else {
				$resource_type = "";
			}
			
			if ($resource_type != 'mysql link') {
				$this->writeDB = NULL;
				$this->start_db();
			}			
		}

		return;
	}

	public function __destruct()
	{
      	if (is_resource($this->DB)) {
			$resource_type = get_resource_type($this->DB);	
			if ($resource_type == 'mysql link') {
				mysql_close($this->DB);
			}
		}
      	if (is_resource($this->writeDB)) {
			$resource_type = get_resource_type($this->writeDB);
			if ($resource_type == 'mysql link') {
				mysql_close($this->writeDB);
			}
		}
	}

	public function escapeSimple($text) {
		return mysql_escape_string($text);
	}

	public function start_db()
	{
		parent::start_db();

		if (isset($this->_config['read'])) {
			$random_server = array_rand($this->_config['read'],1);
			$this->_config['host'] = $this->_config['read'][$random_server]['host'];
			if (isset($this->_config['read'][$random_server]['port'])) {
				$this->_config['port'] = $this->_config['read'][$random_server]['port'];
			}
		}
		
		if (isset($this->_config['port'])) {
			$server = $this->_config['host'].':'.$this->_config['port'];
		} else {
			$server = $this->_config['host'];
		}
			
		if (isset($this->_config['write'])) {
			$random_server = array_rand($this->_config['write'],1);
			$writeserver = $this->_config['write'][$random_server]['host'];
			if (isset($this->_config['write'][$random_server]['port'])) {
				$writeserver .= ':'.$this->_config['write'][$random_server]['port'];
			}

			if (!$this->writeDB = mysql_connect($writeserver,$this->_config['user'],$this->_config['pass'])) {
				throw new Exception('cant connect to server '.$writeserver);
			}

			if (!mysql_select_db($this->_config['dbname'],$this->writeDB)) {
				throw new Exception('cant change to dbname of database '.$this->_config['database'].' on server '.$writeserver);
			}

		}
		
		if (!$this->DB = mysql_connect($server,$this->_config['user'],$this->_config['pass'])) {
			throw new Exception('cant connect to server '.$server);
		}

		if (!mysql_select_db($this->_config['dbname'],$this->DB)) {
			throw new Exception('cant change to dbname of database '.$this->_config['database'].' on server '.$server);
		}
		
	}

	public function tableInfo($table)
	{
		$data = $this->query('DESCRIBE '.$this->quoteIdentifier($table).';');
		$table_info = Array();
		foreach($data as $field) {
			$flags = Array();
			$new_field = Array();
			$new_field['table'] = $table;
			$new_field['name'] = $field['Field'];
			$new_field['default'] = $field['Default'];
			if ($field['Extra'] == 'auto_increment') {
				$flags[] = 'auto_increment';
			}
			if (strpos($field['Type'],'(') !== false) {
				preg_match('/^(\w+)\((.*)\)(.*)$/',$field['Type'],$matches);
				// debug only $new_field['matches'] = $matches;
				$new_field['type'] = strtolower($matches[1]);
				if ($new_field['type'] == 'enum') {
					$new_field['values'] = $matches[2];
					$new_field['len'] = count(explode(',',$matches[2]));
				} else {
					$new_field['len'] = $matches[2]+0;
					if (!empty($matches[3])) {
						$flags[] = trim($matches[3]);
					}
				}
			} else {
				$new_field['type'] = strtolower($field['Type']);
			}

			if ($field['Null'] == 'NO') {
				$flags[] = 'not_null';
			}
			if ($field['Key'] == 'PRI') {
				$flags[] = 'primary_key';
			} elseif ($field['Key'] == 'UNI') {
				$flags[] = 'unique_key';
			}
			$new_field['flags'] = implode(' ',$flags);

			if (!isset($new_field['len'])) {
				$new_field['len'] = '';
			}

			$table_info[] = $new_field;
		}
		return $table_info;
	}

	public function setDatabase($database)
	{
		if ($this->getDatabase() != $database) {
			parent::setDatabase($database);
			mysql_close($this->DB);
			$this->checkself();
		}
		return true;
	}

	public function setCharset($Charset,$ForceResults = false) {
		$this->query('SET CHARACTER SET '.$Charset.';');
		$this->query('SET NAMES '.$Charset.';');
		$this->query('SET character_set_client = '.$Charset.';');
		$this->query('SET character_set_connection = '.$Charset.';');
		$this->query('SET character_set_database = '.$Charset.';');
		if ($ForceResults) {
			$this->query('SET character_set_results = '.$Charset.';');			
		} else {
			$this->query('SET character_set_results = NULL;');
		}
		$this->query('SET character_set_server = '.$Charset.';');
	}
	
	public function getDatabase()
	{
		return $this->_config['database'];
	}
	
	public function getDatabaseName()
	{
		return $this->_config['dbname'];
	}

	public function getListOf($type) 
	{
		
		switch ($type) {
			
			case 'tables':
				
				$result = $this->query("SHOW TABLES");
				$tables = Array();
				foreach($result as $row) {
					$tables[] = array_pop($row);
				}
				return $tables;				

			case 'users':
				
				$databases = $this->query('SELECT DISTINCT User FROM mysql.user');
				return $databases;
				
			case 'databases':
				
				$databases = $this->query("SHOW DATABASES");
				return $databases;
				
			default:
				return null;
				
		}
	}

}
