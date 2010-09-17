<?

require_once(DATAOBJECT2_PATH.'/Driver/Common.php');

class DB_DataObject2_Driver_PDO extends DB_DataObject2_Driver_Common 
{	
	protected $rowCount;	
	protected $lastInsertId;

	public static function factory($config)
	{
		if (!is_array($config)) {
			$config = DB_DataObject2_Driver_Common::peardsn2array($config);
		}
		
		if (isset($config['driver'])) {
			switch($config['driver']) {
				
				case "mysql":
					// TODO
					require_once DATAOBJECT2_PATH.'/Driver/PDO_MYSQL.php';
					return new DB_DataObject2_Driver_PDO_MYSQL($config);
					break;
					
				default:
					throw new Exception('unknown driver');
					break;
				
			}
		} else {
			throw new Exception('no driver');
		}
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
        	case 'show':
        	case 'hand':
				$result = $this->DB->query($string);
				$this->rowCount = $result->rowCount();
				$array = $result->fetchAll(PDO::FETCH_ASSOC);
				$result = NULL;
				return $array;
        	break;
		
        	default:
        		$this->rowCount = $this->DB->exec($string);
        		if ($command == 'inse') {
        			$this->lastInsertId = $this->DB->lastInsertId();
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
				"PDO: unsupported DB call: ".$name,
				DB_DATAOBJECT_ERROR_INVALID_CALL);
		}
	}
	
	public function quoteIdentifier($text)
	{
		return '`'.$text.'`';
	}	

	public function quote($text)
	{
		return $this->DB->quote($text);
	}	
	
	public function error()
	{
		return $this->DB->errorInfo();
	}
	
	public function config2pdodsn($config)
	{
		return $config['driver'].':'.'host='.$config['host'].';dbname='.$config['dbname'].';port='.$config['port'];
	}

	public function __destruct()
	{
		$this->DB = NULL;
	}
	
}

?>
