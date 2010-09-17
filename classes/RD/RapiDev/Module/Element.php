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

class RD_RapiDev_Module_Element extends RDM {

	public static $RD_Functions = Array(
									'Element',
									'FetchElement',
									'ElementClean',
									'ElementExist',
									'ElementRequire',
									'ElementPlugin',
								  );

	public static $RD_Depencies = Array();
	
	public $ElementConfig = Array();
	public $ElementViews = Array();

	public function Setup() {
		RD::RequireClass('RD_Element');
		return;
	}
	
	public function ElementClean() {
		$this->ElementConfig = Array();
		return;
	}

	public function HookModuleElement() {
		if ($ElementName = $this->GetGet('element')) {
			$Config = $this->GetGet();
			unset($Config['element']);
			unset($Config['module']);
			$this->Element($ElementName,$Config);
		}
	}

	public function Element($ElementName,$Config = Array(), $Fetch = false) {
		return $this->ElementStart($ElementName, $Config, $Fetch);		
	}

	public function ElementExist($ElementName) {
		return $this->File('elements'.DIR_SEP.$ElementName.'.php',false);
	}

	public function ElementPlugin($Function,$Params) {
		if (isset($Params['id']) && !isset($Params['name'])) {
			$Params['name'] = $Params['id'];
		}
		if (!isset($Params['name'])) {
			throw new RDE('element needs a name');
		}
		$ElementName = $Params['name'];
		$ElementScope = $this->GetElementScope($ElementName);
		RDD::Log($ElementScope);
		if (!isset($this->ElementViews[$ElementScope]) ||
			empty($this->ElementViews[$ElementScope])) {
			throw new RDE('Element not prepared');
		}
		$RD_View = array_shift($this->ElementViews[$ElementScope]);
		return $this->ViewExecute($RD_View,true);
	}
	
	public function GetElementScope($ElementName) {
		if ($View = $this->GetCurrentView(false)) {
			$ElementScope = $View->GetName().'.'.$ElementName;				
		} elseif ($View = $this->GetExecuteView(false)) {
			$ElementScope = $View->GetName().'.'.$ElementName;
		} else {
			$ElementScope = $ElementName;
		}
		return $ElementScope;
	}
	
	public function ElementRequire($ElementName) {
		if ($File = $this->ElementExist($ElementName)) {
			require_once($File);
			return true;
		}
		return false;
	}
	
	public function ElementCreate($ElementName) {
		$Classname = 'RDM_Element_'.ucfirst(str_replace('/','_',$ElementName));
		$Element = new $Classname($this->main);
		return $Element;
	}
	
	public function ElementStart($ElementName,$Config = Array(), $Fetch = false) {
		if ($this->ElementRequire($ElementName)) {
			$Element = $this->ElementCreate($ElementName);
			$ElementScope = $this->GetElementScope($ElementName);

			if (!isset($this->ElementViews[$ElementScope])) {
				$this->ElementViews[$ElementScope] = Array();
			}

			$ElementNumber = count($this->ElementViews[$ElementScope]);
			$RD_Viewname = 'Element_'.$ElementName.'_'.$ElementNumber;
			
			if ($Element->MethodExists('HTMLCacheKey') && $Element->MethodExists('HTMLCacheTTL')) {
				$Key = $Element->HTMLCacheKey();
				$CacheTTL = $Element->HTMLCacheTTL();
				if ($Key && $CacheTTL) {
					$Data = $this->CacheTTL('Element/HTMLCache/'.$Key);
					if ($Data && isset($Data['HTML'])) {
						$HTML = $Data['HTML'];							
					}
				}
			}
			
			if (isset($HTML) && STAGE != 'dev') {
				$this->ViewEcho($HTML);
				if ($Fetch) {
					return $HTML;
				} else {
					return $Element;
				}
			}
			
			$this->View($RD_Viewname);
			
			$this->Assign('RD_Context',$RD_Viewname);
			$this->Assign('element',$ElementName);

			if (!isset($Config['element'])) {
				$Config['element'] = $ElementName;
			}

			$this->Hook('PreElementSetup',$Element);
			if (method_exists($Element,'Setup')) {
				$Return = $Element->Setup($Config);
			}
			$this->Hook('PostElementSetup',$Element);
			if (isset($Return) && $Return === false) {
				$Config['nodisplay'] = true;
			} else {
				$this->Hook('PreElementStart',$Element);
				$Return = '';
				if (method_exists($Element,'Start')) {
					$Return = $Element->Start();
				}
				$this->Hook('PostElementStart',$Element);
				if ($Return === false) {
					$Config['nodisplay'] = true;
				}
				
				$this->SetTemplate('empty.tpl');

				if (isset($Config['nodisplay']) || isset($Config['no_template'])) {
	
					$this->SetTemplate('empty.tpl');
					
				} elseif (isset($Config['template'])) {
	
					$this->SetTemplate($Config['template']);

				} elseif (method_exists($Element,'Template')) {

					$this->SetTemplate($Element->Template());

				} else {
	
					$TemplateNames = Array();
					
					$TemplateNames[] = 'element'.DIR_SEP.$ElementName.'.tpl';
	
					$Splitted = explode('_',$ElementName);
					$Last = array_pop($Splitted);
					$ReverseName = $Last.'_'.implode('_',$Splitted);
					$TemplateNames[] = 'element'.DIR_SEP.$ReverseName.'.tpl';
	
					$ReverseNameDir = $Last.DIR_SEP.implode('_',$Splitted);
					$TemplateNames[] = 'element'.DIR_SEP.$ReverseNameDir.'.tpl';
	
					# HA HA
					$ReverseNameDirDir = $Last.DIR_SEP.implode(DIR_SEP,$Splitted);
					$TemplateNames[] = 'element'.DIR_SEP.$ReverseNameDirDir.'.tpl';
					
					foreach($TemplateNames as $TemplateName) {
						if ($this->TemplateExist($TemplateName)) {
							$this->SetTemplate($TemplateName);
							break;
						}
					}

				}
				

			}
			if (isset($Element->no_template) && $Element->no_template) {
				$Config['no_template'] = true;
			}
			$Element_View = $this->GetView();

			$this->ElementViews[$ElementScope][] = $Element_View;			
			
			if (isset($CacheTTL) && $CacheTTL) {
				$Return = $this->ViewFinish(true);
				$Data = Array();
				$Data['HTML'] = $Return;
				$this->CacheTTL('Element/HTMLCache/'.$Key,$Data,$CacheTTL);
				if (true === $Fetch) {
					return $Return;
				}
				echo $Return;
				return $Element;
			}
			
			$Return = $this->ViewFinish($Fetch);

			if (true === $Fetch) {
				return $Return;
			}
			
			return $Element;
			
			RDD::Log('element prepare finished');
		} else {
			RDD::Log('dont have an executable for the element '.$ElementName,WARN);
		}
		return;
	}

}

if (!function_exists('el')) {
	function el() {
		$args = func_get_args();
		return RDC::CallObject(RD::$Self,'Element',$args);	
	}
}
