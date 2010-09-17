<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Page Class (the classic Router)
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

class RD_RapiDev_Module_Page extends RDM {

	public $Page;
	public $Pages;
	public $DefaultPage = 'default';
	public $DefaultTemplate = 'structure.tpl';
	public $GotoPage;

	public $PagesAllowed = true;
	
	public $PageCache = Array();
	
	public static $FunctionSeparator = '_';
	public static $FunctionPrefix = 'Page_';
	public static $Directory = 'pages';
	public static $ClassPrefix = 'RD_Page';

	public static $PageStart = 'start';
	public static $PageEnd = 'end';

	public static $RD_Functions = Array(
		'SetPageDefaultTemplate',
		'UpdatePages',
		'SetGotoPage',
		'SetPageDefault',
		'GetPageDefault',
		'SetPagesAllowed',
		'GetPagesAllowed',
		'GetPages',
		'PageExist',
		'GetPage'
	);

	public static $RD_Depencies = Array();

	public function Setup() {
		RD::RequireClass('RD_Page');		return;
	}

	public function HookPostInit() {
		if (!($this->Page = $this->Get('page'))) {
			$this->Page = $this->DefaultPage;
		}
		$this->UpdatePages();
		return;
	}

	public function SetPageDefault($Value) {
		$this->DefaultPage = $Value;
		return;
	}

	public function SetPageDefaultTemplate($Value) {
		$this->DefaultTemplate = $Value;
		return;
	}

	public function GetPageDefault() {
		return $this->DefaultPage;
	}
	
	public function SetPagesAllowed($Pages) {
		$this->PagesAllowed = $Pages;
		return;
	}

	public function GetPagesAllowed() {
		return $this->PagesAllowed;
	}
	
	public function GetPages($key = null) {
	    if($key !== null) {
	        if(!array_key_exists($key, $this->Pages) or !is_array($this->Pages)) {
	            return false;
	        }
	        return $this->Pages[$key];
	    }
		return $this->Pages;
	}

	public function GetPage() {
		return $this->Page;
	}

	public function SetGotoPage($Page) {
		$this->GotoPage = $Page;
		return;
	}

	public function UpdatePages($Page = NULL) {

		$this->Hook('PrePageUpdate');
		if ($Page !== NULL && is_string($Page) && !empty($Page)) {
			$this->Page = $Page;
		} elseif (isset($this->main->POST['page'])) {
			$this->Page = $this->main->POST['page'];
		} elseif (isset($this->main->GET['page'])) {
			$this->Page = $this->main->GET['page'];
		} elseif (isset($this->main->SESSION['page'])) {
			$this->Page = $this->main->SESSION['page'];
		}
		RDD::Log('Resulting Page: '.$this->Page,TRACE,1211);
		$this->Pages = explode('_',$this->Page);
		RDD::Log('Resulting Pages:',TRACE,1212);
		RDD::Log($this->Pages,TRACE,1212);
		$this->Hook('PostPageUpdate');
		return;

	}
	
	protected function GoToCheck() {
		if(isset($this->GotoPage)) {
			RDD::Log('Page module found a goto to '.$this->GotoPage,TRACE,1210);
			$this->UpdatePages($this->GotoPage);
			unset($this->GotoPage);
			$this->CallPage();
			return false;
		} else {
			return true;
		}
	}
	
	public function PageExist($Pages) {
		if (!is_array($Pages)) {
			$Pages = explode('_'.$Pages);
		}
		foreach($Pages as $Key => $Page) {
			$Pages[$Key] = ucfirst($Page);
		}
	}

	/**
     * Creates needed object and loads all needed methods for the object
     **/

	public function CallPage() {

		if ($this->PagesAllowed === false) {
			return;
		}

		if(!$this->GoToCheck()) {
			return;
		}
		if ($this->PagesAllowed !== true && is_array($this->PagesAllowed)) {
			$Access = false;
			foreach($this->PagesAllowed as $PageAllowed) {
				$Length = strlen($PageAllowed);
				if (substr($this->Page,0,$Length) == $PageAllowed) {
					$Access = true;
					break;
				}
			}
			if (!$Access) {
				throw new Exception('RDM_Page: not allowed to access the Page: "'.$this->Page.'"');
			}
		}

		$this->Hook('PageStart');
		
		$RunCache = Array();
		
		foreach($this->Pages as $Key => $PagePart) {
			$FileName = self::$Directory;
			$ClassName = self::$ClassPrefix;
			for($i = 0 ; $i <= $Key ; $i++) {
				$FileName .= DIR_SEP.$this->Pages[$i];
				$ClassName .= '_'.ucfirst($this->Pages[$i]);
			}
			if (!isset($this->PageCache[$ClassName])) {
				try {
					RD::RequireClass($ClassName);
				} catch (Exception $e) {
					$FileName .= '.php';
					if ($File = $this->File($FileName)) {
						require_once($File);
					}
				}
				if (class_exists($ClassName)) {
					RDD::Log('Page class '.$ClassName.' loaded into RunCache',TRACE,1221);
					$this->PageCache[$ClassName] = new $ClassName($this->main);
				} else {
					RDD::Log('Page class '.$ClassName.' or file '.$FileName.' not exist',WARN);
					continue;
				}
			}
			$RunCache[$ClassName] = $this->PageCache[$ClassName];
		}
		
		if (!empty($RunCache)) {

			$MethodStack = Array(self::$PageStart);
			$PageDepth = count($this->Pages);
					
			$TempPageStack = Array();
			for($i = 0 ; $i < $PageDepth-1 ; $i++) {
				$TempPageStack[] = $this->Pages[$i];
				$MethodStack[] = implode(self::$FunctionSeparator,$TempPageStack).self::$FunctionSeparator.self::$PageStart;
			}

			$MethodStack[] = implode(self::$FunctionSeparator,$this->Pages);

			while (!empty($TempPageStack)) {
				$MethodStack[] = implode(self::$FunctionSeparator,$TempPageStack).self::$FunctionSeparator.self::$PageEnd;
				array_pop($TempPageStack);
			}

			$MethodStack[] = self::$PageEnd;

			foreach($MethodStack as $Method) {
				foreach($RunCache as $PageClass) {
					$PageMethod = self::$FunctionPrefix.$Method;
					RDD::Log('Trying PageMethod: "'.$PageMethod.'" on "'.get_class($PageClass).'"',TRACE,1211);
					if ($PageClass->MethodExists($PageMethod)) {
						$PageClass->$PageMethod();
						if(!$this->GoToCheck()) {
							return;
						}
					}
				}
			}

		}		
	}
	
	public function PageStart() {
		$this->View('RD_Page');
		$this->SetTemplate($this->DefaultTemplate);
		$this->CallPage();
		$this->Assign('page',$this->Page);
		$this->Assign('pages',$this->Pages);
	}
	
	public function PageFinish() {
		$this->ViewFinish();
	}

	public function HookFinish() {
		$this->PageFinish();
		return;
	}

	public function HookStart() {
		$this->PageStart();
	}

}

