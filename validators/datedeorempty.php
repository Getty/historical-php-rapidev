<?php

/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Datum (dd.mm.yy) check. 
 * 
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


class RDM_Validate_DatedeOrEmpty extends RDM {

	/**
	 * Validates Input against germany date dd.mm.yy, 
	 * @param String $input
	 * @param String $options
	 * @return - true or Errorstring
	 */
	
	public function Start($input, $options) {
		$input = trim($input);
		if ("" === $input) {
			return true;
		}

		$eregiStr = "/^[0-3]{1}[0-9]{1}\.[0-1]{1}[0-9]{1}\.[0-9]{2}$/" ;
		$valide = preg_match( $eregiStr, $input);

		if($valide == 0 ){
			if(isset($options['error'])){
				return $options['error'];
			}else{
				return 'Dies ist keine gültige Wert! Richtige Format: dd.mm.yy z.B.: 29.06.08, 03.12.07';
			}
		}else{
			$data=explode(".", $input );
			if ( !checkdate( $data[1], $data[0], '20'.$data[2] ) ){
				return "Datum leider ist nicht korrekt!";
			}
			return true;
		}
	}
}