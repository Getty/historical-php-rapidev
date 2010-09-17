<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Debugging Class
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

if (!defined('TRACE')) define('TRACE',1);
if (!defined('DEBUG')) define('DEBUG',2);
if (!defined('INFO')) define('INFO',3);
if (!defined('WARN')) define('WARN',4);
if (!defined('ERROR')) define('ERROR',5);
if (!defined('FATAL')) define('FATAL',6);

if (!class_exists('RD_Debug')) {

	require_once(RD_PATH.DIR_SEP.'classes'.DIR_SEP.'RD'.DIR_SEP.'Util'.DIR_SEP.'HTMLDebug.php');
	require_once(RD_PATH.DIR_SEP.'classes'.DIR_SEP.'RD'.DIR_SEP.'Util.php');

	class RD_Debug {
		
		public static $Logfile;
		public static $MinLevel = TRACE;
		public static $Debug = true;
		public static $MemUsage = true;
		public static $ShowError = true;
		public static $ShowDebug = false;
		public static $LogCache = Array();
		public static $HTTPDebugFunction = Array('RD_Util_HTMLDebug','VarDump');
		public static $EchoHTTPDebugFunction = true;
		public static $CLIDebugFunction = 'var_export';
		public static $EchoCLIDebugFunction = true;
		public static $EnableUniqueId = false;
		public static $DisableMemUsage = false;
		public static $DisableTime = false;
		public static $DisableIp = false;
		public static $PreventErrorsByPath = false;

		public static $DateFormat = 'H:i:s m.d.Y';

		public static $FirstTime;
		
		public static $LevelMap = Array(
										'1' => 'TRACE',
										'2' => 'DEBUG',
										'3' => 'INFO',
										'4' => 'WARN',
										'5' => 'ERROR',
										'6' => 'FATAL'
									);
									
		public static $ErrorMap = Array(
										'1' => 'E_ERROR',
										'2' => 'E_WARNING',
										'4' => 'E_PARSE',
										'8' => 'E_NOTICE',
										'16' => 'E_CORE_ERROR',
										'32' => 'E_CORE_WARNING',
										'64' => 'E_COMPILE_ERROR',
										'128' => 'E_COMPILE_WARNING',
										'256' => 'E_USER_ERROR',
										'512' => 'E_USER_WARNING',
										'1024' => 'E_USER_NOTICE',
										'2048' => 'E_STRICT'
									);
	

		/**
		 * Adding Logentry
		 *
		 * @param String $text
		 * @param Integer $level
		 * @param Integer $trace_level
		 * @return Integer/void
		 */
		public static function Log( $text, $level = DEBUG, $trace_level = 0, $depth = 2) {
			
			if ($level < self::$MinLevel) {
				return;
			}
						
			if (isset(self::$Logfile) && self::$Debug) {
				
				$params = Array();
				$params['text'] = $text;
				$params['level'] = $level;
				$params['from'] = self::CallFrom($depth);
				
				if ($level == TRACE) {
					$params['trace_level'] = $trace_level;
				}
				
				if (self::$MemUsage && function_exists('memory_get_usage') && !self::$DisableMemUsage) {
					$params['mem'] = memory_get_usage();
				}

				if (!self::$DisableTime) {
					if (!isset(self::$FirstTime)) {
						self::$FirstTime = microtime(true);
						$params['time'] = 0;
					} else {
						$params['time'] = microtime(true) - self::$FirstTime;
					}
				}
				$return = self::AddLog($params);
				
				// REFACTOR ME (TODO) => MakeLogLine
				if (self::$ShowDebug) {
					if (!RD_Util::IsCLI()) {
						echo "<pre>[".$params['from']."] ";
					}
					if (!is_string($params['text'])) {			
						self::EchoDebugDump($params['text']);
					} else {
						if ($params['text'] == '*') {
							echo str_repeat('*',80);
						} else {
							echo $params['text'];							
						}
					}
					if (!RD_Util::IsCLI()) {
						echo "</pre>";
					}
					echo "\n";
				}
	
				return $return;
				
			}
			return;
		}

		public static function CallFrom($depth = 2) {
			$ar_backtrace = debug_backtrace();
			if (isset($ar_backtrace[$depth])) {
				$logtext_function = $ar_backtrace[$depth]["function"];
				if (isset($ar_backtrace[$depth]["class"])) {
					$logtext_class = $ar_backtrace[$depth]["class"];
				} else {
					$logtext_class = 'GLOBAL';
				}
			} else {
				$logtext_function = '';
				$logtext_class = '';
			}
			
			if (empty($logtext_class) && empty($logtext_function)) {
				$logtext_from = "GLOBAL";
			} else {
				if (empty($logtext_class)) {
					$logtext_from = $logtext_function;
				} else {
					$logtext_from = $logtext_class.'::'.$logtext_function;
				}
			}
			
			return $logtext_from;
		}

		protected static function AddLog($params) {
			return self::WriteLog(self::MakeLogLine($params));
		}
	
		public static function MakeLogLine($params) {
			array_push(self::$LogCache,$params);
	
			$string = self::$LevelMap[$params['level']];
	
			if (isset($params['trace_level'])) {
				$string .= '-'.$params['trace_level'];
			}
			
			// TODO: time to date
			//$string .= ' ['.date(self::$DateFormat,$params['time']).'] ';
			if (isset($params['time'])) {
				$string .= ' ['.$params['time'].'] ';
			}
			
			if (isset($params['mem'])) {
				$string .= '('.$params['mem'].') ';
			}
			
			if (self::$EnableUniqueId && isset($_SERVER['UNIQUE_ID']) && !empty($_SERVER['UNIQUE_ID'])) {
				$string .= ' '.$_SERVER['UNIQUE_ID'].' ';
			}
			
			if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && !self::$DisableIp) {
				$string .= ' ['.$_SERVER['REMOTE_ADDR'].'] ';
			} else {
				$string .= ' ';
			}
			
			if (!is_string($params['text'])) {			
				$string .= self::ReturnDebugDump($params['text']);
			} else {
				$string .= $params['text'];
			}
			
			$string .= "\n";
	
			return $string;
		}
		
		protected static function WriteLog($text) {
			if (isset(self::$Logfile) && self::$Logfile) {
				return RD_Util::Write(self::$Logfile,$text,true);
			}
			return false;
		}
	
		public static function Exception($Exception)
		{
			$params = Array();
			$params['text'] = '[[CODE_'.$Exception->getCode().
							  ':'.$Exception->getFile().':'.$Exception->getLine().']] '.
							  $Exception->getMessage();

				if (!self::$DisableTime) {
					if (!isset(self::$FirstTime)) {
						self::$FirstTime = microtime(true);
						$params['time'] = 0;
					} else {
						$params['time'] = microtime(true) - self::$FirstTime;
					}
				}

			if (self::$MemUsage && function_exists('memory_get_usage')) {
				$params['mem'] = memory_get_usage();
			}
			
			$params['level'] = FATAL;
			$params['from'] = self::CallFrom(2);
			$params['fulltrace'] = debug_backtrace();

			self::AddLog($params);
	
			if (self::$ShowError) {
				if (RD_Util::IsCLI()) {
					echo 'EXCEPTION: '.$params['text']."\n";					
				} else {
					echo "<b style='color: red'>EXCEPTION: ".$params['text']."</b>\n";					
				}
				$Trace = $Exception->getTrace();
				$Trace = self::FormatExceptionTrace($Trace);
				self::EchoTraceDump($Trace);
			}
	
			
			if (function_exists('RD_Cleanup')) { 
			    RD_Cleanup(); 
			}
			
			if (function_exists('RDD_FATAL')) {
				RDD_FATAL($Exception);
			} else {
				die('i am out of here');
			}
			return;
		}
		
		public static function Error($ErrNo, $ErrStr, $ErrFile, $ErrLine) {
			if ($ErrNo == E_STRICT) {
				return;
			}
			
			// catch smarty TODO
			if (strstr($ErrFile,'core.get_include_path.php')) {
				return ;
			}
			
			// catch double session_start()
			if (strstr($ErrStr,'session_start()')) {
				return ;
			}
	
			// catch stupid PHP 5.1 error
			if (strstr($ErrStr,'Indirect modification of overloaded property')) {
				return ;
			}
					
			// catch smarty_compile TODO
			if (strstr($ErrFile,'smarty_compile')) {
				return ;
			}
			
			// catch mysql_escape_string() 
			if(strstr($ErrStr, 'mysql_real_escape_string()')){
				return ;
			}
			
			// catch the noob programmers notices from vB
			if (self::$PreventErrorsByPath && strstr($ErrFile, '/'.self::$PreventErrorsByPath.'/')) {
				RDD::Log('[['.self::$ErrorMap[$ErrNo].':'.$ErrFile.':'.$ErrLine.']] '.$ErrStr, ERROR);
				return;
			}
			
			// catch akismet warnings
			if(!defined('AKISMET_WARNINGS') && stristr($ErrStr, 'akismet')) {
				RDD::Log($ErrStr,WARN);
				return;
			}
			
			$params = Array();
			$params['text'] = '[['.self::$ErrorMap[$ErrNo].':'.$ErrFile.':'.$ErrLine.']] '.$ErrStr;

			if (!self::$DisableTime) {
				if (!isset(self::$FirstTime)) {
					self::$FirstTime = microtime(true);
					$params['time'] = 0;
				} else {
					$params['time'] = microtime(true) - self::$FirstTime;
				}
			}
			
			if (self::$MemUsage && function_exists('memory_get_usage')) {
				$params['mem'] = memory_get_usage();
			}
			
			$params['level'] = ERROR;
			$params['from'] = self::CallFrom(2);
			$params['fulltrace'] = debug_backtrace();
			
			self::AddLog($params);
			
			if (self::$ShowError) {
				echo "<b style='color: red'>ERROR: ".$params['text']."</b>\n";
				// $trace = debug_backtrace();
				// echo "<pre>Backtrace:\n".self::EchoDebugDump($trace)."</pre>";
			}
			
			return;
		}
	
		public static function GetLog() {
			return self::$LogCache;
		}
		
		public static function SetLogfile($Logfile) {
			if (!defined('RD_LOG')) {
				self::$Logfile = $Logfile;
			}
			return;
		}
		
		public static function ReturnDebugDump($var) {
			return var_export($var,true);
		}
		
		public static function EchoTraceDump($var) {
			if (RD_Util::IsCLI()) {
				print_r($var);
			} else {
				$Callback = self::$HTTPDebugFunction;
				if (self::$EchoHTTPDebugFunction) {
					echo RDC::Call($Callback,Array($var));
				} else {
					RDC::Call($Callback,Array($var));
				}
			}
		}

		public static function EchoDebugDump($var) {
			if (self::$Debug && !RD_Util::IsCLI()) {
				$Callback = self::$HTTPDebugFunction;
				if (self::$EchoHTTPDebugFunction) {
					echo RDC::Call($Callback,Array($var));
				} else {
					RDC::Call($Callback,Array($var));
				}
			} elseif (RD_Util::IsCLI()) {
				$Func = self::$CLIDebugFunction;
				if (self::$EchoCLIDebugFunction) {
					echo $Func($var);					
				} else {
					$Func($var);
				}
			}
		}

		/**
		 * Formatiert das Trace-Array einer Exception so um, dass die Array-Keys
		 * den Namen der Funktion oder Klassen- und Methodennamen der Methode aus-
		 * gibt, in der die Exception geworfen wurde.
		 *
		 * @static 
		 * @author Sven Strittmatter <strittmatter@webix.de>
		 * 
		 * @param array $traces
		 * @return array
		 */
		public static function FormatExceptionTrace(array $traces) {
			$return = array();
			
			foreach ($traces as $index => $trace) {
				$key = $index;
				
				if (isset($trace['class'])) {
					$key = $trace['class'].'::';
				}
				
				if (isset($trace['function'])) {
					if (is_numeric($key)) {
						$key = $trace['function'].'()';
					} else {
						$key .= $trace['function'].'()';
					}
				}
				
				$return[$key] = $trace;
			}
			
			return $return;
		}
	}
	
	if (defined('RD_LOG')) {
		RDD::$Logfile = RD_LOG;
	}
	
	if (defined('RD_LOG_LEVEL')) {
		RDD::$MinLevel = RD_LOG_LEVEL;
	}

}

if (!class_exists('RDD') && !defined('DONT_LOAD_RDD')) {

	class RDD extends RD_Debug {}
		
}

if (defined('STAGE') && 'dev' === STAGE) {
	error_reporting(E_ALL);
	set_error_handler(Array('RDD','Error'));
}
set_exception_handler(Array('RDD','Exception'));
