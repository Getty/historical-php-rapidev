<?

abstract class RD_RapiDev_Page_CRUD extends RD_Page {

	public $Table;
	public $Database;

	public $ExtraJoins = Array();
	public $ExtraFunctions = Array();
	public $HideKeys = Array();
	public $ShowKeys = Array();

	public $TextHeadUpdate = 'update';
	public $TextHeadCreate = 'create';
	public $TextCreate = 'create';
	public $TextUpdate = 'update';
	public $TextDelete = 'delete';
	public $TextDuplicate = 'duplicate';

	public $HideListOnEdit = false;

	public $SaveNotice;

	public $ID_Of_CurrentObject;

	public function CRUD_ID() {
		if (!isset($this->Database)) {
			return $this->Table;
		} else {
			return $this->Database.$this->Table;
		}
	}

	public function CRUD_Form() {
		if (isset($this->CRUD_Form)) {
			return $this->CRUD_Form;
		}
		return strtolower($this->Table);
	}

	public function CRUD_FetchObject() {
		if (isset($this->Database)) {
			return $this->DB($this->Database,$this->Table);
		} else {
			return $this->DB($this->Table);
		}
	}

	public function CRUD_AssignTable() {
		$List = $this->CRUD_FetchObject();
		if (!empty($this->ExtraJoins)) {
			foreach($this->ExtraJoins as $Value) {
				$List->joinAdd($Value);
			}
		}
		$List->find();
		$Table = $List->fetchAllArray();
		if (!empty($this->ShowKeys)) {
			$this->ShowKeys[] = 'ID';
			foreach($Table as $Key => $Value) {
				$NewValue = Array();
				foreach($this->ShowKeys as $ShowKey) {
					if (isset($Table[$Key][$ShowKey])) {
						$NewValue[$ShowKey] = $Table[$Key][$ShowKey];
					}
				}
				$Table[$Key] = $NewValue;
			}
		}
		if (!empty($this->HideKeys)) {
			foreach($Table as $Key => $Value) {
				foreach($this->HideKeys as $HideKey) {
					if (isset($Table[$Key][$HideKey])) {
						unset($Table[$Key][$HideKey]);
					}
				}
			}
		}

		$this->Assign('CRUD_Table',$Table);
	}

	public function CRUD_CheckResult($Result) {
		return true;
	}

        public function Page_start() {

		if (!isset($this->Table)) {
			throw new RDE('RD_RapiDev_Page_CRUD needs $this->Table');
		}

		$Edit = $this->IssetRequest('CRUD_Edit_'.$this->CRUD_ID().'_ID');
		$ID = $this->Get('CRUD_Edit_'.$this->CRUD_ID().'_ID');

		$Delete = $this->IssetRequest('CRUD_Delete_'.$this->CRUD_ID().'_ID');

		$Duplicate = $this->IssetRequest('CRUD_Duplicate_'.$this->CRUD_ID().'_ID');

		$this->ID_Of_CurrentObject = $ID;

		if ($Edit) {

			$DefaultValues = Array();
			$Object = $this->CRUD_FetchObject();

			if ($ID) {

				$Object->get($ID);

				if ($this->MethodExists('CRUD_CheckObject')) {
					$this->CRUD_CheckObject($Object);
				}

				$DefaultValues = $Object->toArray();

				if ($this->MethodExists('CRUD_ModifyDefaultValues')) {
					$DefaultValues = $this->CRUD_ModifyDefaultValues($DefaultValues);
				}

			}

			$Done = false;

			$Result = $this->EasyForm($this->CRUD_Form(),$DefaultValues);

			if ($Result) {

				if ($this->MethodExists('CRUD_ModifyResultValues')) {
					$Result = $this->CRUD_ModifyResultValues($Result);
				}

				if (($CheckResultError = $this->CRUD_CheckResult($Result)) === true) {
					$Object->setFrom($Result);
					if ($ID) {
						$Object->update();
					} else {
						$Object->insert();
					}

					if ($this->MethodExists('CRUD_AfterResultObject')) {
						$this->CRUD_AfterResultObject($Object,$Result);
					}

					$this->FormClean();
					$Edit = false;
					if (isset($this->SaveNotice)) {
						$this->AssignNotice($this->SaveNotice);
					}
					$this->Save();
				} else {
					$this->FormClean($this->CRUD_Form());
					$this->EasyForm($this->CRUD_Form(),$DefaultValues);
					$this->FormError($this->CRUD_Form(),$CheckResultError);
				}

			} 

		}

		if ($Delete) {

			$Object = $this->CRUD_FetchObject();
			$Object->get($this->Get('CRUD_Delete_'.$this->CRUD_ID().'_ID'));

			if ($this->MethodExists('CRUD_CheckObject')) {
				$this->CRUD_CheckObject($Object);
			}

			$Object->delete();

			$this->UnsetGet('CRUD_Delete_'.$this->CRUD_ID().'_ID');

		}

		if ($Duplicate) {

			$FromObject = $this->CRUD_FetchObject();
			$FromObject->get($this->Get('CRUD_Duplicate_'.$this->CRUD_ID().'_ID'));

			if ($this->MethodExists('CRUD_CheckObject')) {
				$this->CRUD_CheckObject($FromObject);
			}

			$FromArray = $FromObject->toArray();
			$ToObject = $this->CRUD_FetchObject();
			unset($FromArray['ID']);
			$ToObject->setFrom($FromArray);
			$ToObject->insert();

			$this->UnsetGet('CRUD_Duplicate_'.$this->CRUD_ID().'_ID');

		}

		$this->CRUD_AssignTable();

		$this->Assign('CRUD',Array(
			'Edit' => $Edit,
			'ID' => $ID,
			'Form' => $this->CRUD_Form(),
			'Field' => 'CRUD_Edit_'.$this->CRUD_ID().'_ID',
			'DeleteField' => 'CRUD_Delete_'.$this->CRUD_ID().'_ID',
			'DuplicateField' => 'CRUD_Duplicate_'.$this->CRUD_ID().'_ID',
			'TextHeadUpdate' => $this->TextHeadUpdate,
			'TextHeadCreate' => $this->TextHeadCreate,
			'TextCreate' => $this->TextCreate,
			'TextUpdate' => $this->TextUpdate,
			'TextDelete' => $this->TextDelete,
			'TextDuplicate' => $this->TextDuplicate,
			'ExtraFunctions' => $this->ExtraFunctions,
			'HideListOnEdit' => $this->HideListOnEdit,
		));

	}

}
