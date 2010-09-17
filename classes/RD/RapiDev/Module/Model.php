<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Model Class (the classic Model)
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

class RD_RapiDev_Module_Model extends RDM {

	public static $RD_Functions = Array(
									'Model',
									'SetModel',
									'GetModel',
									'UnsetModel',
									'IssetModel',
								  );

	public static $RD_Depencies = Array();

	protected $Model;

	public function Setup() {
		return;
	}

	public function Model($Model = NULL) {
		if ($Model === NULL) {
			return $this->GetModel();
		}
		return $this->SetModel($Model);
	}

	public function UnsetModel() {
		unset($this->Model);
		return $this;
	}

	public function IssetModel() {
		return isset($this->Model);
	}

	public function GetModel() {
		if (!isset($this->Model)) {
			throw new RDE('RDM_Model: No model set');
		}
		return $this->Model;
	}

	public function SetModel($Model) {
		$this->Model = $Model;
		return $this;
	}

}

if (!function_exists('model')) {
	function model() {
		$args = func_get_args();
		return RDC::CallObject(RD::$Self,'Model',$args);	
	}
}
