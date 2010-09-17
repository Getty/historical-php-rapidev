<?php

/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Username check. 
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


class RDM_Validate_Username extends RDM {

	/**
	 * Validates Input against username, 
	 * options[min_length, max_length] - min und max length of username,
	 * 3-80 default, first letter must to be always letter, not digit or ' ' or . or _- .
	 * öäßü are permit too!
	 * options[plus] - string of permit letters you can add (see http://de3.php.net/manual/en/book.pcre.php) 
	 * !!! Always trim BEFOR use after validation!. 
	 * ATTENTION you need not add slashes!!! mes fuuuiii!!!
	 * @param String $input
	 * @param String $options
	 * @return - true or Errorstring
	 */
	
	public function Start($input, $options) {
		$input = trim($input);
		if ("" === $input) {
			return 'Der Feld ist leer!';
		}

		$min_length = 3;
		$max_length = 80;
		if ( isset( $options['min_length'] ) ){
			$min_length = $options['min_length'];
		}
		if ( isset( $options['max_length'] ) ){
			$min_length = $options['max_length'];
		}
		if ( strlen($input) < $min_length ){
			return 'Die Länge muss mindestens '.$min_length.' Zeichen sein!';
		}
		if ( strlen($input) > $max_length ){
			return 'Die Länge muss maximal '.$max_length.' Zeichen sein!';
		}
		$AlleErlaubteZeichen = " A-Za-z0-9ÄÖÜäöüß_\-\.";
		$AlleErlaubteZeichenMenschlich = 'A-Za-zÄÖÜäöüß_-.0-9';
		$valide1 = eregi( '^[A-Za-zÄÖÜäöüß]{1}', $input );
		if ($valide1 === false){
			return 'Erste Zeichen muss immer eine Buchstabe sein!';
		}
		if (isset( $options['plus'] )){
			$AlleErlaubteZeichen .= $options['plus'];
		}
		$eregiStr= "/^[".$AlleErlaubteZeichen."]{1,}$/";

		
		$valide = preg_match( $eregiStr, $input);

		if($valide == 0 ){
			if(isset($options['error'])){
				return $options['error'];
			}else{
				if ( isset($options['plus']) ){
					$AlleErlaubteZeichenMenschlich .= $options['plus'];	
				} 
				return 'Dies ist keine gültige Wert! Erlaubt sind folgende Zeichen: '
					.$AlleErlaubteZeichenMenschlich.' und Leerzeichen.';
			}
		}else{
			return true;
		}
	}
}