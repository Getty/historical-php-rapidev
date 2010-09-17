<?

require_once(DATAOBJECT2_PATH.'/Driver/PDO.php');

class DB_DataObject2_Driver_PDO_MYSQL extends DB_DataObject2_Driver_PDO
{	

	/* pdo connection itself */
	protected $DB;
	public $insertupdate = true;
	
	public function __construct($config)
	{
		if (isset($config['database'])) {
			$this->_database = $config['database'];
		} else {
			throw new Exception('DB_DataObject2 database not set',DB_DATAOBJECT_ERROR_INVALIDCONFIG);			
		}
		
		$this->_config = $config;
		$this->start_db();		
	}
	
	public function escapeSimple($text) {
		return mysql_escape_string($text);
	}

	public function start_db()
	{
		parent::start_db();
		
		if (isset($this->_config['additional'])) {
			$this->DB = new PDO($this->config2pdodsn($this->_config),$this->_config['user'],$this->_config['pass'],$this->_config['additional']);
		} else {
			$this->DB = new PDO($this->config2pdodsn($this->_config),$this->_config['user'],$this->_config['pass']);
		}
		$this->DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	}
	
	public function tableInfo($table)
	{
		$result = $this->DB->query('DESCRIBE '.$this->quoteIdentifier($table).';');
		$data = $result->fetchAll();
		$table_info = Array();
		foreach($data as $field) {
			$flags = Array();
			$new_field = Array();
			$new_field['table'] = $table;
			$new_field['name'] = $field['Field'];
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
		parent::setDatabase($database);
		$this->DB = null;
		$this->start_db();
		return true;
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
				
				$tables = Array();
				$result = $this->DB->query("SHOW TABLES");
				while ($data = $result->fetch(PDO::FETCH_ASSOC)) {
					$tables[] = array_pop($data);
				}
				return $tables;				

			case 'users':
				
				return 'SELECT DISTINCT User FROM mysql.user';
				
			case 'databases':
				
				$databases = Array();
				$result = $this->DB->query("SHOW DATABASES");
				while ($data = $result->fetch(PDO::FETCH_ASSOC)) {
					$databases[] = array_pop($data);
				}
				return $databases;				
				
			default:
				return null;
				
		}
	}

	
}

?>
