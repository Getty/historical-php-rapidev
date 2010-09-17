<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * RapiDev Object Class
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

if (!class_exists('RD_Object')) {

	abstract class RD_Object {
	
		private $ObjectTags = Array();
		
		public function AddObjectTag($Tag) {
			if (strpos($Tag,' ') === false) {
				$this->ObjectTags[] = $Tag;
			}
			return $this;
		}
		
		public function RemoveObjectTag($Tag) {
			if ($Key = array_search($Tag,$this->Objects)) {
				unset($this->ObjectTags[$Key]);
			}
			return $this;
		}
	
		public function IssetObjectTag($Tag) {
			return in_array($Tag,$this->ObjectTags);
		}
	
		public function MethodExists($Method) {
			return method_exists($this,$Method);
		}

		public function SetFrom($Array) {
	        foreach ($Array as $Key => $Value) {
	            $Method = 'Set' . $Key;
	            if ($this->MethodExists($Method)) {
		            $this->$Method($Value);
	            } elseif ($this->MethodExists($Key)) {
		            $this->$Key($Value);
				}
	        }
	        
	        return $this;
	    }

	}
	
}

if (!class_exists('RDO') && !defined('DONT_LOAD_RDO')) {

	class RDO extends RD_Object {}

}