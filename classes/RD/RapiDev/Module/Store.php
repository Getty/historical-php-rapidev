<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Storage Management Module
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

class RD_RapiDev_Module_Store extends RDM {

	public static $RD_Functions = Array(
									'Store',
								);

	public static $RD_Depencies = Array();
	
	protected $DefaultStoreDriver = 'File';
	protected $StoreCache = Array();

	public static $Self;
	
	public function __construct($main) {
		self::$Self = $this;
		parent::__construct($main);
	}
	
	public function SetDefaultStoreDriver($DefaultStoreDriver) {
		$this->DefaultStoreDriver = $DefaultStoreDriver;
		return $this->main;
	}
	
	public function GetDefaultStoreDriver() {
		return $this->DefaultStoreDriver;
	}
	
	public function Store() {

		$count = func_num_args();
		$args = func_get_args();

		if (!isset($this->StoreCache[$this->DefaultStoreDriver])) {
			$ClassName = 'RD_Store_'.$this->DefaultStoreDriver;
			RD::RequireClass($ClassName);
			$this->StoreCache[$this->DefaultStoreDriver] = new $ClassName($this->main);
			$this->StoreCache[$this->DefaultStoreDriver]->Setup();
		}

		if ($count == 1) {
			return $this->StoreCache[$this->DefaultStoreDriver]->Load($args[0]);
		} elseif ($count == 2) {
			return $this->StoreCache[$this->DefaultStoreDriver]->Save($args[0],&$args[1]);
		} else {
			throw new RDE('RDM_Store: cant do anything');
		}

	}

}

class RD_Chassis {
	
}

if (!function_exists('store')) {
	function store() {
		$args = func_get_args();
		return RDC::call_object(RDM_Store::$Self,'Store',$args);
	}
}
