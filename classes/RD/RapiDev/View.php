<?

class RD_RapiDev_View extends Smarty {

	public $main;
	public $_file_perms = 0666;
	public $_dir_perms = 0777;

	public $force_compile = true;
	public $tplvars;
	protected $displayed = false;
	protected $finishvars = false;
	public $template;
	public $compile_dir_rights = 0777;
	public $display_templatename = false;
	public $debug_tpl = 'empty.tpl';

	public $DefaultLDelim = '<@';
	public $DefaultRDelim = '@>';
	
	protected $Name;
	protected $NameParent;

	public function __construct($Main) {
		$this->main = $Main;
		$this->Smarty();
		$this->Setup();		
	}
	
	public function SetNameParent($NameParent) {
		$this->NameParent = $NameParent;
		return $this;
	}
	
	public function SetName($Name) {
		$this->Name = $Name;
		return $this;
	}

	public function GetName() {
		return $this->Name;
	}

	public function GetNameParent() {
		return $this->NameParent;
	}

	public function Setup() {

		//
		
		$compile_dir = $this->getCacheDir().DIR_SEP.'smarty_compile';
		
		if (!is_dir($compile_dir)) {
			mkdir($compile_dir);
			chmod($compile_dir,$this->compile_dir_rights);
		} elseif ( !is_writable($compile_dir) ) {
			chmod($compile_dir,$this->compile_dir_rights);
		}

		if (!is_writeable($compile_dir)) {
			throw new RDE('smarty compile directory not writeable');
		}

		$this->compile_dir = $compile_dir;

		//
		
		$this->force_compile = true;
		
		// WORKAROUND TOOD
		if (defined('STAGE')) {
			if (STAGE != 'dev') {
				$this->force_compile = false;
			}
		}

		//

		$template_dirs = $this->Dirs('templates');
		$this->template_dir = $template_dirs;		

		//
		
		$plugins_dirs = $this->Dirs('plugins');
		$plugins_dirs[] = SMARTY_DIR.'plugins';
#		dump($plugins_dirs);
		$this->plugins_dir = $plugins_dirs;

		//
		
		$this->left_delimiter = $this->DefaultLDelim;
		$this->right_delimiter = $this->DefaultRDelim;

		//
		
		$this->VarsClean();
		
	}

	public function VarsClean() {
		$this->_tpl_vars = Array();
		$this->Assign('PHP_SELF',$this->main->PHP_SELF);
		$this->Assign('DOCUMENT_ROOT',$_SERVER['DOCUMENT_ROOT']);
		if (defined('STAGE')) {
			$this->Assign('STAGE',STAGE);
		}
		return;
	}
	
	public function Fetch($resource_name = NULL, $cache_id = NULL, $compile_id = NULL, $display = false) {
		if ($resource_name == NULL) {
			if (!empty($this->template)) {
				$resource_name = $this->template;
			} else {
				return "";
			}
		}
		return parent::fetch($resource_name,$cache_id,$compile_id,$display);
	}

	public function Display($resource_name = NULL, $cache_id = NULL, $compile_id = NULL) {
		return $this->Fetch($resource_name,$cache_id,$compile_id,true);
	}

	public function AssignIfUnset($Var,$Value = NULL) {
		if (is_array($Var)) {
			foreach($Var as $Key => $Value) {
				$this->AssignIfUnset($Key,$Value);
			}
		}
		if (!$this->IssetAssign($Var)) {		
			parent::assign($Var,$Value);
		}
		return $this;
	}

	public function Assign($Var,$Value = NULL) {
		parent::assign($Var,$Value);
		return $this;
	}

	public function Append($Var,$Value = NULL) {
		if (!$this->IssetAssign($Var)) {
			$this->Assign($Var,Array());
		}
		parent::append($Var,$Value);
		return $this;
	}

	public function IssetAssign($Var) {
		return isset($this->_tpl_vars[$Var]);
	}
	
	public function GetAssigns() {
		return $this->_tpl_vars;
	}
	
	public function SetAssigns($Array) {
		if (!is_array($Array)) {
			throw new RDE('RD_View: SetAssigns needs Array');
		}
		$this->_tpl_vars = $Array;
		return $this;
	}

	public function GetDebugging() {
		return $this->_smarty_debug_info;
	}

	public function StartDebugging() {
		$this->debugging = true;
		return $this;
	}

	public function StopDebugging() {
		$this->debugging = false;
		return $this;
	}

	public function ResetDebugging() {
		$this->_smarty_debug_info = Array();
		return $this;
	}
	
	public function SetTemplate($template) {
		$this->template = $template;
		return $this;
	}

	public function __call($name,$arguments) {
		if (isset($this->main)) {
			return RDC::call_object($this->main,$name,$arguments);
		}
		return;
	}

}
