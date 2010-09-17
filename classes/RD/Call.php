<?

/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Call Class
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
 
 if (!class_exists('RD_Call')) {

	class RD_Call {

		// deprecateed API start vvvvvv

		public static function call_static($classname,$method,$params = Array()) {
			return self::CallStatic($classname,$method,$params);
		}
		
		public static function call_object($object,$method,$params = Array()) {
			return self::CallObject($object,$method,$params);
		}
		
		public static function call_func($func,$params = Array()) {
			return self::CallFunction($func,$params);
		}

		// deprecateed API end ^^^^^^

		public static function CallStatic($classname,$method,$params = Array()) {	
			return call_user_func_array(Array($classname,$method),$params);
		}

		public static function CallObject($object,$method,$params = Array()) {
			switch(count($params)) {
				
				case 0:
					return $object->$method();
					break;

				case 1:
					return $object->$method($params[0]);
					break;
				
				case 2:
					return $object->$method($params[0],$params[1]);
					break;
				
				case 3:
					return $object->$method($params[0],$params[1],$params[2]);
					break;
				
				case 4:
					return $object->$method($params[0],$params[1],$params[2],$params[3]);
					break;
				
				case 5:
					return $object->$method($params[0],$params[1],$params[2],$params[3],$params[4]);
					break;
				
				case 6:
					return $object->$method($params[0],$params[1],$params[2],$params[3],$params[4],$params[5]);
					break;
				
				case 7:
					return $object->$method($params[0],$params[1],$params[2],$params[3],$params[4],$params[5],$params[6]);
					break;
				
				case 8:
					return $object->$method($params[0],$params[1],$params[2],$params[3],$params[4],$params[5],$params[6],$params[7]);
					break;

				case 9:
					return $object->$method($params[0],$params[1],$params[2],$params[3],$params[4],$params[5],$params[6],$params[7],$params[8]);
					break;

			}
		}

		public static function CallFunction($func,$params = Array()) {
			switch(count($params)) {
				
				case 0:
					return $func();
					break;

				case 1:
					return $func($params[0]);
					break;
				
				case 2:
					return $func($params[0],$params[1]);
					break;
				
				case 3:
					return $func($params[0],$params[1],$params[2]);
					break;
				
				case 4:
					return $func($params[0],$params[1],$params[2],$params[3]);
					break;
				
				case 5:
					return $func($params[0],$params[1],$params[2],$params[3],$params[4]);
					break;
				
				case 6:
					return $func($params[0],$params[1],$params[2],$params[3],$params[4],$params[5]);
					break;
				
				case 7:
					return $func($params[0],$params[1],$params[2],$params[3],$params[4],$params[5],$params[6]);
					break;
				
				case 8:
					return $func($params[0],$params[1],$params[2],$params[3],$params[4],$params[5],$params[6],$params[7]);
					break;

				case 9:
					return $func($params[0],$params[1],$params[2],$params[3],$params[4],$params[5],$params[6],$params[7],$params[8]);
					break;
					
			}
		}
		
		// Choose your API, both functions stay supported

		public static function NewObject($ClassName,$params = Array()) {
			return self::CreateObject($ClassName,$params);
		}

		public static function CreateObject($ClassName,$params = Array()) {
			switch(count($params)) {
				
				case 0:
					return new $ClassName();
					break;

				case 1:
					return new $ClassName($params[0]);
					break;
				
				case 2:
					return new $ClassName($params[0],$params[1]);
					break;
				
				case 3:
					return new $ClassName($params[0],$params[1],$params[2]);
					break;
				
				case 4:
					return new $ClassName($params[0],$params[1],$params[2],$params[3]);
					break;
				
				case 5:
					return new $ClassName($params[0],$params[1],$params[2],$params[3],$params[4]);
					break;
				
				case 6:
					return new $ClassName($params[0],$params[1],$params[2],$params[3],$params[4],$params[5]);
					break;
				
				case 7:
					return new $ClassName($params[0],$params[1],$params[2],$params[3],$params[4],$params[5],$params[6]);
					break;
				
				case 8:
					return new $ClassName($params[0],$params[1],$params[2],$params[3],$params[4],$params[5],$params[6],$params[7]);
					break;

				case 9:
					return new $ClassName($params[0],$params[1],$params[2],$params[3],$params[4],$params[5],$params[6],$params[7],$params[8]);
					break;

			}
		}

		public static function Call($callback,$params = Array()) {
			if (is_array($callback)) {
				if (is_object($callback[0])) {
					return self::CallObject($callback[0],$callback[1],$params);
				} elseif (is_string($callback[0])) {
					return self::CallStatic($callback[0],$callback[1],$params);
				}
			} elseif (is_string($callback)) {
				return self::CallFunction($callback,$params);
			}
			return;
		}
		
	}

}

if (!class_exists('RDC') && !defined('DONT_LOAD_RDC')) {

	class RDC extends RD_Call {}
		
}