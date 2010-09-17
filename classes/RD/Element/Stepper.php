<?

define('STEP_FINISH',-1);

class RD_Element_Stepper extends RDM {

	protected $Step = false;
	protected $Reset = false;
	protected $SessionStep;
	protected $GetStep;
	protected $PostStep;
	
	public function Start() {
		$this->StartStepper();
		return;
	}
	
	public function StartStepper() {
		$this->CheckGetReset();
		$this->GetStepEnv();
		$this->StepLoop();
		$this->StepFinal();
		return;
	}
	
	public function StepLoop() {
		
		$Return = $this->CallStep();
		
		if ($Return == STEP_FINISH) {
			$this->Finish();
		} elseif (is_int($Return)) {
			$NewStep = $Return;
		}
		
		if (isset($NewStep)) {
			if ($NewStep > $this->SessionStep) {
				$this->SetSessionStep($NewStep);
			}
			$this->Step = $NewStep;
			$this->StepLoop();
		}

		return;

	}
	
	protected function StepFinal() {
		$this->Assign($this->ElementName.'_step',$this->Step);
		return;
	}
	
	protected function SetSessionStep($Step) {
		if ($Step > $this->SessionStep) {
			$this->SetSession($this->ElementName.'_step',$Step);
		}
		return $Step;
	}

	protected function CheckGetReset() {
		if ($this->GetGet($this->ElementName.'_reset')) {
			$this->Reset();
		}
		return;
	}
	
	protected function Finish() {
		if (method_exists($this,'StepFinish')) {
			$this->StepFinish();
		}
		$this->Reset();
		$this->Assign($this->ElementName.'_step_finish',true);
		return;
	}
	
	public function Reset() {
		$this->FormClean();
		$this->UnsetSession($this->ElementName.'_step');
		return;
	}

	protected function GetStepEnv() {

		$this->SessionStep = $this->GetSession($this->ElementName.'_step',1);
		$this->GetStep = $this->GetGet($this->ElementName.'_step');
		$this->PostStep = $this->GetPost($this->ElementName.'_step');

		if (!$this->PostStep) {
			if ($this->GetStep && $this->GetStep <= $this->SessionStep) {
				$Step = $this->GetStep;
			}
		} else {
			if ($this->PostStep <= $this->SessionStep) {
				$Step = $this->PostStep;
			}
		}

		if (!isset($Step)) {
			$Step = $this->SessionStep;
		}

		if ($Step <= 0) {
			$Step = 1;
		}
		
		$this->Step = $Step;

		return;
	}
	
	protected function CallStep() {
		if (method_exists($this,'Step'.$this->Step)) {
			$StepMethod = 'Step'.$this->Step;
			return $this->$StepMethod();
		}
		return;
	}
	
}