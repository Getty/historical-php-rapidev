<?php

/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Alphanumeric Check
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
 * @author     Harald Doderer <harrykan@gmx.de>
 * @copyright  2007 Harald Doderer
 * @license    GPL-2 
 * 
 */


class RDM_Validate_Alphanumeric extends RDM {

	public $allowedSigns ='';

	/**
	 * Validates Input against Alphanumeric Values
	 * It is possible to set some additional allowed signs
	 *
	 * @param String $input
	 * @param Array $options
	 * @return Bool/String
	 */
	
	public function Start($input, $options) {
		$return = false;
		
		if(!isset($options['alphanum'])) {
			$options['alphanum'] = 'an';
		}
		
		switch(strtolower($options['alphanum'])) {
			// a = alphabet only	
			case 'a': 	
				if(eregi('[a-z]', $input)) {
					$return = true;			
				} 
				
				break;
						
			// an = alphanumeric (alphabet and numbers) only
			default:
			case 'an': 	
				if(eregi('[a-z0-9]', $input)) {
					$return = true;			
				} 
				
				break;
			
			// n = numbers only	
			case 'n': 	
				if(eregi('[0-9]', $input)) {
					$return = true;
				} 
				
				break;
		}
		
		return $return;
	}
}