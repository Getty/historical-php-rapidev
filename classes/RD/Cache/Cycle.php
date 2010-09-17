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

class RD_Cache_Cycle extends RDM {

	public $DefaultFiledir;
	public $DefaultCycle = 3600;
	public $DefaultDiff = 0;
	public $KeyPrefix = 'CacheCycle/';
	
	public function Setup() {
		return;
	}

	public function Save($Key,$Data,$Cycle = NULL,$Diff = NULL) {
		if ($Data !== NULL) {
			$DataArray = Array();
			$DataArray['Data'] = $Data;
			if ($Cycle === NULL) {
				$DataArray['Cycle'] = $this->DefaultCycle;
			} else {
				$DataArray['Cycle'] = $Cycle;
			}
			if ($Diff === NULL) {
				$DataArray['Diff'] = $this->DefaultDiff;
			} else {
				$DataArray['Diff'] = $Diff;
			}
			$Now = time();
			$DataArray['Created'] = $Now;
			$DiffNow = $Now - $DataArray['Diff'];
			$DataArray['CycleNo'] = floor($DiffNow / $DataArray['Cycle']);
		} else {
			$DataArray = $Data;
		}
		return $this->Store($this->KeyPrefix.$Key,$DataArray);
	}
	
	public function Load($Key) {
		$DataArray = $this->Store($this->KeyPrefix.$Key);
		if (is_array($DataArray) && !empty($DataArray)) {
			$Data = $DataArray['Data'];
			$DiffNow = time() - $DataArray['Diff'];
			$CycleNo = floor($DiffNow / $DataArray['Cycle']);
			if ($DataArray['CycleNo'] < $CycleNo) {
				$this->Store($this->KeyPrefix.$Key,NULL);
				return NULL;
			}
			return $Data;
		}
		return NULL;
	}

}
