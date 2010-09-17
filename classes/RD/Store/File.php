<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * File Caching Module
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
 * @copyright  2007 Torsten Raudssus
 * @license    GPL-2 
 * 
 */

class RD_Store_File extends RDM {

	public $DefaultFiledir;
	
	/* public $DefaultCompression = 'None';

	public function SetDefaultCompression($DefaultCompression) {
		$this->DefaultCompression = $DefaultCompression;
		return $this->main;
	}

	public function GetDefaultCompression() {
		return $this->DefaultCompression;
	} */
	
	public function Setup() {
		if (!isset($this->DefaultFiledir)) {
			$this->DefaultFiledir = $this->GetCacheDir();
		}
		return;
	}

	public function Save($Key,&$Data) {
		if (is_array($Key)) {
			$Key = implode(DIR_SEP,$Key);
		}
		if (strpos($Key,DIR_SEP) !== false) {
			$Dirs = explode(DIR_SEP,$Key);
			array_pop($Dirs);
			$Current = $this->DefaultFiledir.DIR_SEP;
			foreach($Dirs as $Dir) {
				$DirPath = $Current.$Dir;
				if (!is_dir($DirPath)) {
					mkdir($DirPath);
					chmod($DirPath,0755);
				}
				$Current = $DirPath.DIR_SEP;
			}
		}
		$File = $this->DefaultFiledir.DIR_SEP.$Key;
		if ($Data === NULL) {
			if (is_dir($File)) {
				rm_recursive($File);
			} else {
				unlink($File);
			}
			return true;
		}
		return RD_Util::Write($File,serialize($Data));
		
	}

	public function Load($Key) {
		if (is_array($Key)) {
			$Key = implode(DIR_SEP,$Key);
		}
		if (is_file($this->DefaultFiledir.DIR_SEP.$Key)) {
			return unserialize(RD_Util::Read($this->DefaultFiledir.DIR_SEP.$Key));
		}
		return NULL;
	}

}
