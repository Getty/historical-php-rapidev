<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * User Class
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

class RD_RapiDev_Module_User extends RDM {

	public $SessionName;
	
	public $UserDatabase;
	public $UserTable = 'User';
	public $DataObject;
	public $UsernameCol = 'Username';
	public $PasswordCol = 'Password';
	public $UsernameField = 'login_Username';
	public $PasswordField = 'login_Password';
	public $ErrorTriggerForm = false;
	public $FailedLogin = false;
	public $UsePAM = false;
	
	public static $RD_Functions = Array(
											'GetUser',
											'SetUser',
											'Login',
											'Logout',
											'GetUserSessionName',
											'GetUserModuleConfig',
											'GetUserFailedLogin',
											'SetUserDataobject',
											'SetUserModuleConfig'
										);

	public static $RD_Depencies = Array();
	
	public static $Self;
	
	public function __construct($main) {
		self::$Self = $this;
		$this->SessionName = get_class($main).'User';
		parent::__construct($main);
	}

	public function GetUserSessionName() {
		return $this->SessionName;
	}

	public function SetUserDataobject($DataObject) {
		$this->DataObject = $DataObject;
		return $this;
	}
	
	public function GetUserFailedLogin() {
		return $this->FailedLogin;
	}
	
	public function SetUserModuleConfig($conf) {
		if (isset($conf['UserDatabase'])) {
			$this->UserDatabase = $conf['UserDatabase'];
		}
		
		if (isset($conf['UserTable'])) {
			$this->UserTable = $conf['UserTable'];
		}
		
		if (isset($conf['DataObject'])) {
			$this->DataObject = $conf['DataObject'];
		}
		
		if (isset($conf['UsernameCol'])) {
			$this->UsernameCol = $conf['UsernameCol'];
		}
		
		if (isset($conf['PasswordCol'])) {
			$this->PasswordCol = $conf['PasswordCol'];
		}
		
		if (isset($conf['UsernameField'])) {
			$this->UsernameField = $conf['UsernameField'];
		}
		
		if (isset($conf['PasswordField'])) {
			$this->PasswordField = $conf['PasswordField'];
		}
		
		if (isset($conf['ErrorTriggerForm'])) {
			$this->ErrorTriggerForm = $conf['ErrorTriggerForm'];
		}
		
		if (isset($conf['UsePAM'])) {
			$this->UsePAM = $conf['UsePAM'];
		}

	}
		
	public function GetUserModuleConfig() {
		if ((isset($this->UserDatabase) && isset($this->UserTable)) || isset($this->DataObject)) {
			$Result = Array();
			if ((isset($this->UserDatabase) && isset($this->UserTable))) {
				$Result['UserDatabase'] = $this->UserDatabase;
				$Result['UserTable'] = $this->UserTable;
			}
			if (isset($this->DataObject)) {
				$Result['DataObject'] = clone($this->DataObject);
			}
			$Result['UsernameCol'] = $this->UsernameCol;
			$Result['PasswordCol'] = $this->PasswordCol;
			$Result['UsernameField'] = $this->UsernameField;
			$Result['PasswordField'] = $this->PasswordField;
			$Result['ErrorTriggerForm'] = $this->ErrorTriggerForm;
			return $Result;
		} else {
			return false;
		}
	}
	
	// TODO Workaround, funktioniert aber erst wenn HookPostInit garantiert das DB geladen ist (Modulabhaengigkeiten)
	// public function HookPreStart() {
	public function HookPostPostInit() {
		if ((isset($this->UserDatabase) && isset($this->UserTable)) || isset($this->DataObject)) {
			RDD::Log('Starting Usercheck',TRACE,1410);
			$Page = $this->GetPage();
			if ($Page == 'logout' || isset($this->main->GET['logout'])) {
				RDD::Log('User logout found',TRACE,1410);
				$this->Logout();
			} elseif (!$this->GetUser()) {
				if (($Username = $this->Get($this->UsernameField)) && (($Password = $this->Get($this->PasswordField)))) {
					RDD::Log('Found login try',TRACE,1410);
					if ($this->Login($Username,$Password) === false) {
						RDD::Log('Unsuccessful login',TRACE,1410);
						$this->FailedLogin = true;
						$this->Hook('UserFailedLoggedIn');
					} else {
						RDD::Log('Successful login',TRACE,1410);
						$this->Hook('UserLoggedIn');
					}
				}
			}
			if ($this->GetUser()) {
				RDD::Log('Found User in session',TRACE,1410);
				$this->Hook('User');
			} else {
				RDD::Log('No user',TRACE,1410);
				$this->Hook('NoUser');
			}
		}
		return;
	}


	public function HookPreFinish() {
		if ($User = $this->GetUser()) {
			$this->Assign('User',$User);
		}
	}
	
	/**
	 * Loggt den User ein. Wenn das Passwort nicht gesetzt wird kann
	 * ein Login vom System forciert werden.
	 * 
	 * Die Methode wird vom Modul exportiert.
	 *
	 * Der Returnwert ist bei erfolgreichem Login ein Array mit den Userdaten. 
	 * Bei fehlgeschlagenem Login false.
	 * 
	 * @access public
	 * @param string $Username
	 * @param string $Password
	 * @return mixed
	 */
	public function Login($Username, $Password = NULL) {
		RDD::Log('Usermodule perform login', TRACE,1410);
		if (empty($this->DataObject)) {
			$DB = RDM_Dao::SDB($this->UserDatabase,$this->UserTable);
		} else {
			$DB = clone($this->DataObject);
		}
		
		// pam_auth($Username,$Password)
		
		$UsernameCol = $this->UsernameCol;
		$PasswordCol = $this->PasswordCol;
		$DB->$UsernameCol = $Username;
		
		if (NULL !== $Password) {
			$DB->$PasswordCol = md5($Password);
		} else {
			RDD::Log('Usermodule forces system login without password', TRACE,1410);
		}
		
		if ($DB->find() == 1) {
			RDD::Log('Usermodule found user', TRACE,1410);
			$DB->fetch();
			
			if (array_key_exists('Online', $DB->table())) {  // in some projects we dont have this column!
				$DB->Online = 1;
				$DB->update();
			}

		    if (array_key_exists('LastLogin', $DB->table())) {  // in some projects we dont have this column!
				$DB->LastLogin = time();
				$DB->update();
			}

			if (array_key_exists('Locked', $DB->table()) && $DB->Locked != 0) {  // in some projects we dont have this column!
                RDD::Log('Usermodule login failed', TRACE,1410);
                $this->SetSession('User_Locked', true);
                $this->Hook('UserLoginFailed');
                return false;
			}
			
			$Data = $DB->toArray();
            $this->SetSession($this->SessionName,$Data);
            $this->Hook('UserLogin');
            $this->Save();
            return $Data;
		}
		
		RDD::Log('Usermodule login failed', TRACE,1410);
		$this->Hook('UserLoginFailed');
		return false;
	}
	
	public function Logout() {
		if ($this->GetUser()) {
			if (!isset($this->DataObject)) {
				$DB = RDM_Dao::SDB($this->UserDatabase,$this->UserTable);
			} else {
				$DB = clone($this->DataObject);
			}

			if (array_key_exists('Online', $DB->table())) { // in some projects we dont have this column!
				$DB->ID = $this->GetUser('ID');
		
				if ($DB->find() == 1) {
					RDD::Log('Usermodule found user', TRACE,1410);
					$DB->fetch();
					$DB->Online = 0;
					$DB->update();
				}
			}
			
			$this->UnsetSession($this->SessionName);
			$this->UnsetSession('LastLoginSaved');
			$this->Hook('UserLogout');
		}
	}
	
	public static function SGetUser() {
		if (!isset(self::$Self)) {
			throw new RDE('RDM_User: cant use static SGetUser() before construction');
		}
		$inst = self::$Self;
		return RDC::call_object($inst,'GetUser');
	}
	
	public function GetUser($key = false) {
		if ($key) {
			$UserAssoc = $this->GetSession($this->SessionName);

			if (!is_array($UserAssoc)) {
				return false;
			}
			
			if (array_key_exists($key, $UserAssoc)) {
				return $UserAssoc[$key];
			} else {
				return false;
			}
		} else {
			return $this->GetSession($this->SessionName);
		}
	}

	public function SetUser($key = false,$value = NULL) {
		$UserSession = $this->GetUser();
		if (!$UserSession) {
			return false;
		}
		if (is_array($key)) {
			$UserSession = $key;
		} elseif ($value === NULL) {
			if (isset($UserSession[$key])) {
				unset($UserSession[$key]);
			}
		} else {
			$UserSession[$key] = $value;
		}
		$this->SetSession($this->SessionName,$UserSession);
		return $value;
	}
	
	public function HookPreCleanup() {
		if ($this->ConsoleDebug() && $this->GetUser()) {
			$this->AddConsoleDebugInformation('GetUser()',$this->GetUser());
		}
		return;
	}
	
}

if (!function_exists('user')) {
	function user() {
		$args = func_get_args();
		return call_user_func_array(Array('RDM_User','SGetUser'),$args);	
	}
}
