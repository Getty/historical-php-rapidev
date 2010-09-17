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

class RD_Cache_TTL extends RDM {

	public $DefaultFiledir;
	public $DefaultTTL = 3600;
	public $KeyPrefix = 'CacheTTL/';
	
	public function Setup() {
		return;
	}

	public function Save($Key,$Data,$TTL = NULL) {
		if ($Data !== NULL) {
			$DataArray = Array();
			$DataArray['Data'] = $Data;
			if ($TTL === NULL) {
				$DataArray['TTL'] = $this->DefaultTTL;
			} else {
				$DataArray['TTL'] = $TTL;
			}
			$DataArray['Created'] = time();
		} else {
			$DataArray = $Data;
		}
		return $this->Store($this->KeyPrefix.$Key,$DataArray);
	}

	public function Load($Key) {
		$DataArray = $this->Store($this->KeyPrefix.$Key);
		if (is_array($DataArray) && !empty($DataArray)) {
			$Data = $DataArray['Data'];
			$Created = $DataArray['Created'];
			$TTL = $DataArray['TTL'];
			if (time() > $Created+$TTL) {
				$this->Store($this->KeyPrefix.$Key,NULL);
			}
			return $Data;
		}
		return NULL;
	}

}
