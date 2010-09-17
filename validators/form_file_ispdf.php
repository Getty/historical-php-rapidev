<?php

/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Check form file(s), if they are pictures.
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
 * @copyright  2009 Torsten Raudssus
 * @license    GPL-2 
 * 
 */


class RDM_Validate_Form_file_ispdf extends RDM {

	/**
	 * Validates Input against a minimum length and maximum length
	 *
	 * @param String $input
	 * @param String $options
	 * @return -
	 */
	
	public function Start(&$input,$options) {
		$error_files = Array();
		if (is_array($input)) {
			if (!empty($input)) {
				if (isset($input['tmp_name'])) {
					if (substr($file['name'],-4) != '.pdf') {
						$this->main->SESSION['forms'][$options['formname']]['elements'][$options['element']]['value'] = '';
						$error_files[] = $input['name'];
					}
				} else {
					foreach($input as $key => $file) {
						if (substr($file['name'],-4) != '.pdf') {
							unset($this->main->SESSION['forms'][$options['formname']]['elements'][$options['element']]['value'][$key]);
							unset($this->main->SESSION['forms'][$options['formname']]['elements'][$options['element']]['filesbefore'][$key]);
							unset($this->main->SESSION['forms'][$options['formname']]['elements'][$options['element']]['finalvalue'][$key]);
							$error_files[] = $file['name'];
						}
					}
				}
				if (!empty($error_files)) {
					return "Ein Nicht-PDF wurde entfernt!";
				}
			}
		}
		return true;
	}
}













