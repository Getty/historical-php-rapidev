<?

/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Text Class
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

if (!class_exists('RD_Text')) {

	class RD_Text extends RD_Object {
		
		public $Identifier;
		public $Args;
		public $Translated;
		
		public static $TextObjects = Array();
		public static $Default;
		public static $Lang = 'en';
		public static $FallbackLang = 'en';
		public static $AllowedLanguages = Array('none','en');
		public static $TextGathering;

		public static $Translator = Array();
		
		// TODO: Better Factory
		public function __construct($Identifier,$Args = Array(),$Translated = false) {
			$this->Identifier = $Identifier;
			if (isset(self::$TextGathering)) {
				RD_Util::WriteLn(self::$TextGathering,$Identifier);
			}
			if ($Args === true) {
				$Args = Array();
				$Translated = true;
			}
			$this->Args = $Args;
			$this->Translated = $Translated;
			self::$TextObjects[] = $this;
		}
		
		public static function AddTranslator($Translator) {
			self::$Translator[] = $Translator;
		}
		
		public static function SetLanguage($Lang) {
			self::$Lang = $Lang;
		}

		public static function GetLanguage() {
			return self::$Lang;
		}
		
		public static function GetAllowedLanguages() {
			return self::$AllowedLanguages;
		}
		
		public static function ResetAllowedLanguages() {
			self::$AllowedLanguages = Array();
			self::AddAllowedLanguage(self::$Lang);
			self::AddAllowedLanguage(self::$FallbackLang);
		}

		public static function AddAllowedLanguage($Lang) {
			if (!in_array($Lang,self::$AllowedLanguages)) {
				self::$AllowedLanguages[] = $Lang;
			}
			return self::$AllowedLanguages;
		}

		public static function IsAllowedLanguage($Lang) {
			return (!in_array($Lang,self::$AllowedLanguages));
		}

		public static function SetFallbackLanguage($Lang) {
			self::$FallbackLang = $Lang;
		}

		public static function GetFallbackLanguage() {
			return self::$FallbackLang;
		}
		
		public function Translate($Lang = NULL) {
			if ($Lang === NULL) {
				$Lang = self::$Lang;
			}
			$this->Translated = NULL;
			if ($this->Translated) {
				return $this->returnTranslated();
			} else {
				foreach(self::$Translator as $Translator) {
					$Translation = $Translator->Translate($this->Identifier,$Lang,self::$FallbackLang);
					if ($Translation && $this->Identifier != $Translation) {
						$this->Translated = $Translation;
						return $this->returnTranslated();
					}
				}
			}
			if (!isset(self::$Default)) {
				self::$Default = RD::$Self->GetConfig('RD_Text_Default');
			}
			if (isset(self::$Default[$Lang][$this->Identifier])) {
				$this->Translated = self::$Default[$Lang][$this->Identifier];
			} elseif ($Lang != self::$FallbackLang) {
				if (isset(self::$Default[self::$FallbackLang][$this->Identifier])) {
					$this->Translated = self::$Default[self::$FallbackLang][$this->Identifier];
				}
			}
			return $this->returnTranslated();
		}

		public function __sleep() {
			return Array('Identifier','Args');
		}
		
		public function __wakeup() {
			self::$TextObjects[] = $this;
		}
		
		public function returnTranslated() {
			if ($this->Translated) {
				return vsprintf($this->Translated,$this->Args);
			} else {
				return vsprintf($this->Identifier,$this->Args);
			}
		}

		public function __toString() {
			if (self::$Lang != 'none') {
				$this->Translate();
			}
			return $this->returnTranslated();
		}
		
	}

}

if (!class_exists('RDT') && !defined('DONT_LOAD_RDT')) {

	class RDT extends RD_Text {}

}

if (!function_exists('T') && !defined('DONT_LOAD_T')) {

	function T($Identifier,$Args = Array(),$Translated = false) {
		return new RDT($Identifier,$Args,$Translated);
	}
	
}
