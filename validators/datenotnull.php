<?php

/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Compression Class (the classic Router)
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
 * @copyright  2007 Sven Strittmatter
 * @license    GPL-2 
 * 
 */


class RDM_Validate_Datenotnull extends RDM {

	/**
	 * Validates Input against a minimum length and maximum length
	 *
	 * @param String $input
	 * @param String $options
	 * @return -
	 */
	
	public function Start($input, $options) {
		
		$valide = 1;
		
		if ($input['day'] == 0){
			$valide = 0;
		}
		if ($input['month'] == 0){
			$valide = 0;
		}
		if ($input['year'] == 0){
			$valide = 0;
		}
		
		if($valide === 0){
			if(isset($options['error'])){
				return $options['error'];
			}else{
				return "Bitte f&uuml;llen Sie Ihr Geburtsdatum richtig aus.";
			}
		}else{
			return true;
		}
	}
}