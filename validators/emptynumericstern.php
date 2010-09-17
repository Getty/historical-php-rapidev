<?php

/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Empty or Numeric or Star or % Check. 
 * !!! Always trim !!!
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
 * @author     Feliks Polyak <polyak@webix.de>
 * @copyright  2008 Feliks Polyak
 * @license    GPL-2 
 * 
 */


class RDM_Validate_Emptynumericstern extends RDM {

	/**
	 * Validates Input against an emptystring("") or numeric value or * or % for search
	 * !!! Always trim !!!
	 * @param String $input
	 * @param String $options
	 * @return - true or Errorstring
	 */
	
	public function Start($input, $options) {
		
		if ("" === trim($input)) {
			return true;
		}

		$valide = eregi('^[0-9\*\%]{0,10}$', trim($input) );
		if($valide === false ){
			if(isset($options['error'])){
				return $options['error'];
			}else{
				return "Dies ist keine gültige Zahl!";
			}
		}else{
			return true;
		}
	}
}