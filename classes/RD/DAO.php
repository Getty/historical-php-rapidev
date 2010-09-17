<?

class RD_DAO extends DB_DataObject2 {
	
	public $last_query;

	protected function _query($string) {
		RDD::Log($this->_database.' | '.$string,TRACE,9999);
		$this->last_query = $string;
		return parent::_query($string);
	}
	
	public function get($k = null, $v = null) {
		parent::get($k,$v);
		return $this;
	}
		
}

