<?php

/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Empty or Numeric Check. 
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


class RDM_Validate_Emptynumeric extends RDM {

	/**
	 * Validates Input against an emptystring("") or numeric value or * for search
	 * !!! Always trim !!!
	 * @param String $input
	 * @param String $options
	 * @return - true or Errorstring
	 */
	
	public function Start($input, $options) {
		
		if ("" === trim($input)) {
			return true;
		}
		if ( 0 >= trim($input)){
			return "Die Zahl muss größer als 0 sein!";
		}
		$valide = eregi('^[0-9]{0,10}$', trim($input) );
		if($valide === false ){
			if ( strpos( $input, '%' ) !== false || strpos( $input, '*' ) !== false ){
				return "Sie können Wildcards (% oder *) nur im Feld Benutzername benutzen!";
			}
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