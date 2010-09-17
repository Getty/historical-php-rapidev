<?

class DB_DataObject2_Driver_Common
{	
	
	/* configuration of the driver */
	protected $_config;
	protected $_database;
	public $insertupdate = false;	
	
	public static function factory($config)
	{
		throw new Exception(
			"your driver doesn't support the factory",
			E_USER_ERROR);
	}
	
    /**
     * converts a PEAR DSN into an array
     *
     * @access public static
     * @return array
     */
    
	public static function peardsn2array($dsn) 
    {
    	preg_match('/^(\w+):\/\/([^:]+):([^@]+)@([^\/]+)\/(.*)$/', $dsn, $matches);
    	$result = Array();
    	$result['driver'] = $matches[1];
    	$result['user'] = $matches[2];
    	$result['pass'] = $matches[3];
    	$hostport = explode(':',$matches[4]);
    	$result['host'] = $hostport[0];
    	if (!empty($hostport[1])) {
    		$result['port'] = $hostport[1];
    	}
    	$result['database'] = $matches[5];
    	return $result;
    }
    	
	public function start_db()
	{
		
		if (isset(DB_DataObject2::$CONFIG)) {
			if (isset($this->_database)) {
				if (isset(DB_DataObject2::$CONFIG["database_{$this->_database}"])) {
					$this->_config = DB_DataObject2::$CONFIG["database_{$this->_database}"];
				} else {
					throw new Exception('DB_DataObject2 database config('.$this->_database.') not set',DB_DATAOBJECT_ERROR_INVALIDCONFIG);
				}
			} else {
				throw new Exception('DB_DataObject2 database not set',DB_DATAOBJECT_ERROR_INVALIDCONFIG);				
			}
		} else {
			throw new Exception('DB_DataObject2 config not loaded',DB_DATAOBJECT_ERROR_INVALIDCONFIG);
		}
		
	}

    /**
     * Getting a unique database name
     *
     * @access public
     * @return String
     */
    
    public function setDatabase($database) {
    	$this->_database = $database;
    	return true;
    }
    
	public function getDatabase() {
    	return $this->_database;
	}
	
	public function tableInfo($table) {
		throw new Exception(
			"your driver doesn't support tableInfo()",
			DB_ERROR_UNSUPPORTED);
	}
	
	public function getListOf($type) {
		throw new Exception(
			"your driver doesn't support listings",
			DB_ERROR_UNSUPPORTED);
	}

}


?>