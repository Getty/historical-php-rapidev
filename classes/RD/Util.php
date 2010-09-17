<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Tools Class (mostly core functions also used by RDD that could be replaced)
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

if (!class_exists('RD_Util')) {

	class RD_Util {
		
		public static function IsCLI() {
			return php_sapi_name() == 'cli';
		}
		
		public static function Write($Filename,&$Data,$Append = false) {
			$Flags = $Append ? FILE_APPEND : 0;
			return file_put_contents($Filename,$Data,$Flags);
		}

		public static function WriteLn($Filename,$Data,$Append = true) {
			$NewData = strval($Data)."\n";
			return self::Write($Filename,$NewData,$Append);
		}
		
		public static function Read($Filename) {
			return file_get_contents($Filename);
		}
		
	}

}

