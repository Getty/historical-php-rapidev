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
 * @author     Sascha Guilliard <S.Guilliard@cpp-tools.de>
 * @copyright  2007 Sascha Guilliard
 * @license    GPL-2 
 * 
 */


class RDM_Compression_Bz extends RDM {

	protected function Check() {
		if(!function_exists('bzcompress') || !function_exists('bzdecompress')) {
			throw new RDE('RDM_Compression_Bz: bzcompress and/or bzdecompress undefined');
		}
	}
	
	public function Compress($data) {
		$this->Check();
		return bzcompress($data);
	}
	
	public function Decompress($data) {
		$this->Check();
		return bzdecompress($data);
	}	
}