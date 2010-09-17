<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Form Module
 * 
 * LICENSE:
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * 
 * @author     Torsten Raudssus <torsten@raudssus.de>
 * @copyright  2007 Torsten Raudssus
 * @license    GPL-2 
 * 
 */

require_once(RD_PATH.DIR_SEP.'plugins'.DIR_SEP.'function.form.php');

class RD_RapiDev_Module_Form extends RDM {

	public $Config;

	/*
	 * Validator Typ der leeren Inhalt verbietet (existiert nicht real)
	 */
	public static $NotEmptyType = 'notempty';
	/*
	 * Validator Typ der leeren Inhalt verbietet einschliesslich 0 und "0" (existiert nicht real)
	 */
	public static $NotNullType = 'notnull';
	/*
	 * Validator Typ für komplett zu ignorierende aber mitzuführende Elemente
	 */
	public static $UnusedElementType = 'unused';
	/*
	 * Element Typ für einen File Upload
	 */
	public static $FileuploadElementType = 'file';
	/*
	 * Element Typ für einen Multi File Upload
	 */
	public static $MultiFileuploadElementType = 'multifile';
	/*
	 * Element Typ für ein Subformular
	 */
	public static $FormElementType = 'form';
	/*
	 * Element Typ für ein multiples Subformular
	 */
	public static $MultiFormElementType = 'multiform';
	/*
	 * Maximale Anzahl von multiplen Subformularen innerhalb eines Multiform Elements
	 */
	public static $MultiFormMaximum = 100;
	public static $DefaultErrorMessage;
	public static $DefaultDescription = 'Description';
	public static $EmptyErrorMessage;
	public static $NullErrorMessage;
	public static $StripSlashesFix = true;

	/**
	 * Wenn auf true gesetzt, dann werden alle Tags aus dem FormValue entfernt:
	 * <code>
	 * RDM_Form::$StripTagsFix = true;
	 * </code>
	 * 
	 * Man kann optional auch ein Array mit erlaubten Tags angeben, diese werde ndann nicht entfernt:
	 * <code>
	 * RDM_Form::$StripTagsFix = '<p><a>';
	 * </code>
	 *
	 * @var unknown_type
	 */
	public static $StripTagsFix = false;
	public static $Extensions = Array('php','xml');
	public static $ElementArrayTypes = Array('datas','validators','prepares','performs');
	public static $ElementTextTypes = Array('text','textarea','tinymce');

	public static $RD_Functions = Array(
											'EasyForm',
											'EasyFormReset',
											'FormClean',
											'FormError',
											'FormIsSubmitted',
											'SubmitForm',
											'LoadForm',
											'PrepareForm',
											'FinalValuesForm',
											'FormPlugin',
											'OverrideForm',
										);

	public static $RD_Depencies = Array();

	protected $FormViews = Array();
	
	public $DefaultGridPageSize = 20;

	public function __construct($main) {
		self::$DefaultErrorMessage = T('FORM_VALUE_INVALID');
		self::$EmptyErrorMessage = T('FORM_VALUE_EMPTY');
		self::$NullErrorMessage = T('FORM_VALUE_EMPTY');
		parent::__construct($main);
	}
	
	public function HookPostInit() {

		if (!$this->GetSession('forms') || $this->GetGet('formclean')) {
			$this->FormClean();
		}
		if ($Config = $this->GetConfig('RDM_Form_Config')) {
			$this->Config = $Config;
		}

		RDD::Log('Session Form PostInit');
		RDD::Log($this->main->SESSION);

		return;

	}

	public function HookModuleForm() {

		$View = $this->ViewStart();

		if ($this->GetRequest('fileupload') == 'ajax') {
			$this->AjaxFileupload();
		} elseif (($Key = $this->GetRequest('multiform')) && ($MultiFormCount = $this->GetRequest('multiform_count'))) {
						
			if (!($FormName = $this->GetRequest('form'))) {
				throw new RDE('RDM_Form: multiform needs a form');
			}
			
			$forms = $this->GetSession('forms');
			
			if (!isset($forms[$FormName])) {
				throw new RDE('RDM_Form: form '.$FormName.' doesnt exist');
			}

			$MultiSubformNameBase = $FormName.'_'.$Key;
			$this->main->GET[$MultiSubformNameBase.'_count'] = $MultiFormCount;
			
			$this->PrepareForm($FormName);
			$this->Assign('LoadForm',$MultiSubformNameBase.'_'.($MultiFormCount-1));
			$this->AssignForms($View);

			$this->SetTemplate('loadform.tpl');
			
		} elseif ($GridPage = $this->GetRequest('gridpage')) {

			if ($FormName = $this->GetRequest('form')) {
				throw new RDE('RDM_Form: grid needs a form');
			} elseif (!isset($this->main->SESSION['forms'][$FormName])) {
				throw new RDE('RDM_Form: form '.$this->main->GET['form'].' doesnt exist');
			}
			
			if (!isset($this->main->GET['element'])) {
				throw new RDE('RDM_Form: grid needs an element');
			} elseif (!isset($this->main->SESSION['forms'][$FormName]['elements'][$this->main->GET['element']])) {
				throw new RDE('RDM_Form: element '.$this->main->GET['element'].' doesnt exist in form '.$FormName);				
			} elseif (!isset($this->main->SESSION['forms'][$FormName]['elements'][$this->main->GET['element']]['data'])) {
				throw new RDE('RDM_Form: element '.$this->main->GET['element'].' doesnt has data');
			}
			$ElementName = $this->main->GET['element'];
			
			if (!isset($this->main->GET['gridsize'])) {
				throw new RDE('RDM_Form: grid needs a gridsize');
			}
			$GridSize = $this->main->GET['gridsize'];
			
			$Offset = $GridSize * $GridPage;
			
			$Data = array_splice($this->main->SESSION['forms'][$FormName]['elements'][$ElementName]['data'],$Offset,$GridSize,false);
			
			$this->Assign('form',$this->main->SESSION['forms'][$FormName]);
			$this->Assign('data',$Data);
			$this->SetTemplate('form'.DIR_SEP.'elements'.DIR_SEP.'grid'.DIR_SEP.'rows.tpl');

		}

		$this->Save();
		
		$this->ViewFinish();
		
		return;
	}
	
	public function AjaxFileupload() {

		$FormName = $this->GetRequest('form');
		$ElementName = $this->GetRequest('element');
		
		$Form = $this->main->SESSION['forms'][$FormName];
		$Element = $this->main->SESSION['forms'][$FormName]['elements'][$ElementName];

		$Key = $ElementName;
		
		if (strtolower($Element['type']) == strtolower(self::$FileuploadElementType)) {
			$Field = $Element['field'];
			RDD::Log('Fileupload for '.$Field,TRACE,1203);
			$UploadCacheDirectory = $this->GetCacheDir().DIR_SEP.'forms'.DIR_SEP.session_id().$FormName.$Key;
			if (!is_dir($this->GetCacheDir().DIR_SEP.'forms')) {
				mkdir($this->GetCacheDir().DIR_SEP.'forms');
				chmod($this->GetCacheDir().DIR_SEP.'forms', 0777);
			}
			if (!is_dir($UploadCacheDirectory)) {
				mkdir($UploadCacheDirectory);
				chmod($UploadCacheDirectory, 0777);
			}
			if ($this->GetRequest($Element['field'].'_0_remove')) {
				unset($this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore']);
				unset($this->main->SESSION['forms'][$FormName]['elements'][$Key]['value']);
			}
			if (isset($_FILES[$Field]) && !empty($_FILES[$Field]['name'])) {
				$ConvertedUploadedFile = Array();
				$ConvertedUploadedFile['name'] = $_FILES[$Field]['name'];
				$ConvertedUploadedFile['type'] = $_FILES[$Field]['type'];
				$ConvertedUploadedFile['error'] = $_FILES[$Field]['error'];
				$ConvertedUploadedFile['size'] = $_FILES[$Field]['size'];
				$NewFilename = str_replace('/','_',$_FILES[$Field]['tmp_name']);
				copy($_FILES[$Field]['tmp_name'], $UploadCacheDirectory.DIR_SEP.$NewFilename);
				$ConvertedUploadedFile['tmp_name'] = $UploadCacheDirectory.DIR_SEP.$NewFilename;
				$this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'] = Array($ConvertedUploadedFile);
			} elseif (!empty($this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'])) {
				$this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'] = $this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'];
			} else {
				$this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'] = '';
			}
		} elseif (strtolower($Element['type']) == strtolower(self::$MultiFileuploadElementType)) {
			$Field = $Element['field'];
			RDD::Log('Multi Fileupload for '.$Field,TRACE,1203);
			$UploadedFiles = Array();
			$UploadCacheDirectory = $this->GetCacheDir().DIR_SEP.'forms'.DIR_SEP.session_id().$FormName.$Key;
			if (!is_dir($this->GetCacheDir().DIR_SEP.'forms')) {
				mkdir($this->GetCacheDir().DIR_SEP.'forms');
				chmod($this->GetCacheDir().DIR_SEP.'forms', 0777);
			}
			if (!is_dir($UploadCacheDirectory)) {
				mkdir($UploadCacheDirectory);
				chmod($UploadCacheDirectory, 0777);
			}
			if (!empty($this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'])) {
				foreach($this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'] as $FileKey => $UploadedFile) {
					if ($this->GetRequest($Element['field'].'_'.$FileKey.'_remove')) {
						unset($this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'][$FileKey]);
					} else {
						$UploadedFiles[] = $UploadedFile;
					}
				}
			}
			if (isset($_FILES[$Field])) {
				if (is_array($_FILES[$Field]['name']) && !empty($_FILES[$Field]['name'])) {
					$NewUploadKeys = array_keys($_FILES[$Field]['name']);
					$NewUploadedFiles = Array();
					foreach($NewUploadKeys as $NewUploadKey) {
						if (empty($_FILES[$Field]['name'][$NewUploadKey])) {
							continue;
						}
						$NewUploadedFile = Array(
							'name' => $_FILES[$Field]['name'][$NewUploadKey],
							'type' => $_FILES[$Field]['type'][$NewUploadKey],
							'tmp_name' => $_FILES[$Field]['tmp_name'][$NewUploadKey],
							'error' => $_FILES[$Field]['error'][$NewUploadKey],
							'size' => $_FILES[$Field]['size'][$NewUploadKey],
							'md5' => md5(file_get_contents($_FILES[$Field]['tmp_name'][$NewUploadKey])),
						);
						$NewUploadedFiles[] = $NewUploadedFile;
					}
					$ConvertedUploadedFiles = Array();
					foreach($NewUploadedFiles as $NewUploadedFile) {
						$Double = false;
						foreach($UploadedFiles as $UploadedFile) {
							if (!isset($UploadedFile['md5'])) {
								$UploadedFile['md5'] = md5(file_get_contents($UploadedFile['tmp_name'][$NewUploadKey]));
							}
							if ($UploadedFile['md5'] == $NewUploadedFile['md5']) {
								$Double = true;
								break;
							}
						}
						if ($Double) {
							RDD::Log('Double Content, aehm, i mean, Double Picture',WARN);
							continue;
						}
						$ConvertedUploadedFile = Array();
						$ConvertedUploadedFile['name'] = $NewUploadedFile['name'];
						$ConvertedUploadedFile['type'] = $NewUploadedFile['type'];
						$ConvertedUploadedFile['error'] = $NewUploadedFile['error'];
						$ConvertedUploadedFile['size'] = $NewUploadedFile['size'];
						$ConvertedUploadedFile['md5'] = $NewUploadedFile['md5'];
						$NewFilename = str_replace('/','_',$NewUploadedFile['tmp_name']);
						copy($NewUploadedFile['tmp_name'], $UploadCacheDirectory.DIR_SEP.$NewFilename);
						$ConvertedUploadedFile['tmp_name'] = $UploadCacheDirectory.DIR_SEP.$NewFilename;
						$UploadedFiles[] = $ConvertedUploadedFile;
					}
				}
			}
			if (empty($UploadedFiles)) {
				$UploadedFiles = '';
			}
			$this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'] = $UploadedFiles;
		}

		if (!empty($this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'])) {
			if (strtolower($Element['type']) == strtolower(self::$FileuploadElementType)) {
				$this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'] = $this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'];
			} elseif (strtolower($Element['type']) == strtolower(self::$MultiFileuploadElementType)) {
				$this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'] = $this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'];
			}
		}
		
		if (isset($Element['function']) && !empty($this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'])) {
			$Function = 'form_filesbefore_'.$Element['function'];
			$this->main->SESSION['forms'][$FormName]['elements'][$Key] = $Function($this->main->SESSION['forms'][$FormName]['elements'][$Key]);
		}
		
		$ReturnArray = Array();
		$FilesArray = $this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'];		
		$FoundUpload = false;

		if (is_array($FilesArray)) {
			foreach($FilesArray as $SubKey => $SubArray) {
				unset($FilesArray[$SubKey]['tmp_name']);
				foreach($_FILES as $Up) {
					if (is_array($Up['name'])) {
						$FileNames = $Up['name'];						
					} else {
						$FileNames = Array($Up['name']);
					}
					foreach($FileNames as $FileName) {
						if ($SubArray['name'] == $FileName) {
							$FoundUpload = true;
						}
					}
				}
			}
		}
		$ReturnArray['files'] = $FilesArray;
		$ReturnArray['found'] = $FoundUpload;

		$View = $this->View('FilesBeforeForm-'.$FormName.'-Element-'.$ElementName);
		$View->SetTemplate('tpl:<@ form name='.$FormName.' element='.$ElementName.' template="form/elements/file/filesbefore.tpl" @>');
		$ReturnArray['filesbefore'] = urlencode($this->ViewExecute($View,true));
	
		echo json_encode($ReturnArray);
		
	}
	
	public function HookUserLogout() {
		$this->FormClean();
		return;
	}
	
	public static function realempty($var) {
		return !(trim($var) !== '' && $var !== 0 && $var !== '0' && $var !== NULL);
	}
	
	public function ExecuteForm($FormName) {
	}
	
	public function HookViewExecute($View) {
		return $this->AssignForms($View);
	}
	
	public function AssignForms($View) {
		if (!isset($this->main->SESSION['forms'])) {
			$this->main->SESSION['forms'] = Array();
		}
		$View->Assign('forms',$this->main->SESSION['forms']);
		return;		
	}
	
	public function EasyForm($FormName, $DefaultValues = Array(), $Override = Array(), $FormArray = NULL, $Reset = false) {
		$this->PrepareForm($FormName, $FormArray, $Reset, $Override);
		if (!empty($DefaultValues)) {
			$this->ValuesForm($FormName, $DefaultValues, false);
		}
		$Return = false;
		if ($this->FormIsSubmitted($FormName) && $this->ValidateForm($FormName)) {
			$this->UnsubmitForm($FormName);
			$this->FinishForm($FormName);
			return $this->FinalValuesForm($FormName);
		}
		$this->FinishForm($FormName);
		return $Return;
	}

    public function EasyFormReset($FormName, $DefaultValues = Array(), $Override = Array(), $FormArray = NULL) {
        if ($Result = $this->EasyForm($FormName, $DefaultValues, $Override, $FormArray)) {
            $this->FormClean($FormName);
            $this->EasyForm($FormName, $DefaultValues, $Override, $FormArray,true);
            return $Result;
        }
        return false;
    }

	public function FormClean($FormName = null) {

		if ($FormName === null) {
			$this->main->SESSION['forms'] = Array();
		} else {
			unset($this->main->SESSION['forms'][$FormName]);	        
		}

		$this->main->SESSION['forms']['formtypes'] = Array(self::$FormElementType,self::$MultiFormElementType);

		return;

	}

	public function FormIsSubmitted($FormName) {
		$SubmitValue = $this->GetRequest($FormName);
		if (!empty($SubmitValue)) {
			return true;
		} else {
			return false;
		}
	}

	public function UnsubmitForm($FormName) {
		if (isset($this->main->POST[$FormName])) {
			unset($this->main->POST[$FormName]);
		}
		return;
	}

	public function SubmitForm($FormName) {
		$this->main->POST[$FormName] = 'submitted';
		return;
	}

	public function FetchData($data,$count = false) {
		if ($data['type'] == 'dao') {
			if (!isset($this->main->Modules['dao'])) {
				throw new RDE('RDM_Form: data source dao needs dao module loaded');
			}
			if (!isset($data['database'])) {
				$database = $this->main->Modules['dao']->GetOption('default_database');
				if (!$database) {
					throw new RDE('RDM_Form: data source dao needs database');
				}
				$data['database'] = $database;
			}
			if (isset($data['description'])) {
				$description = $data['description'];
				unset($data['description']);
			} else {
				$description = self::$DefaultDescription;
			}
			if (isset($data['key'])) {
				$key = $data['key'];
				unset($data['key']);
			}
			if (isset($data['addempty'])) {
				$addempty = $data['addempty'];
				unset($data['addempty']);
				if (isset($data['emptykey'])) {
					$emptykey = $data['emptykey'];
				} else {
					$emptykey = "";
				}
			}
			if (isset($addempty)) {
				$new = Array();
				$new[$description] = $addempty;
				$DataArray[$emptykey] = $new;
			} else {
				$DataArray = Array();
			}
			if (isset($data['sql'])) {
				if (!isset($key)) {
					throw new RDE('RDM_Form: doesnt support auto-index on sql statements');
				}
				$DB = $this->DB($data['database'],$data['sql']);
				while($DB->fetch()) {
					$DataArray[$DB->$key] = $DB->toArray();
				}
			} else {
				if (!isset($data['table'])) {
					throw new RDE('RDM_Form: data source dao without sql needs table');
				}
				$DB = $this->DB($data['database'],$data['table']);
				if (!isset($key)) {
					if (!isset($key)) {
						$keys = $DB->keys();
						if (count($keys) == 1) {
							$key = $keys[0];
						} else {
							throw new RDE('RDM_Form: doesnt support auto-index on multiply key tables by now');
						}
					}
					if (isset($data['select'])) {
						$DB->selectAdd();
						$DB->selectAdd($data['select']);
						unset($data['select']);
					}
					if (isset($data['selectadd'])) {
						$DB->selectAdd($data['selectadd']);
						unset($data['selectadd']);
					}
					if (isset($data['orderby'])) {
						$DB->orderBy($data['orderby']);
						unset($data['orderby']);
					}
					if (isset($data['whereadd'])) {
						$DB->whereAdd($data['whereadd']);
						unset($data['whereadd']);
					}
					if (isset($data['limit'])) {
						$limit = $data['limit'];
						unset($data['limit']);
					}
					if (isset($data['limit_offset'])) {
						$limit_offset = $data['limit_offset'];
						unset($data['limit_offset']);
					} elseif (isset($limit)) {
						$limit_offset = 0;
					}
					if (isset($data['join'])) {
						$join = $data['join'];
						unset($data['join']);
						$joinDB = $this->main->Modules['dao']->DB($data['database'],$join);
						$DB->joinAdd($joinDB);
					}
					unset($data['table']);
					unset($data['database']);
					unset($data['type']);
					$DB->setFrom($data);
					if ($count) {
						return $DB->count();
					} else {
						if (isset($limit)) {
							$DB->limit($limit_offset,$limit);
						}
						if ($DB->find()) {
							while ($DB->fetch()) {
								$DataArray[$DB->$key] = $DB->toArray();
							}
						}
					}
				}
			}
		} else {
			if ($DataClassFile = $this->DataSourceExist($data['type'])) {
				require_once($DataClassFile);
				$DataClass = 'RD_Data_'.ucfirst($data['type']);
				$DataObj = new $DataClass($this->main);
				$DataArray = $DataObj->Start($data,$count);
			} else {
				throw new RDE('RDM_Form: unsupported data source type');				
			}
		}
		return $DataArray;
	}

	public function DataSourceExist($DataSource) {
		return $this->File('datas'.DIR_SEP.$DataSource.'.php',false);
	}

	public function PrepareFormFieldData($FormName,$Key) {
		
		/**
		 * Data prepare
		 */
		
		$Form = &$this->main->SESSION['forms'][$FormName];
		$Element = &$Form['elements'][$Key];

		if (!isset($Element['data'])) {
			if (isset($Element['values'])) {
				$Element['data'] = $Element['values'];
			} elseif (isset($Element['datas'])) {
				if (count($Element['datas']) > 1) {
					throw new RDE('RDM_Form: doesnt support multiply data sources by now');
				}
				foreach($Element['datas'] as $DataKey => $data) {
					if (isset($Element['pagesize'])) {
						$Pagesize = $Element['pagesize']+0;
						$data['limit'] = $Pagesize;
						$count = $this->FetchData($data,true);
						$Element['data_count'] = $count;
						$Maxpage = ceil($count/$Pagesize);
						$Element['pages'] = range(1,$Maxpage);
						$Element['maxpage'] = $Maxpage;
						$Element['page'] = 1;
					}
					if (isset($data['description'])) {
						$description = $data['description'];
					} else {
						$description = self::$DefaultDescription;
					}
					$Element['description'] = $description;
					$Element['data'] = $this->FetchData($data);
				}
			}
		} else {
			if (isset($Element['pagesize'])) {
				$Pagesize = $Element['pagesize']+0;
				if (!($ElementPage = $this->Get($Element['field'].'_page'))) {
					$ElementPage = 1;
				}
				if (!isset($Element['page'])) {
					$Element['page'] = 1;
				}
				if ($Element['page'] != $ElementPage) {
					if (isset($Element['datas'])) {
						if (count($Element['datas']) > 1) {
							throw new RDE('RDM_Form: doesnt support multiply data sources by now');
						}
						foreach($Element['datas'] as $DataKey => $data) {
							$data['limit'] = $Pagesize;
							$data['limit_offset'] = ($ElementPage-1) * $Pagesize;
							$Form['elements'][$Key]['data'] = $this->FetchData($data);
						}
					}
					$Element['page'] = $ElementPage;
				}
			}
		}

		// var_dump($Form['elements'][$Key]['data']);

		return;
	}
	
	protected function prepareFormFieldForm($FormName,$Key,$Element,$Reset) {
			
		$SubformName = $FormName.'_'.$Key;
		
		if (isset($this->main->SESSION['forms'][$FormName]) && !$Reset) {
			
			if (!isset($Element['form'])) {
				throw new RDE('RDM_Form: form element needs a form name');
			}
		
			$FormArray = $this->prepareFormFieldLoadForm($Element);
			
			$this->PrepareForm($SubformName,$FormArray,$Reset);
			
			$Form = &$this->main->SESSION['forms'][$FormName];
			$Form['elements'][$Key]['subformname'] = $SubformName;
			
		}
		
		return;
	}
	
	protected function prepareFormFieldMultiForm($FormName,$Key,$Element,$Reset) {

		if (!isset($Element['form'])) {
			throw new RDE('RDM_Form: form element needs a form name');
		}

		$MultiSubformNameBase = $FormName.'_'.$Key;
		$Count = $this->Get($MultiSubformNameBase.'_count');

		RDD::Log('got for multiform '.$FormName.' '.$Element['id'].' the count: '.$Count,TRACE,1202);

		if ($Count === false) {
			if (isset($Element['count'])) {
				$Count = $Element['count'];
			} else {
				$Count = 0;
			}
		} else {
			$Count = $Count + 0;
		}
	
		if ($Count > self::$MultiFormMaximum) {
			throw new RDE('form module doesnt allow more then '.self::$MultiFormMaximum.' subforms in a multiform');
		}
		
		$Form = &$this->main->SESSION['forms'][$FormName];

		$Form['elements'][$Key]['count'] = $Count;
		
		if ($Count > 0) {
			$Form['elements'][$Key]['counter'] = range(0,$Count-1);
			$MultiSubformNameBase = $FormName.'_'.$Key;
			$Form['elements'][$Key]['multisubformnamebase'] = $MultiSubformNameBase;
			$Form['elements'][$Key]['multisubforms'] = Array();
			
			$FormArray = $this->prepareFormFieldLoadForm($Element);
			
			for ($i = 0; $i < $Count; $i++) {
				$NewFormName = $MultiSubformNameBase.'_'.$i;
				$Form['elements'][$Key]['multisubforms'][$i] = $NewFormName;
				$this->PrepareForm($NewFormName,$FormArray,$Reset);
			}
		}

		return;
	}
	
	protected function prepareFormFieldLoadForm($Element) {
		$FormArray = Array();
		
		if (is_string($Element['form'])) {
			$FormArray = $this->LoadForm($Element['form']);
		} elseif (is_array($Element['form'])) {
			$FormArray = $Element['form'];
		}
			
		if (isset($Element['formtemplate'])) {
			$FormArray['template'] = $Element['formtemplate'];
		} elseif (!isset($FormArray['template'])) {
			$FormArray['template'] = 'form'.DIR_SEP.'elements.tpl';
		}
		
		return $FormArray;
	}
	
	protected function prepareFormField($FormName,$Key,$Element,$Reset) {

		RDD::Log('Preparing form field '.$Key.' for '.$FormName,TRACE,1200);
		$Form = &$this->main->SESSION['forms'][$FormName];
		
		if (!isset($Form['elements'][$Key]['full'])) {
			$Form['elements'][$Key]['full'] = false;
		}
		
		/**
		 * Prepare the field name (id/name) for the POST/GET request, if unset (hint: override)
		 */
		
		if (!isset($Form['elements'][$Key]['field'])) {
			$Form['elements'][$Key]['field'] = $FormName.'_'.$Key;
		}

		if (!isset($Form['elements'][$Key]['errorname'])) {
			if (isset($Element['name'])) {
				$Form['elements'][$Key]['errorname'] = $Element['name'];
			} else {
				$Form['elements'][$Key]['errorname'] = $Element['id'];
			}
		}

		if (strtolower($Element['type']) == strtolower(self::$FormElementType)) {
			
			$this->prepareFormFieldForm($FormName,$Key,$Element,$Reset);
			$Form['elements'][$Key]['full'] = true;
			
		} elseif (strtolower($Element['type']) == strtolower(self::$MultiFormElementType)) {
		
			$this->prepareFormFieldMultiForm($FormName,$Key,$Element,$Reset);
			$Form['elements'][$Key]['full'] = true;
			
		} elseif (strtolower($Element['type']) == strtolower(self::$UnusedElementType) /*|| isset($Element['disabled'])*/) {
			// explicit does nothing
			
		} else {
			
			if ($Element['type'] == 'grid') {
				if (!isset($Form['elements'][$Key]['gridsize'])) {
					$Form['elements'][$Key]['gridsize'] = $this->DefaultGridPageSize;
				}
			}
			
			/**
			 * Data prepare
			 */

			$this->PrepareFormFieldData($FormName,$Key);

		}

		return;
	}

	/**
	 * Zum überschreiben der Variablen in einem schon laufenden Form Array
	 * 
	 * TODO: SubForm und MultiForm Support
	 *
	 * @param string $FormName
	 * @param Array $Override
	 */
	
	public function OverrideForm($FormName,$Override) {
		$Form = &$this->main->SESSION['forms'][$FormName];
		if (isset($Override['elements'])) {
			RDD::Log('found elements');
			foreach($Override['elements'] as $key => $element) {
				RDD::Log('element '.$key);
				if (strpos($key,'!') === false) {
					foreach($element as $elementkey => $elementvalue) {
						RDD::Log('elementkey '.$elementkey);
						if (in_array($elementkey,self::$ElementArrayTypes)) {
							foreach($elementvalue as $arraykey => $arrayvalue) {
								RDD::Log('arraykey '.$arraykey);
								if (strpos($arraykey,'!') === false) {
									foreach($arrayvalue as $subarraykey => $subarrayvalue) {
										RDD::Log('subarraykey '.$subarraykey);
										if (strpos($elementkey,'!') === false) {
											$Form['elements'][$key][$elementkey][$arraykey][$subarraykey] = $subarrayvalue;
										} else {
											unset($Form['elements'][$key][$elementkey][$arraykey][$subarraykey]);
										}
									}
								} else {
									unset($Form['elements'][$key][$elementkey][$arraykey]);
								}
							}
						} else {
							RDD::Log('element '.$elementkey.' not array');
							if (strpos($elementkey,'!') === false) {
								$Form['elements'][$key][$elementkey] = $elementvalue;								
							} else {
								unset($Form['elements'][$key][$elementkey]);
							}
						}
					}
				} elseif (isset($Form['elements'][$key])) {
					unset($Form['elements'][$key]);
				}				
			}
			unset($Override['elements']);
		}
		foreach($Override as $formkey => $formvalue) {
			if (strpos($formkey,'!') === false) {
				$Form[$formkey] = $formvalue;
			} elseif (isset($Form[$formkey])) {
				unset($Form[$formkey]);
			}
		}
		return;
	}

	public function FinishForm($FormName) {
		RDD::Log('FinishForm '.$FormName,TRACE,1200);
		$Form = &$this->main->SESSION['forms'][$FormName];
		foreach($Form['elements'] as $Key => $Element) {
			
			if (empty($Form['elements'][$Key]['value']) && isset($Form['elements'][$Key]['defaultvalue'])) {
				$Form['elements'][$Key]['value'] = $Form['elements'][$Key]['defaultvalue'];
			}

			if ($Element['type'] == self::$FormElementType) {

				$SubformName = $FormName.'_'.$Form['elements'][$Key]['id'];

				$SubformResult = $this->FinishForm($SubformName);

			} elseif ($Element['type'] == self::$MultiFormElementType) {

				$Count = $Element['count'];

				if ($Count > 0) {

					$MultiSubformNameBase = $FormName.'_'.$Key;

					for ($i = 0; $i < $Count; $i++) {
						$this->FinishForm($MultiSubformNameBase.'_'.$i);
					}
				}
			}
		}
	}

	/**
	 * Die Kernfunktion die ein Formular ins System lüdt
	 *
	 * @param string $FormName
	 * @param array $FormArray
	 * @param boolean $Reset
	 * @param array $Override
	 */	
	public function PrepareForm($FormName, $FormArray = NULL, $Reset = false, $Override = Array()) {
		RDD::Log('Preparing form '.$FormName,TRACE,1200);
		if (isset($this->main->SESSION['forms'][$FormName]) && !$Reset) {
			RDD::Log('Form '.$FormName.' already prepared',TRACE,1201);
		} else {
			if (is_string($FormArray)) {
				$FormArray = $this->LoadForm($FormArray);
			}
			if ($FormArray === NULL) {
				$this->main->SESSION['forms'][$FormName] = $this->LoadForm($FormName);
			} elseif (is_array($FormArray)) {
				$this->main->SESSION['forms'][$FormName] = $FormArray;
			} else {
				throw new RDE('form module has no form array');
			}
			$Form = &$this->main->SESSION['forms'][$FormName];
			$Form['id'] = $FormName;
			foreach($Form as $Key => $Value) {
				$Function = 'form_param_'.$Key.'_prepare';
				if (function_exists($Function)) {
					$Function($this->main,$FormName);
				}
			}
			if (!empty($Override)) {
				$this->OverrideForm($FormName,$Override);
			}
			foreach($Form['elements'] as $Key => $Element) {
				$this->prepareFormField($FormName,$Key,$Element,$Reset);
			}
		}
		
		foreach($this->main->SESSION['forms'][$FormName]['elements'] as $Key => $Element) {
		
			$type_function = 'form_type_'.$Element['type'];
		
			if (function_exists($type_function)) {
				$this->main->SESSION['forms'][$FormName]['elements'][$Key] = $type_function($this->main->SESSION['forms'][$FormName]['elements'][$Key]);
			}
		
			if (strtolower($Element['type']) == strtolower(self::$FormElementType)) {
				$this->prepareFormFieldForm($FormName,$Key,$Element,$Reset);
			} elseif (strtolower($Element['type']) == strtolower(self::$MultiFormElementType)) {
				$this->prepareFormFieldMultiForm($FormName,$Key,$Element,$Reset);
			} elseif (strtolower($Element['type']) == strtolower(self::$FileuploadElementType)) {
				$Field = $Element['field'];
				RDD::Log('Fileupload for '.$Field,TRACE,1203);
				$UploadCacheDirectory = $this->GetCacheDir().DIR_SEP.'forms'.DIR_SEP.session_id().$FormName.$Key;
				if (!is_dir($this->GetCacheDir().DIR_SEP.'forms')) {
					mkdir($this->GetCacheDir().DIR_SEP.'forms');
					chmod($this->GetCacheDir().DIR_SEP.'forms', 0777);
				}
				if (!is_dir($UploadCacheDirectory)) {
					mkdir($UploadCacheDirectory);
					chmod($UploadCacheDirectory, 0777);
				}
				if ($this->GetRequest($Element['field'].'_0_remove')) {
					unset($this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore']);
					unset($this->main->SESSION['forms'][$FormName]['elements'][$Key]['value']);
				}
				if (isset($_FILES[$Field]) && !empty($_FILES[$Field]['name'])) {
					$ConvertedUploadedFile = Array();
					$ConvertedUploadedFile['name'] = $_FILES[$Field]['name'];
					$ConvertedUploadedFile['type'] = $_FILES[$Field]['type'];
					$ConvertedUploadedFile['error'] = $_FILES[$Field]['error'];
					$ConvertedUploadedFile['size'] = $_FILES[$Field]['size'];
					$NewFilename = str_replace('/','_',$_FILES[$Field]['tmp_name']);
					copy($_FILES[$Field]['tmp_name'], $UploadCacheDirectory.DIR_SEP.$NewFilename);
					$ConvertedUploadedFile['tmp_name'] = $UploadCacheDirectory.DIR_SEP.$NewFilename;
					$this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'] = Array($ConvertedUploadedFile);
				} elseif (!empty($this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'])) {
					$this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'] = $this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'];
				} else {
					$this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'] = '';
				}
			} elseif (strtolower($Element['type']) == strtolower(self::$MultiFileuploadElementType)) {
				$Field = $Element['field'];
				RDD::Log('Multi Fileupload for '.$Field,TRACE,1203);
				$UploadedFiles = Array();
				$UploadCacheDirectory = $this->GetCacheDir().DIR_SEP.'forms'.DIR_SEP.session_id().$FormName.$Key;
				if (!is_dir($this->GetCacheDir().DIR_SEP.'forms')) {
					mkdir($this->GetCacheDir().DIR_SEP.'forms');
					chmod($this->GetCacheDir().DIR_SEP.'forms', 0777);
				}
				if (!is_dir($UploadCacheDirectory)) {
					mkdir($UploadCacheDirectory);
					chmod($UploadCacheDirectory, 0777);
				}
				if (!empty($this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'])) {
					foreach($this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'] as $FileKey => $UploadedFile) {
						if ($this->GetRequest($Element['field'].'_'.$FileKey.'_remove')) {
							unset($this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'][$FileKey]);
						} else {
							$UploadedFiles[] = $UploadedFile;
						}
					}
				}
				if (isset($_FILES[$Field])) {
					if (is_array($_FILES[$Field]['name']) && !empty($_FILES[$Field]['name'])) {
						$NewUploadKeys = array_keys($_FILES[$Field]['name']);
						$NewUploadedFiles = Array();
						foreach($NewUploadKeys as $NewUploadKey) {
							if (empty($_FILES[$Field]['name'][$NewUploadKey])) {
								continue;
							}
							$NewUploadedFile = Array(
								'name' => $_FILES[$Field]['name'][$NewUploadKey],
								'type' => $_FILES[$Field]['type'][$NewUploadKey],
								'tmp_name' => $_FILES[$Field]['tmp_name'][$NewUploadKey],
								'error' => $_FILES[$Field]['error'][$NewUploadKey],
								'size' => $_FILES[$Field]['size'][$NewUploadKey],
								'md5' => md5(file_get_contents($_FILES[$Field]['tmp_name'][$NewUploadKey])),
							);
							$NewUploadedFiles[] = $NewUploadedFile;
						}
						$ConvertedUploadedFiles = Array();
						foreach($NewUploadedFiles as $NewUploadedFile) {
							$Double = false;
							foreach($UploadedFiles as $UploadedFile) {
								if ($UploadedFile['md5'] == $NewUploadedFile['md5']) {
									$Double = true;
									break;
								}
							}
							if ($Double) {
								RDD::Log('Double Content, aehm, i mean, Double Picture',WARN);
								continue;
							}
							$ConvertedUploadedFile = Array();
							$ConvertedUploadedFile['name'] = $NewUploadedFile['name'];
							$ConvertedUploadedFile['type'] = $NewUploadedFile['type'];
							$ConvertedUploadedFile['error'] = $NewUploadedFile['error'];
							$ConvertedUploadedFile['size'] = $NewUploadedFile['size'];
							$ConvertedUploadedFile['md5'] = $NewUploadedFile['md5'];
							$NewFilename = str_replace('/','_',$NewUploadedFile['tmp_name']);
							copy($NewUploadedFile['tmp_name'], $UploadCacheDirectory.DIR_SEP.$NewFilename);
							$ConvertedUploadedFile['tmp_name'] = $UploadCacheDirectory.DIR_SEP.$NewFilename;
							$UploadedFiles[] = $ConvertedUploadedFile;
						}
					}
				}
				if (empty($UploadedFiles)) {
					$UploadedFiles = '';
				}
				if (is_array($UploadedFiles)) {
					foreach($UploadedFiles as $UploadKey => $UploadValue) {
						if ($this->GetRequest($Element['field'].'_'.$UploadKey.'_checkbox_isthere')) {
							if (!$this->GetRequest($Element['field'].'_'.$UploadKey.'_checkbox')) {
								unset($UploadedFiles[$UploadKey]);
							}
						}
					}
				}
				$this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'] = $UploadedFiles;
			} else {
				$Field = $Element['field'];
				RDD::Log('Searching in framework for '.$Field,TRACE,1203);
				if ((($Value = $this->Get($Field)) !== false) && !$Reset) {
					$this->UnsetPost($Field);
					$this->UnsetGet($Field);
					RDD::Log('Found',TRACE,1202);
					if (self::$StripSlashesFix 
						&& get_magic_quotes_gpc()
						&& in_array($this->main->SESSION['forms'][$FormName]['elements'][$Key]['type'],self::$ElementTextTypes)) {
							$Value = stripslashes($Value);
					}
					// xss:
					if (self::$StripTagsFix 
						&& in_array($this->main->SESSION['forms'][$FormName]['elements'][$Key]['type'],self::$ElementTextTypes)) {
							if (is_array(self::$StripTagsFix) && !empty(self::$StripTagsFix)) {
								$Value = strip_tags($Value, self::$StripTagsFix);
							} else {
								$Value = strip_tags($Value);
							}
					}					
					$this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'] = $Value;
				} elseif (!isset($Form['elements'][$Key]['value'])) {
					RDD::Log($Key.': no value given at all, setting empty',TRACE,1202);
					$this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'] = '';
				} elseif ($this->Get($Field) === false) {
					RDD::Log($Key.': not Found, setting empty',TRACE,1202);
					$Value = '';
					$this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'] = '';
				}
				RDD::Log('Resulting value: "'.$this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'].'"',TRACE,1203);
			}
			if (isset($Element['pagesize'])) {
				$this->PrepareFormFieldData($FormName,$Key);
			}
			if (!empty($this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'])) {
				if (strtolower($Element['type']) == strtolower(self::$FileuploadElementType)) {
					$this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'] = $this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'];
				} elseif (strtolower($Element['type']) == strtolower(self::$MultiFileuploadElementType)) {
					$this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'] = $this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'];
				}
			}
			if (isset($Element['function']) && !empty($this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'])) {
				$Function = 'form_filesbefore_'.$Element['function'];
				$this->main->SESSION['forms'][$FormName]['elements'][$Key] = $Function($this->main->SESSION['forms'][$FormName]['elements'][$Key]);
			}
		}
		
		return;
	}

	public function ValuesForm($FormName, $Values = Array(), $Override = true) {
		if (!empty($Values) && is_array($Values)) {
			RDD::Log('values given',TRACE,1201);
			if (isset($this->main->SESSION['forms'][$FormName])) {
				$Form = &$this->main->SESSION['forms'][$FormName];
				foreach($Values as $Key => $Value) {
					if (isset($Form['elements'][$Key])) {

						$Element = &$Form['elements'][$Key];
						if (!isset($Element['valuesformvalue'])) {
                            
							$Prepares = Array();
								
							if (isset($Element['prepares'])) {
								foreach($Element['prepares'] as $Prepare) {
									$Prepares[] = $Prepare['type'];
								}
							}
							
							if (isset($this->Config['DefaultPrepares'][$Element['type']])) {
								foreach($this->Config['DefaultPrepares'][$Element['type']] as $Prepare) {
									if (!in_array($Prepare,$Prepares)) {
										$Prepares[] = $Prepare;
									}
								}
							}
							
							$RealValue = $Value;
							
							foreach($Prepares as $Prepare) {
								$function = 'form_prepare_'.$Prepare;
								if (function_exists($function)) {
									$RealValue = $function($this->main,$Form,$Element,$RealValue);
								} elseif (function_exists($Prepare)) {
									$RealValue = $Prepare($RealValue);
								} else {
									throw new RDE('RD_Form: cant find prepare method '.$Prepare);
								}
							}
							
							$Form['elements'][$Key]['valuesformvalue'] = $RealValue;
						} else {
							$RealValue = $Element['valuesformvalue'];
						}
						
						if ($Element['type'] == self::$FormElementType) {
							RDD::Log('trigger subform '.$Key,TRACE,1201);
							$SubformName = $FormName.'_'.$Key;
							
							if (is_array($RealValue)) {
								$SubformValues = $RealValue;
							} else {
								$SubformValues = Array();
							}

							$this->ValuesForm($SubformName, $SubformValues, $Override);
							
						} elseif ($Element['type'] == self::$MultiFormElementType) {
                            
							RDD::Log('trigger multiform '.$Key,TRACE,1201);
							$Count = $Form['elements'][$Key]['count'];

							if (is_array($RealValue)) {
								$ValueCount = count($RealValue);
								if ($ValueCount > 0) {
									$MultiSubformNameBase = $FormName.'_'.$Key;
									for ($i = 0; $i < $ValueCount; $i++) {
										$MultiFormValues = Array();
										if (isset($RealValue[$i]) && is_array($RealValue[$i])) {
											$MultiFormValues = $RealValue[$i];
										}
										$FormArray = $this->prepareFormFieldLoadForm($Form['elements'][$Key]);			
										$NewFormName = $MultiSubformNameBase.'_'.$i;
										$Form['elements'][$Key]['multisubforms'][$i] = $NewFormName;
										$this->PrepareForm($NewFormName,$FormArray,false);
										$this->ValuesForm($MultiSubformNameBase.'_'.$i, $MultiFormValues, $Override);
									}
									if ($ValueCount > $Count) {
										$Form['elements'][$Key]['count'] = $ValueCount;
										$Form['elements'][$Key]['counter'] = range(0,$ValueCount-1);
									}
								}
							}

						} elseif (
									!$this->FormIsSubmitted($Form['id']) 
										&& 
										(
											(
												!$this->IssetPost($Form['elements'][$Key]['field'])
												&&
												(!isset($Form['elements'][$Key]['value']) || $Form['elements'][$Key]['value'] == "")
											)
											||
											$Override
										)
						) {
							RDD::Log('overriding '.$Key,TRACE,1201);
							$Form['elements'][$Key]['value'] = $RealValue;
						}

						if (!empty($this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'])) {
							if (strtolower($Element['type']) == strtolower(self::$FileuploadElementType)) {
					$this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'] = $this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'];
							} elseif (strtolower($Element['type']) == strtolower(self::$MultiFileuploadElementType)) {
					$this->main->SESSION['forms'][$FormName]['elements'][$Key]['filesbefore'] = $this->main->SESSION['forms'][$FormName]['elements'][$Key]['value'];
							}
						}

					}
				}
				return;
			} else {
				RDD::Log('form '.$FormName.' not loaded',TRACE,1201);
				return false;
			}
		} else {
			RDD::Log('no default values given for '.$FormName,TRACE,1201);
			return false;
		}
	}

	public function FormError($FormName,$ErrorMsg = NULL) {
		if (isset($this->main->SESSION['forms'][$FormName])) {
			if ($ErrorMsg === NULL) {
				if (isset($this->main->SESSION['forms'][$FormName]['defaulterror'])) {
					$this->main->SESSION['forms'][$FormName]['error'] =
						$this->main->SESSION['forms'][$FormName]['defaulterror'];
				} else {
					$this->main->SESSION['forms'][$FormName]['error'] = 'Formular ungültig';
				}
			} else {
				$this->main->SESSION['forms'][$FormName]['error'] = $ErrorMsg;
			}
		} else {
			RDD::Log('form '.$FormName.' not loaded',WARN);
			return false;
		}
	}

	public function ValidateForm($FormName) {
		if (isset($this->main->SESSION['forms'][$FormName])) {
			$Valid = true;
			$Form = &$this->main->SESSION['forms'][$FormName];
			$FormEmpty = true;
			$FormNull = true;

			foreach($Form as $Key => $Value) {
				$Function = 'form_param_'.$Key.'_validate';
				if (function_exists($Function)) {
					$Return = $Function($this->main,$FormName);
					if ($Return === false) {
						$Valid = false;
					}
				}
			}

			foreach($Form['elements'] as $Key => $Element) {

				if (isset($Form['elements'][$Key]['errormsg'])) {
					unset($Form['elements'][$Key]['errormsg']);
				}

				if ($Element['type'] == self::$FormElementType) {

					$SubformName = $FormName.'_'.$Form['elements'][$Key]['id'];

					$SubformResult = $this->ValidateForm($SubformName);
				
					if ($SubformResult === false) {
						$Errormsgs = Array();
						$Subform = &$this->main->SESSION['forms'][$SubformName];
						foreach($Subform['elements'] as $SubformKey => $SubformElement) {
							if (isset($SubformElement['errormsg'])) {
								foreach($SubformElement['errormsg'] as $Errormsg) {
									$Errormsgs[] = Array(
											'name' => $Subform['name'].' '.$SubformElement['errorname'],
											'errormsg' => $Errormsg
										);
								}
							}
						}
						$Form['elements'][$Key]['errormsg'] = $Errormsgs;
						$Valid = false;
					}

				} elseif ($Element['type'] == self::$MultiFormElementType) {

					RDD::Log('trigger multiform '.$Key,TRACE,1201);
					
					$Count = $Element['count'];

					$Errormsgs = Array();
					
					$MultiFormValid = true;
					$ValidCount = 0;
					$FalseCount = 0;

					if ($Count > 0) {

						$MultiSubformNameBase = $FormName.'_'.$Key;

						for ($i = 0; $i < $Count; $i++) {
							$MultiFormValues = Array();
							if (isset($Values[$Key]) && is_array($Values[$Key])) {
								if (isset($Values[$Key][$i]) && is_array($Values[$Key][$i])) {
									$MultiFormValues = $Values[$Key][$i];
								}
							}
							
							$MultiFormResult = $this->ValidateForm($MultiSubformNameBase.'_'.$i);
							$MultiSubform = &$this->main->SESSION['forms'][$MultiSubformNameBase.'_'.$i];
							
							if (isset($Element['ignoreempty'])) {								
								if ($MultiSubform['formempty']) {
									$MultiFormResult = 'empty';
								}
							} elseif (isset($Element['ignorenull'])) {								
								if ($MultiSubform['formnull']) {
									$MultiFormResult = 'null';
								}
							}

							if (isset($Element['minimum'])) {
								if ($MultiFormResult !== false && $MultiFormResult !== 'empty' && $MultiFormResult !== 'null') {
									$ValidCount++;
								}
								if ($MultiFormResult === false) {
									$FalseCount++;
								}
							}
							
							if ($MultiFormResult === false || $MultiFormResult === 'empty' || $MultiFormResult === 'null') {
								if (isset($Element['geterrors'])) {
									foreach($MultiSubform['elements'] as $SubformKey => $SubformElement) {
										if (isset($SubformElement['errormsg'])) {
											foreach($SubformElement['errormsg'] as $Errormsg) {
												$Number = $i+1;
												$Name = isset($Element['name']) ? $Element['name'] : $MultiSubform['name'];
												$Errormsgs[] = Array(
													'name' => $Number.'. '.$Name.' '.$SubformElement['errorname'],
													'errormsg' => $Errormsg
												);
											}
										}
									}
								}
								$MultiFormValid = false;
							}

						}
						
					}

					if (isset($Element['minimum']) && !$FalseCount) {
						if ($ValidCount >= $Element['minimum']) {
							$MultiFormValid = true;
							unset($Errormsgs);
						} else {
							$Errormsgs[] = T($Element['minimumerrormsg'],Array($Element['minimum']));
						}
					}

					if ($MultiFormValid === false) {
						$Valid = false;
						$Form['elements'][$Key]['errormsg'] = $Errormsgs;
					}

				} else {

					if (emptyarray($Element['value'])
					&& $Form['elements'][$Key]['value'] !== 0
					&& $Form['elements'][$Key]['value'] !== "0"
					&& $Form['elements'][$Key]['value'] !== NULL) {
						$Empty = true;
					} else {
						$Empty = false;
						$FormEmpty = false;
					}
					
					if (empty($Element['value'])) {
						$Null = true;
					} else {
						$Null = false;
						$FormNull = false;
					}

					$ElementValid = true;
						
					/**
					 * Using validators for checking form data
					 */
					if (isset($Element['validators'])) {

						/**
						 * Checking for notempty validator
						 */

						$defaulterrormsg = false;
						$form_defaulterrormsg = false;
						$element_defaulterrormsg = false;
						$element_onlyerrormsg = false;

						foreach($Element['validators'] as $ValidatorKey => $Validator) {
							
							if ($Validator['type'] == self::$NotEmptyType) {
								if ($Empty) {
								    $Return = empty($Validator['error']) ? self::$EmptyErrorMessage : $Validator['error'];   
								} else {
									$Return = true;
								}
							} elseif ($Validator['type'] == self::$NotNullType) {
								if ($Null) {
									$Return = self::$NullErrorMessage;
								} else {
									$Return = true;
								}								
							} elseif ($Empty) {
								$Return = true;
							} else {
								#
								#
								# Validator
								#
								############
								$Validator['form'] = $Form;
								$Validator['formname'] = $FormName;
								$Validator['element'] = $Key;
								$Return = $this->Validate($Validator['type'],$Form['elements'][$Key]['value'],$Validator);
							}
							
							if ($Return === false || is_string($Return) || is_a($Return,'RDT')) {
								$Valid = false;
								$ElementValid = false;
								if (!isset($Form['elements'][$Key]['errormsg']) || !is_array($Form['elements'][$Key]['errormsg'])) {
									$Form['elements'][$Key]['errormsg'] = Array();
								}
								if (isset($Validator['errormsg'])) {
								    $Form['elements'][$Key]['errormsg'][] = T($Validator['errormsg']);
								} elseif (isset($Form['elements'][$Key]['onlyerrormsg'])) {
									if (!$element_onlyerrormsg) {
										$Form['elements'][$Key]['errormsg'][] = T($Form['elements'][$Key]['onlyerrormsg']);
										$element_onlyerrormsg = true;
									}
								} elseif (is_string($Return)) {
									$Form['elements'][$Key]['errormsg'][] = T($Return);
								} elseif (is_a($Return,'RDT')) {
									$Form['elements'][$Key]['errormsg'][] = $Return;								
								} else {
									if (isset($Form['elements'][$Key]['defaulterrormsg'])) {
										if (!$element_defaulterrormsg) {
											$Form['elements'][$Key]['errormsg'][] = T($Form['elements'][$Key]['defaulterrormsg']);
											$element_defaulterrormsg = true;
										}
									} elseif (isset($Form['defaulterrormsg'])) {
										if (!$form_defaulterrormsg) {
											$Form['elements'][$Key]['errormsg'][] = T($Form['defaulterrormsg']);
											$form_defaulterrormsg = true;
										}
									} else {
										if (!$defaulterrormsg) {
											$Form['elements'][$Key]['errormsg'][] = self::$DefaultErrorMessage;
											$defaulterrormsg = true;
										}
									}
								}
							}
						}
					}

					$validate_function = 'form_type_'.$Element['type'].'_validate';

					if (function_exists($validate_function)) {
						$Form['elements'][$Key] = $validate_function($Form['elements'][$Key]);
						if (isset($Form['elements'][$Key]['valid']) && $Form['elements'][$Key]['valid'] === false) {
							$Valid = false;
							$ElementValid = false;
						}
					}

					$Form['elements'][$Key]['valid'] = $ElementValid;
					
				}
			}

			$this->main->SESSION['forms'][$FormName]['formempty'] = $FormEmpty;
			$this->main->SESSION['forms'][$FormName]['formnull'] = $FormNull;

			if ($Valid) {
				$Form['valid'] = true;
				foreach($Form['elements'] as $Key => $Element) {
					if ($Element['type'] == self::$FormElementType) {
						$SubformName = $FormName.'_'.$Key;
						$FinalValue = $this->FinalValuesForm($SubformName);
					} elseif ($Element['type'] == self::$MultiFormElementType) {
						$Count = $Element['count'];
						if ($Count > 0) {
							$MultiSubformNameBase = $FormName.'_'.$Key;
							$MultiformResult = Array();
							for ($i = 0; $i < $Count; $i++) {
								$MultiFormResult = $this->FinalValuesForm($MultiSubformNameBase.'_'.$i);
								if (!empty($MultiFormResult)) {
									$MultiformResult[$i] = $MultiFormResult;
								}
							}
						}
						$FinalValue = $MultiformResult;
					} else {
						$FinalValue = $Form['elements'][$Key]['value'];
					}
					
					#
					# Transform
					#
					###############

					$Transforms = Array();

					if (isset($Element['transforms'])) {
						foreach($Element['transforms'] as $Transform) {
							$Transforms[] = $Transform['type'];
						}
					}

					if (isset($this->Config['DefaultTransforms'][$Element['type']])) {
						foreach($this->Config['DefaultTransforms'][$Element['type']] as $Transform) {
							if (!in_array($Transform,$Transforms)) {
								$Transforms[] = $Transform;
							}
						}
					}

					foreach($Transforms as $Transform) {
						$function = 'form_transform_'.$Transform;
						if (function_exists($function)) {
							$FinalValue = $function($this->main,$Form,$Element,$FinalValue);
						} elseif (function_exists($Transform)) {
							$FinalValue = $Transform($FinalValue);
						} else {
							throw new RDE('RD_Form: cant find transform method '.$Transform);
						}
					}

					$Form['elements'][$Key]['finalvalue'] = $FinalValue;
				}
				if (isset($this->main->SESSION['forms'][$FormName]['error'])) {
					unset($this->main->SESSION['forms'][$FormName]['error']);
				}
			} else {
				$Form['valid'] = false;
			}
			foreach($Form['elements'] as $Key => $Element) {
				$post_validate_function = 'form_type_'.$Element['type'].'_post_validate';		
				if (function_exists($post_validate_function)) {
					$Form['elements'][$Key] = $post_validate_function($Form['elements'][$Key],$Valid);
				}
			}
			return $Valid;
		} else {
			RDD::Log('form '.$FormName.' not loaded',WARN);
			return false;
		}
	}

	public function FinalValuesForm($FormName) {
		if (isset($this->main->SESSION['forms'][$FormName])) {
			$Form = &$this->main->SESSION['forms'][$FormName];
			$Result = Array();
			foreach($Form['elements'] as $Key => $Element) {
				if (array_key_exists('finalvalue',$Form['elements'][$Key])) {
					$Result[$Key] = $Form['elements'][$Key]['finalvalue'];
				} else {
					RDD::Log('form '.$FormName.' not validated',WARN);
					break;
				}
			}
			return $Result;
		} else {
			RDD::Log('form '.$FormName.' not loaded',WARN);
			return false;
		}
	}

	/**
	 * Loading the from config directory and giving back the array
	 *
	 * @param String $FormName
	 */
	public function LoadForm($FormName) {
		RDD::Log('Loading Form '.$FormName,TRACE,1201);
		$filebase = 'forms'.DIR_SEP.$FormName.'.';
		foreach(self::$Extensions as $Extension) {
			if ($FileName = $this->File($filebase.$Extension)) {
				RDD::Log('Found Form '.$FormName.' as '.$Extension,TRACE,1202);
				$function = 'LoadForm'.strtoupper($Extension);
				if (method_exists($this,$function)) {
					return $this->$function($FileName);
				}
			}
		}
		throw new RDE('form module cant find the form '.$FormName);
	}
	
	/**
	 * Creates an array from a given xml file
	 *
	 * @param String $FileName
	 * @return Array
	 */
	public function LoadFormXML($FileName) {
		$Form = Array();
		$xml = new XMLReader();
		$xml->open($FileName);
		while($xml->read()) {
			if ($xml->name == 'form' && $xml->nodeType == XMLReader::ELEMENT) {
				RDD::Log('Found Formtag',TRACE,1203);
				// This will create the $Form Array
				while($xml->moveToNextAttribute()) {
					if ($xml->name == 'name' || $xml->name == 'submitvalue') {
						$Form[$xml->name] = T($xml->value);
					} else {
						$Form[$xml->name] = $xml->value;
					}
				}
				$Form['elements'] = Array();
				while($xml->read()) {
					// The $EmptyElement and $EmtyData stuff is for the 2 different cases with <element></element> and <element />
					$EmptyElement = false;
					if ($xml->name == 'element' && $xml->nodeType == XMLReader::ELEMENT) {
						if ($xml->isEmptyElement) {
							$EmptyElement = true;
						}
						$NewElement = Array();
						$Validators = Array();
						$Transforms = Array();
						$Prepares = Array();
						$Datas = Array();
						$Values = Array();
						$EmptyValue;
						$DataCounter = 0;
						$ValueCounter = 0;
						$dataName = $DataCounter;
						$valuename = $DataCounter;
						while($xml->moveToNextAttribute()) {
							if ($xml->name == 'name' || substr($xml->name,0,4) == 'text') {
								$NewElement[$xml->name] = T($xml->value);
							} else {
								$NewElement[$xml->name] = $xml->value;
							}
						}
						$Form['elements'][$NewElement['id']] = $NewElement;
						if (!$EmptyElement) {
							while($xml->read()) {
								// This part is for the parsing of the data elements within an "element" element
								$EmptyData = false;
								if ($xml->name == 'data' && $xml->nodeType == XMLReader::ELEMENT) {
									if($xml->isEmptyElement) {
										$EmptyData = true;
									}

									$Joins = Array();
									$NewData = Array();

									while($xml->moveToNextAttribute()) {
										$NewData[$xml->name] = $xml->value;
									}
									if(isset($NewData['id'])) {
										$dataName = $NewData['id'];
									}
									else {
										$dataName = $DataCounter;
									}
									$Form['elements'][$NewElement['id']]['datas'][$dataName]=$NewData;
									if(!$EmptyData) {
										// This will create the join array
										while($xml->read()) {
											if($xml->name == 'join' && $xml->nodeType == XMLReader::ELEMENT) {
												$NewJoin = Array();
												while($xml->moveToNextAttribute()) {
													if ($xml->name == 'addempty') {
														$NewJoin[$xml->name] = T($xml->value);
													} else {
														$NewJoin[$xml->name] = $xml->value;
													}
												}
												$Joins[] = $NewJoin;
											}
											elseif( $xml->name == 'data' && $xml->nodeType == XMLReader::END_ELEMENT ) {
												break;
											}
										}
										// This will add the join array (if it exists) to the $form array
										if(!empty($Joins)) {
											$Form['elements'][$NewElement['id']]['datas'][$dataName]['joins'] = $Joins;
										}
									}
									// This is for the counter of data element, it only counts if no id is given
									if(!isset($NewData['id']))$DataCounter++;
								}
								if ($xml->name == 'value' && $xml->nodeType == XMLReader::ELEMENT) {
									$NewValue = Array();
									while($xml->moveToNextAttribute()) {
										$NewValue[$xml->name] = $xml->value;
									}
									if(isset($NewValue['id'])) {
										$ValueName = $NewValue['id'];
									} else {
										$ValueName = $ValueCounter;
									}
									$Form['elements'][$NewElement['id']]['values'][$ValueName]=$NewValue;
									// This is for the counter ofvalue element, it only counts if no id is given
									if(!isset($NewValue['id']))$ValueCounter++;
								}
								if ($xml->name == 'validator' && $xml->nodeType == XMLReader::ELEMENT) {
									$NewValidator = Array();
									while($xml->moveToNextAttribute()) {
										$NewValidator[$xml->name] = $xml->value;
									}
									$Validators[] = $NewValidator;
								} elseif ($xml->name == 'prepare' && $xml->nodeType == XMLReader::ELEMENT) {
									$NewPrepare = Array();
									while($xml->moveToNextAttribute()) {
										$NewPrepare[$xml->name] = $xml->value;
									}
									$Prepares[] = $NewPrepare;
								} elseif ($xml->name == 'transform' && $xml->nodeType == XMLReader::ELEMENT) {
									$NewTransform = Array();
									while($xml->moveToNextAttribute()) {
										$NewTransform[$xml->name] = $xml->value;
									}
									$Transforms[] = $NewTransform;
								} elseif($xml->name == 'element' && $xml->nodeType == XMLReader::END_ELEMENT) {
									break;
								}
							}
							if (!empty($Validators)) {
								$Form['elements'][$NewElement['id']]['validators'] = $Validators;
							}
							if (!empty($Transforms)) {
								$Form['elements'][$NewElement['id']]['transforms'] = $Transforms;
							}
							if (!empty($Prepares)) {
								$Form['elements'][$NewElement['id']]['prepares'] = $Prepares;
							}
							if (!empty($Datas)) {
								$Form['elements'][$NewElement['id']]['datas'] = $Datas;
							}
						}
					}
				}
				return $Form;
			}
		}
	}
}
