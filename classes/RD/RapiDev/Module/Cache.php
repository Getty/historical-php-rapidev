<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * File Caching Module
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

class RD_RapiDev_Module_Cache extends RD_Module {

	public static $RD_Functions = Array(
									'Cache*',
								);
	public static $RD_Depencies = Array();

	protected $DefaultCacheDriver = 'None';
	protected $CacheCache = Array();

	public static $Self;

	public function __construct($main) {
		self::$Self = $this;
		parent::__construct($main);
	}
	
	public function Setup() {
	}

	public function SetDefaultCacheDriver($DefaultCacheDriver) {
		$this->DefaultCacheDriver = $DefaultCacheDriver;
		return $this->main;
	}

	public function GetDefaultCacheDriver() {
		return $this->DefaultCacheDriver;
	}

	public function Cache() {

		$count = func_num_args();
		$args = func_get_args();

		if ($this->DefaultCacheDriver == 'None') {

			if ($count == 1) {
				return $this->Store($args[0]);
			} elseif ($count == 2) {
				return $this->Store($args[0],$args[1]);
			} else {
				throw new RDE('RDM_Cache: cant do anything');
			}

		} else {

			return $this->ExecuteDriver($this->DefaultCacheDriver,$args);

		}

	}

	protected function ExecuteDriver($Driver,$args) {
		$this->LoadDriver($Driver);
		$count = count($args);
		if ($count == 1) {
			return $this->CacheCache[$Driver]->Load($args[0]);
		} elseif ($count > 1) {
			return RDC::CallObject($this->CacheCache[$Driver],'Save',$args);
		} else {
			throw new RDE('RDM_Cache: cant do anything');
		}
	}

	protected function DriverExist($Driver) {
		return RD::ExistClass('RD_Cache_'.$Driver);
	}

	protected function LoadDriver($Driver) {
		if (!isset($this->CacheCache[$Driver])) {
			$ClassName = 'RD_Cache_'.$Driver;
			RD::RequireClass($ClassName);
			$this->CacheCache[$Driver] = RDC::CreateObject($ClassName,Array($this->main));
			$this->CacheCache[$Driver]->Setup();
		}
		return;
	}

	public function MethodExists($Method) {

		static $KnownMethods = Array();

		if (!isset($KnownMethods[$Method])) {
			$Return = parent::MethodExists($Method);
			if (!$Return) {
				$DriverName = substr($Method,5);
				if (isset($this->CacheCache[$DriverName])) {
					$Return = true;
				} else {
					$File = $this->DriverExist($DriverName);
					if ($File) {
						$Return = true;
					}
				}
			}
			$KnownMethods[$Method] = $Return;
		}
		return $KnownMethods[$Method];

	}

	public function __call($Method,$Args) {
		if (strpos($Method,'Cache') === 0) {
			$Driver = substr($Method,5);
			if ($this->DriverExist($Driver)) {
				return $this->ExecuteDriver($Driver,$Args);
			}
		}
		return parent::__call($Method,$Args);
	}

}

if (!function_exists('cache')) {
	function cache() {
		$args = func_get_args();
		return RDC::call_object(RDM_Cache::$Self,'Cache',$args);
	}
}

if (!function_exists('RD_CacheTTL')) {
	function RD_CacheTTL() {
		$args = func_get_args();
		return RDC::call_object(RDM_Cache::$Self,'CacheTTL',$args);
	}
}

if (!function_exists('RD_CacheCycle')) {
	function RD_CacheCycle() {
		$args = func_get_args();
		return RDC::call_object(RDM_Cache::$Self,'CacheCycle',$args);
	}
}
