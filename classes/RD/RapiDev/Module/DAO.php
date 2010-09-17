<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * DataAccessObject Module with Connection to DB_DataObject2
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

class RD_RapiDev_Module_DAO extends RD_Module {
	
	public $Databases = Array();
	public $Driver = 'mysql';
	public $Prefix = 'DB_';
	public $Extends = 'RD_DAO';

	public static $RD_Functions = Array('DB','AddDatabase','GetDatabase','SetDaoLocation','SetDaoExtends');
	public static $RD_Depencies = Array();
	
	public static $Self;
	
	public function __construct($main) {
		self::$Self = $this;
		parent::__construct($main);
	}

	public function LoadDataobject2() {

		RDD::Log('Loading Dataobject2',TRACE,1201);

		if (!defined('DATAOBJECT2_PATH')) {
			if ($Dataobject2Dir = $this->Lib('dataobject2')) {
				require_once($Dataobject2Dir.DIR_SEP.'DataObject2.php');
			} else {
				throw new RDE('I need Dataobject2!!! (says the '.__CLASS__.' module)');
			}
		}

		require_once(DATAOBJECT2_PATH.DIR_SEP.'Generator.php');
		require_once(DATAOBJECT2_PATH.DIR_SEP.'Cast.php');

	}
	
	public function DB() {
		$args = func_get_args();
		return call_user_func_array(Array($this->Extends,'factory'),$args);
	}
	
	public function HookInstall() {
		$generator = new DB_DataObject2_Generator;
		$generator->start();
		return;
	}
	
	public function SetDaoExtends($Extends) {
		$this->Extends = $Extends;
		return;
	}
	
	public function Setup() {

		$this->LoadDataobject2();

		$options = &PEAR::getStaticProperty('DB_DataObject2','options');
		if (!is_array($options)) {
			$options = Array();
		}
		
		$locations = Array();
		
		foreach($this->getRoots() as $ROOT) {
			if (is_dir($ROOT.DIR_SEP.'daos')) {
				$locations[] = $ROOT.DIR_SEP.'daos';
			}
		}
		
		if (empty($locations)) {
			RDD::Log('DAO needs a location to work');
			return;
		}

		$options['db_driver'] = $this->Driver;
		$options['schema_location'] = $locations;
		$options['class_location'] = $locations;
		// TODO
		// $options['require_prefix'] = 
		$options['class_prefix'] = $this->Prefix;
		$options['extends'] = $this->Extends;
		$options['extends_location'] = '';
		$options['debug'] = 0;
		$options['generator_class_rewrite'] = true;
		$options['quote_identifiers'] = 1;
		$options['proxy'] = false;
		
		RD::RequireClass($this->Extends);

		return;
	}
	
	public static function SDB() {
		if (!isset(self::$Self)) {
			throw new RDE('RDM_Dao: cant use static SDB() before construction');
		}
		$inst = self::$Self;
		$args = func_get_args();
		return call_user_func_array(Array($inst,'DB'),$args);
	}
	
	public function AddDatabase($config) {
		if (!isset($config['database'])) {
			throw new RDE(get_class($this).': database config doesnt has a database identifier');
		}
		$ConfigName = 'database_'.$config['database'];
		$this->SetOption($ConfigName,$config);
		if (!$this->GetOption('default_database')) {
			$this->SetOption('default_database',$config['database']);
		}
		return;
	}

	public function GetDatabase($database) {
		$ConfigName = 'database_'.$database;
		return $this->GetOption($ConfigName);
	}

	public function SetOption($name,$value) {
		$options = &PEAR::getStaticProperty('DB_DataObject2','options');
		if (!is_array($options)) {
			$options = Array();
		}
		$options[$name] = $value;
		return;
	}

	public function GetOption($name) {
		$options = &PEAR::getStaticProperty('DB_DataObject2','options');
		if (!is_array($options)) {
			$options = Array();
		}
		if (isset($options[$name])) {
			return $options[$name];
		}
		return false;
	}

	public function HookPreCleanup() {
		if ($this->ConsoleDebug()) {
			$Config = &PEAR::getStaticProperty('DB_DataObject2','options');
			if (empty($Config)) { $Config = 'DAO not loaded'; }
			$this->AddConsoleDebugInformation('DAO Config',$Config);
		}
		return;
	}

}

if (!function_exists('dao')) {
	function dao() {
		$args = func_get_args();
		return RDC::call_static('RDM_Dao','SDB',$args);	
	}
}

if (!function_exists('mes')) {
	function mes($string) {
		return mysql_escape_string($string);
	}
}
