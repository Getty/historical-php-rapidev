<?

class RD_Page_Login extends RD_Page {

	public function Page_start() {
		if ($this->GetUser()) {
			$this->SetGotoPage($this->GetPageDefault());
			return;
		} else {
			$this->EasyForm('login');
		}
	}

}
