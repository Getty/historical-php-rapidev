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
 * @license    GPL-2 
 * 
 */


class RDM_Validate_Range extends RDM {

	/**
	 * Validates Input against a minimum length and maximum length
	 *
	 * @param String $input
	 * @param String $options
	 * @return -
	 */
	
	public function Start($input,$options) {
		if (isset($options['min'])) {
			if ($input < $options['min']) {
				return false;
			}
		}
		if (isset($options['max'])) {
			if ($input > $options['max']) {
				return false;
			}
		}
		return true;
	}
}
