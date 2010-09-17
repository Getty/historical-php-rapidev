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
 * @author     Harald Doderer <harrykan@gmx.de>
 * @copyright  2007 Harald Doderer
 * @license    GPL-2 
 * 
 */

class RD_RapiDev_Module_Validate extends RDM {

	public static $RD_Functions = Array(
									'Validate',
								  );
	public static $RD_Depencies = Array();
	public static $ValidatorFunctionPrefix = 'validator_';
	public static $Self;

	public $allowedSigns = '';
	
	public function __construct($main) {
		self::$Self = $this;
		parent::__construct($main);
	}

	public function Setup() {
		return;
	}
	

	/**
	 * Validate checks first the existence of a defined Validator Type $type in
	 * directory validators. eg. validators/alphanumeric.php
	 * If it is set, then it will try to execute the method from the validator..
	 * otherwise it will try to find a method from the module
	 * 
	 * @param String $type
	 * @param String $input
	 * @param String $options
	 * @return -
	 */
	
	public function Validate($type,$input,$options = array()) {
		
		$options['main'] = &$this->main;
		
		// if Validator exists in validator directory then we prefer this one
		// else we check if the method exists in this module as fallback
		
		if ($File = $this->File('validators'.DIR_SEP.strtolower($type).'.php')) {
			require_once($File);
			
			$class_name='RDM_Validate_'.ucfirst($type);
				
			if (isset($return)) {
				if (!isset($return['value'])) {
					throw new RDE('RDM_Validate: mapping array needs a value targetparameter integer');
				}
				$value_param = $return['value']+0;
				// mapping array
			} elseif (function_exists(self::$ValidatorFunctionPrefix.$type)) {	
				return self::$ValidatorFunctionPrefix.$type($input,$options);						
			} elseif (class_exists($class_name)) {
				$Validator = new $class_name($this->main);
				if (method_exists($Validator,'Start')) {
					return $Validator->Start($input,$options);
				}
			}

		} elseif (method_exists($this,$type)) {
			return $this->$type($input,$options);
		} elseif (function_exists($type)) {
			return $type($input);
		} else {
			throw new RDE('RDM_Validate: cant find anything todo with type '.$type);
		}
				
		return;
	}

	public function Validate_Email($input,$options) {
	
		return ; 
	}
	
	public function Validate_Password($input,$options) {
		$default_options = Array(	'minLength' =>1,
									'maxLength' =>1,
									'alphanum' =>'AN',
									'allowedSigns'=> Array('$','§'),
									'blockedSigns'=> Array('$','§'),
								);	
		if(!$this->Validate_Range($input,$options)) {
			return false;
		}
		if(!$this->Validate_Signs($input,$options)) {
			return false;
		}
		
		return true;
	}
	
	public function Validate_Signs($input,$options) {
		$default_options = Array('blockedSigns'=> Array('$','§'));
		
		// first we check the blocked Signs
		
		if(isset($options['blockedSigns']) && $options['$blockedSigns']!='') {
			if(is_array($options['blockedSigns'])) {
				foreach($options['blockedSigns'] as $key => $value) {
					if(strpos($input,$value)) {
						return false;
						break;
					}
				}
				return true;
			} else {
				if(strpos($input,$value)) {
						return false;
						break;
				} else {
					return true;
				}
			}
		}
		
		// now we prepare the allowed signs
		
		$this->Prepare_AllowedSigns($options);
		
		// then we check against the AN (Alpha Numeric) Set
		
		if(!$this->Validate_Alphanumeric($input,$options)) {
			return false;
		}
		
		
		return true;	
	}

	
	public function Validate_Alphanumeric($input,$options) {
		
		if(isset($options['alphanum']) && $options['alphanum']!='') {
			switch(strtolower($options['alphanum'])) {
				default:
					
				// a = alphabet only	
				
				case 'a': 	if(!eregi('a-z'.$this->allowedSigns,$input)) {
								return false;			
							} 
							break;
							
				// an = alphanumeric (alphabet and numbers) only
				
				case 'an': 	if(!eregi('a-z0-9'.$this->allowedSigns,$input)) {
								return false;			
							} 
							break;
				
				// n = numbers only	
				
				case 'n': 	if(!eregi('0-9'.$this->allowedSigns,$input)) {
								return false;			
							} 
							break;
			}
		}
		return true;
	}

	
	private function Prepare_AllowedSigns($options) {
		
		if(isset($options['allowedSigns'])) {
			if(is_array($options['allowedSigns'])) {
				$this->allowedSigns='\\'.implode('',$options['allowedSigns']);
			} else {
				if(!empty($options['allowedSigns'])) {
				$this->allowedSigns ='\\'.$options['allowedSigns'];
				}
			}
		}
		return;
	}
	
}
