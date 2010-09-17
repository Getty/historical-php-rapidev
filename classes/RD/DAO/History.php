<?

RD::RequireClass('RD_DAO');

class RD_DAO_History extends RD_DAO {

	public function insert($update = false, $dataObject = false, $delayed = false) {
		$Return = parent::insert($update, $dataObject, $delayed);
		$this->MakeHistoryEntry('insert');
		return $Return;
	}

	public function update($dataObject = false,$return = false) {
		$Return = parent::update($dataObject, $return);
		$this->MakeHistoryEntry('update');
		return $Return;
	}

	public function delete($useWhere = false) {
		$Array = $this->toArray();
		$Return = parent::delete($useWhere);
		$this->MakeHistoryEntry('delete',$Array,true);
		return $Return;
	}

	public function MakeHistoryEntry($Command, $Array = false, $delete = false) {
		if (isset(DB_DataObject2::$INI[$this->_database][$this->__table.'_History'])) {
			$History = RD::$Self->DB($this->_database,$this->__table.'_History');
			if (!$Array) {
				$Array = $this->toArray();
			}
			$ID = $Array['ID'];
			unset($Array['ID']);
			$History->setFrom($Array);
			$History->History_Reference_ID = $ID;
			$History->History_Timestamp = DB_DataObject2_Cast::sql('UNIX_TIMESTAMP()');
			if ($delete) {
				$History->History_Deleted = 1;
			}
			$History_ID = $History->insert();
			if (isset(DB_DataObject2::$INI[$this->_database]['HistoryTracking'])) {
				$HistoryTracking = RD::$Self->DB($this->_database,'HistoryTracking');
				$Name = '';
				if (RD::$Self->GetUser()) {
					$HistoryTracking->User_ID = RD::$Self->GetUser('ID');
					$Name .= 'User '.RD::$Self->GetUser('Name').' does a ';
				}
				$Name .= $Command.' on table '.$this->__table.' with entry #'.$ID;
				$HistoryTracking->Name = $Name;
				$HistoryTracking->Table = $this->__table;
				$HistoryTracking->Table_ID = $ID;
				$HistoryTracking->Table_History_ID = $History_ID;
				$HistoryTracking->Query = $this->last_query;
				$HistoryTracking->insert();
			}
		}
	}

}

