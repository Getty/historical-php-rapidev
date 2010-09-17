<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * View Module
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
 * @copyright  2008 Torsten Raudssus
 * @license    GPL-2 
 * 
 */

class RD_RapiDev_Module_View extends RDM {

	/* NEW */
	
	protected $ConsoleDebug = false;
	protected $ConsoleDebugData = Array();
	protected $ConsoleDebugInformation = Array();
	protected $DisplayTemplatename = false;

	public $ViewStack = Array();
	public $ExecuteStack = Array();
	public $ViewCache = Array();
	public $CurrentView;
	public $ExecuteView;


	public $ViewClass = 'RD_View';
	
	public static $RD_Functions = Array(
		'Fetch',
		'Display',
		'Assign',
		'GetAssign',
		'SetAssign',
		'Append',
		'View',
		'ViewInclude',
		'ViewEcho',
		'ViewStart',
		'ViewExecute',
		'ViewFinish',
		'ViewGenerate',
		'GetCurrentView',
		'GetExecuteView',
		'GetView',
		'ViewScope',
		'Displayed',
		'AssignNotice',
		'SetTemplate',
		'DisplayTemplatename',
		'TemplateExist',
		'AssignEnv',
		'FinishRun',
		'ConsoleDebug',
		'AddConsoleDebugInformation',
		'ViewDump',
		'LoadSmarty',
	);

	public static $RD_Depencies = Array();
	
	public function Setup() {		

		RDD::Log('Setup View Module',TRACE,1200);

		if (defined('RD_SMARTY_DEBUGCONSOLE')) {
			$this->ConsoleDebug = true;
		}

	}

	public function LoadSmarty() {

		RDD::Log('Loading Smarty',TRACE,1201);

		if (!defined('SMARTY_DIR')) {
			if ($SmartyDir = $this->Lib('smarty')) {
				define('SMARTY_DIR',$SmartyDir.DIR_SEP);
			} else {
				throw new RDE('I need Smarty!!! (says the '.__CLASS__.' module)');
			}
		}

		require_once(SMARTY_DIR.DIR_SEP.'Smarty.class.php');
		require_once(SMARTY_DIR.DIR_SEP.'plugins'.DIR_SEP.'modifier.escape.php');

	}
	
	public function ViewStart($RD_View = NULL, $Name = NULL) {
		RDD::Log('ViewStart ##############################',TRACE,1200);
		if ($RD_View === NULL || is_string($RD_View)) {
			if (is_string($RD_View)) {
				$Name = $RD_View;
			}
			$RD_View = $this->ViewGenerate();
			if (isset($this->CurrentView)) {
				$RD_View->SetAssigns(
										$this->GetCurrentView()->GetAssigns()
									);
			}
		}
		if ($Name === NULL) {
			$Name = count($this->ViewCache);
		}
		if (isset($this->CurrentView)) {
			$RD_View->SetNameParent($this->CurrentView->GetName());
			$Name = $this->GetCurrentView()->GetName().'.'.$Name;
		}
		if (isset($this->ViewCache[$Name])) {
			throw new RDE('View of that name already exist');
		}
		RDD::Log('ViewName: '.$Name);
		$RD_View->SetName($Name);
		$this->ViewCache[$Name] = $RD_View;
		if (isset($this->CurrentView)) {
			$this->ViewStack[] = $this->CurrentView;
		}
		$this->CurrentView = $RD_View;
		$this->Hook('ViewStart',$RD_View);
		return $RD_View;
	}
	
	public function ViewFinish($Return = false) {
		RDD::Log('ViewFinish ##############################',TRACE,1200);
		RDD::Log('ViewName: '.$this->GetCurrentView()->GetName());
		$this->AssignEnv();
		if (!empty($this->ViewStack)) {
			$this->CurrentView = array_pop($this->ViewStack);
		} else {
			$Final_View = $this->CurrentView;
			unset($this->CurrentView);

			$FetchedHtml = $this->ViewExecute($Final_View, $Return);
			
			if ($Return) {
				return $FetchedHtml;
			}
		}
		return $this;
	}

	public function ViewScope($NewScope) {
		if ($View = $this->GetCurrentView(false)) {
			return $View->GetName().'.'.$NewScope;
		} elseif ($View = $this->GetExecuteView(false)) {
			return $View->GetName().'.'.$NewScope;
		}
		return $NewScope;
	}


	public function ViewGenerate() {
		RDD::Log('Generate new View',TRACE,1200);
		$this->LoadSmarty();
		RD::RequireClass('RD_View');
		$RD_View = new RD_View($this->main);			
		return $RD_View;
	}

	public function TemplateExist($template) {
		return $this->File('templates'.DIR_SEP.$template);
	}
	
	public function AlienViewStack($State = NULL) {
		if ($State === NULL) {
			return $this->AlienViewStack;
		} else {
			$this->AlienViewStack = $State ? true : false;
			return $this;
		}
	}

	public function ConsoleDebug($State = NULL) {
		if ($State === NULL) {
			return $this->ConsoleDebug;
		} else {
			$this->ConsoleDebug = $State;
			return $this;
		}
	}

	public function AddConsoleDebugInformation($Name,$Data) {
		$this->ConsoleDebugInformation[$Name] = $Data;
		return $this;
	}
	
	public function DisplayTemplatename($Value = NULL) {
		if ($Value === NULL) {
			return $this->DisplayTemplatename;
		}
		$this->DisplayTemplatename = $Value;
		return $this;
	}
	
	public function View($RD_View = NULL) {
		return $this->ViewStart($RD_View);
	}
	
	public function ViewInclude($File) {
		if (!is_file($File) || !is_readable($File)) {
			return;
		}
		static $Count;
		$Count++;
		$this->View('ViewInclude'.$Count);
		$View = $this->GetCurrentView();
		ob_start();
		include($File);
		$Data = ob_get_contents();
		ob_end_clean();
		$View->Assign('Data',$Data);
		$this->SetTemplate('tpl:'.$View->DefaultLDelim.' $Data '.$View->DefaultRDelim);
		$this->ViewFinish();
	}

	public function ViewEcho($TemplateCode) {
		static $Count;
		$Count++;
		$this->View('ViewEcho'.$Count);
		$this->SetTemplate('tpl:'.$TemplateCode);
		$this->ViewFinish();
	}
	
	public function ViewDump($Var) {
		static $Count;
		$Count++;
		$this->View('ViewDump'.$Count);
		$View = $this->GetCurrentView();
		$View->Assign('Var', $Var);
		$this->SetTemplate('tpl:<@ $Var|@dump @>');
		$this->ViewFinish();
	}

	public function DebugViewStack($Array) {
		foreach($Array as $View) {
			RDD::Log($View->GetName(),WARN);
		}
	}
	
	public function ViewExecute($RD_View, $Fetch = false) {
		RDD::Log('Smarty Finish Run',TRACE,1200);
		RDD::Log('ViewName: '.$RD_View->GetName());
		
		$this->Hook('ViewExecute',$RD_View);

		$Key = array_search($RD_View,$this->ViewCache,true);
		unset($this->ViewCache[$Key]);
		
		if (isset($this->ExecuteView)) {
			$this->ExecuteStack[] = $this->ExecuteView;
			$ExecuteViewAssigns = $this->ExecuteView->GetAssigns();
			foreach($ExecuteViewAssigns as $Key => $Value) {
				$RD_View->AssignIfUnset($Key,$Value);
			}
		}
		
		$this->ExecuteView = $RD_View;

		RDD::Log('Smarty Execute Template Start',TRACE,1200);

		$RD_View->Assign('RandomValue',rand(1,999999999));
		
		$old_error_reporting = error_reporting();

		if ($this->ConsoleDebug) {
			$RD_View->ResetDebugging()->StartDebugging();
			RDD::Log('Preparing Smarty Execute Template ConsoleDebug',TRACE,1200);
			$ConsoleDebugCache = Array();
			$ConsoleDebugCache['Name'] = $RD_View->GetName();
			$ConsoleDebugCache['Class'] = get_class($RD_View);
			$ConsoleDebugCache['Vars'] = $RD_View->GetAssigns();
			$this->ConsoleDebugData[] =& $ConsoleDebugCache;
		}

		error_reporting(error_reporting() & ~E_NOTICE);
		
		if ($Fetch) {
			$Return = $RD_View->Fetch();
		} else {
			$Return = $RD_View->Display();
		}

		error_reporting($old_error_reporting);

		if ($this->ConsoleDebug) {
			$ConsoleDebugCache['Templates'] = $RD_View->GetDebugging();
		}

		$RD_View->StopDebugging();

		RDD::Log('Smarty Execute Template End',TRACE,1200);
		
		if (!empty($this->ExecuteStack)) {
			$this->ExecuteView = array_pop($this->ExecuteStack);
		} else {
			unset($this->ExecuteView);
		}

		return $Return;
	}
	
	public function SetAssign($array) {
		if (!is_array($array)) {
			throw new RDE('RDM_View: setassign need an array');
		}
		$this->tplvars = $array;
	}

	public function GetCurrentView($Default = NULL) {
		if (isset($this->CurrentView)) {
			return $this->CurrentView;
		}	
		if ($Default === NULL) {
			throw new RDE('RDM_View has no CurrentView');
		}
		return $Default;
	}

	public function GetExecuteView($Default = NULL) {
		if (isset($this->ExecuteView)) {
			return $this->ExecuteView;
		}	
		if ($Default === NULL) {
			throw new RDE('RDM_View has no ExecuteView');
		}
		return $Default;
	}

	public function GetView() {
		if (isset($this->CurrentView)) {
			return $this->CurrentView;
		}	
		return $this->ViewGenerate();
	}
	
	public function AssignEnv($RD_View = NULL) {
		
		if ($RD_View === NULL) { $RD_View = $this->GetCurrentView(); }
		
		$SESSION = $this->GetSession();
		$COOKIE = $this->GetCookie();
		$POST = $this->GetPost();
		$GET = $this->GetGet();

		foreach($POST as $Key => $Value) {
			if (!$RD_View->IssetAssign($Key)) {
				$RD_View->Assign($Key,$Value);
			}
		}

		foreach($GET as $Key => $Value) {
			if (!$RD_View->IssetAssign($Key)) {
				$RD_View->Assign($Key,$Value);
			}
		}

		foreach($SESSION as $Key => $Value) {
			if (!$RD_View->IssetAssign($Key)) {
				$RD_View->Assign($Key,$Value);
			}
		}
		
		foreach($COOKIE as $Key => $Value) {
			if (!$RD_View->IssetAssign($Key)) {
				$RD_View->Assign($Key,$Value);
			}
		}
		
		$RD_View->Assign('SESSION',$SESSION);
		$RD_View->Assign('COOKIE',$COOKIE);
		$RD_View->Assign('POST',$POST);
		$RD_View->Assign('GET',$GET);

		return $this;
	}

	public function HookCleanup() {
		if ($this->ConsoleDebug && !RD_Util::IsCLI()) {
			$RD_View = $this->ViewGenerate();
			$this->ConsoleDebug = false;
			$Vars = Array();
			$Vars['STAGE'] = STAGE;
			$Vars['Contexts'] = $this->ConsoleDebugData;
			$Vars['Log'] = RDD::GetLog();
			$Vars['CoreCache'] = $this->GetCoreCache();
			$Vars['Roots'] = $this->GetRoots();
			$Vars['LoadedModules'] = $this->GetLoadedModules();
			$Vars['_COOKIE'] = $_COOKIE;
			$Vars['_GET'] = $_GET;
			$Vars['_POST'] = $_POST;
			$Vars['_SESSION'] = $_SESSION;
			$Vars['_SERVER'] = $_SERVER;
			$Vars['_FILES'] = $_FILES;
			$QMPos = strpos($_SERVER['PHP_SELF'],'?');
			if ($QMPos === false) {
				$Filename = $_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF'];
			} else {
				$Filename = $_SERVER['DOCUMENT_ROOT'].substr($_SERVER['PHP_SELF'],0,$QMPos);
			}
			$Vars['Filename'] = $Filename;
			if (is_readable($Filename)) {
				$Vars['FileContent'] = file_get_contents($Filename);
				$Vars['FileAccess'] = fileatime($Filename);
				$Vars['FileModify'] = filemtime($Filename);
				$Vars['FileSize'] = filesize($Filename);
			}
			$Vars['LastLog'] = end($Vars['Log']);
			$Vars['MaxExecTime'] = ini_get('max_execution_time');
			$Vars['MemoryLimit'] = ini_get('memory_limit');
			$Vars['ExtraInformation'] = $this->ConsoleDebugInformation;
			$RD_View->Assign($Vars);
			$RD_View->Display('debug/console.tpl');
			$this->ConsoleDebug = true;
		}
	}

	///////////////////
	// DEPRECATED 
	///////////////////
	public function GetSM() {
		return $this->GetCurrentView();
	}
	
	public function Fetch($resource_name, $cache_id = null, $compile_id = null, $display = false) {
		return $this->GetCurrentView()->Fetch($resource_name, $cache_id, $compile_id, $display);
	}

	public function Display($resource_name, $cache_id = null, $compile_id = null) {
		return $this->GetCurrentView()->Display($resource_name, $cache_id, $compile_id);
	}

	public function Assign($Var, $Value = NULL) {
		return $this->GetCurrentView()->Assign($Var,$Value);
	}

	public function AssignNotice($notice) {
		return $this->Append('notices', $notice);
		return;
	}

	public function Append($tpl_var, $value=null, $merge=false) {
		return $this->GetCurrentView()->Append($tpl_var, $value, $merge);
	}
	
	public function SetTemplate($template) {
		$this->GetCurrentView()->template = $template;
		return;
	}
	
}
