<?
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

class RD_RapiDev_Module_Compression extends RDM {

	public static $RD_Functions = Array(
		'Compress',
		'Decompress',
	);

	public static $RD_Depencies = Array();
	public $compression_cache = Array();

	public function Setup() {
		return;
	}
	
	/**
	 * compresses $data with algorithm given in $compression, there needs to be a $compression.php in compressions/$compression.php
	 *
	 * @param String $data
	 * @param String $compression
	 * @return String
	 */

	public function Compress($data,$compression = false) {
		if($compression==false) {
			$compression='none';
		}
		if(strtolower($compression)=='none') {
			return $data;
		}
		$this->PrepareCompression($compression);
		return $this->compression_cache[$compression]->Compress($data);
	}

	/**
	 * decompresses $data with algorithm given in $compression, there needs to be a $compression.php in compressions/$compression.php
	 *
	 * @param String $data
	 * @param String $compression
	 * @return String
	 */

	
	public function Decompress($data,$compression = false) {
		if($compression==false) {
			$compression='none';
		}
		if(strtolower($compression)=='none') {
			return $data;
		}
		$this->PrepareCompression($compression);
		return $this->compression_cache[$compression]->Decompress($data);
	}

	/**
	 * Checks if $compression.php exists and caches it if it exist
	 *
	 * @param String $compression
	 */
	
	protected function PrepareCompression($compression) {
		if(!array_key_exists($compression,$this->compression_cache)) {
			if ($File = $this->File('compressions'.DIR_SEP.$compression.'.php')) {
				require_once($File);
			} else {
				throw new RDE('RDM_Compression: dont have an executable for the compression '.$compression);
			}
			$class_name='RDM_Compression_'.ucfirst($compression);

			$this->compression_cache[$compression]=new $class_name($this->main);
		}
	}

}

