<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Module Core Class
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

if (!class_exists('RD_Module')) {

	abstract class RD_Module extends RD_Object {
		
		protected $main;
	
		public function __construct(&$main) {
			$this->main = $main;
			return;
		}
	
		public function __call($name,$arguments) {
			return RDC::CallObject($this->main,$name,$arguments);
		}

		public function MethodExists($Method) {
			if (method_exists($this,$Method)) {
				return get_class($this);
			} else {
				return $this->main->MethodExists($Method);
			}
		}

	}

}

if (!class_exists('RDM') && !defined('DONT_LOAD_RDM')) {

	class RDM extends RD_Module {
		
		// TODO
	
	}

}