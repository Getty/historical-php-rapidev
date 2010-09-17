<?
/**
 * 
 */
class RD_RapiDev_Module_Area extends RDM {
    
    public static $RD_Functions = Array(
    									'Area',
    									'AreaExist',
    									'AreaPlugin',
    								);
    								
    protected $AreaCache = Array();
    protected $AreaViews = Array();
    protected $AreaSubViews = Array();
    protected $RunningArea;
    protected $RunningAreaView;

   	public function HookModuleArea() {
		if ($AreaName = $this->GetGet('area')) {
			$Config = $this->GetGet();
			unset($Config['area']);
			unset($Config['module']);
			$this->Area($AreaName,$Config);
			echo $this->AreaPlugin('area', array('name' => $AreaName));
		}
	}
    
	public function AreaExist($AreaName) {
		return $this->File('areas'.DIR_SEP.$AreaName.'.php',false);
	}

	public function Area($AreaName,$Config = Array()) {
		if ($File = $this->AreaExist($AreaName)) {

			$this->AreaSubViews[$AreaName] = Array();
			
			$this->View('Area_'.$AreaName);
			
			$Area_View = $this->GetCurrentView();
			
			$AreaViewName = $Area_View->GetName();
			$this->RunningArea = $AreaName;
			$this->RunningAreaView = $AreaViewName;
			
			require_once($File);
			$ClassName = 'RD_Area_'.ucfirst(str_replace(DIR_SEP,'_',$AreaName));
			if (!class_exists($ClassName)) {
				$ClassName = 'RDM_Area_'.ucfirst(str_replace(DIR_SEP,'_',$AreaName));
			}
			$Area = new $ClassName($this->main);
			
			$this->Hook('PreAreaSetup',$Area);
			if ($Area->MethodExists('Setup')) {
				$Area->Setup($Config);
			}
			$this->Hook('PostAreaSetup',$Area);
			
			$this->Hook('PreAreaStart',$Area);
			$Area->Start();
			$this->Hook('PostAreaStart',$Area);

			unset($Area);
			
			$this->AreaViews[$AreaName] = $Area_View;
					
			$this->ViewFinish();
			
			unset($this->RunningArea);
			unset($this->RunningAreaView);
		}
	}

	public function AreaPlugin($Function,$Params) {
		if (!isset($Params['name'])) {
			throw new RDE('area needs a name');
		}
		$AreaName = $Params['name'];
		$Return = "";
		if (isset($this->AreaSubViews[$AreaName]) &&
			!empty($this->AreaSubViews[$AreaName])) {
			foreach($this->AreaSubViews[$AreaName] as $SubView) {
				$Return .= $this->ViewExecute($SubView,true);
			}
		}
		return $Return;
	}

	public function HookViewStart($RD_View) {
		if (isset($this->RunningArea)) {
			if ($RD_View->GetNameParent() == $this->RunningAreaView) {
				if (!isset($this->AreaSubViews[$this->RunningArea])) {
					$this->AreaSubViews[$this->RunningArea] = Array();
				}
				$this->AreaSubViews[$this->RunningArea][] = $RD_View;				
			}
		}
	}

}
