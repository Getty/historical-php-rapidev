<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Exception Class
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

 if (!class_exists('RD_Exception')) {

	class RD_Exception extends Exception {
		
		// TODO
	
	}

}

if (!class_exists('RDE') && !defined('DONT_LOAD_RDE')) {

	class RDE extends RD_Exception {}

}

if (!function_exists('E') && !defined('DONT_LOAD_E')) {

	function E($Text) {
		throw new RDE($Text);
	}
	
}