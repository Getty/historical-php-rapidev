<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Application Core Class
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

if (isset($RD_Exit) && $RD_Exit) {
	exit(0);
}

if (!defined('RD_PATH')) define('RD_PATH',realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'));
if (!defined('DIR_SEP')) define('DIR_SEP',DIRECTORY_SEPARATOR);
if (!defined('DIR_UP')) define('DIR_UP', DIR_SEP.'..');
if (!defined('PATH_SEP')) define('PATH_SEP',PATH_SEPARATOR);

require_once(RD_PATH.DIR_SEP.'classes'.DIR_SEP.'RD'.DIR_SEP.'Object.php');
require_once(RD_PATH.DIR_SEP.'classes'.DIR_SEP.'RD'.DIR_SEP.'Util.php');
require_once(RD_PATH.DIR_SEP.'classes'.DIR_SEP.'RD'.DIR_SEP.'Call.php');
require_once(RD_PATH.DIR_SEP.'classes'.DIR_SEP.'RD'.DIR_SEP.'Exception.php');
require_once(RD_PATH.DIR_SEP.'classes'.DIR_SEP.'RD'.DIR_SEP.'Text.php');
require_once(RD_PATH.DIR_SEP.'classes'.DIR_SEP.'RD'.DIR_SEP.'Debug.php');
require_once(RD_PATH.DIR_SEP.'classes'.DIR_SEP.'RD'.DIR_SEP.'Module.php');

/**
 * 
 * Die primüre Klasse des Application Framework. Das eigentliche Projekt 
 * erweitert diese Klasse nur um dort dann das Framework oder die Module
 * mit einigen Funktionen zu konfigurieren:
 * 
 * 	class project extends RD {
 * 		public function SetupPage() {
 * 			$this->SetPageDefault('first');
 * 		}
 * 	}
 * 
 * In der eigentlichen index.php, die als einzige Datei wirklich über den
 * Webserver erreicht werden muss, wird nur noch die Projekt-Klasse 
 * instanziiert.
 *
 */

class RD extends RDO {
	public static $DisableSession = false;
	
	/**
	 * 
	 * Kopie der Umgebungsvariablen werden hier abgelegt. Es ist geplant
	 * diese Teile noch weiter auszulagern (z.b. Session).
	 * 
     * @var     Array/String
     */
	public $SERVER;
	public $POST;
	public $GET;
	public $SESSION;
	public $COOKIE;
	public $FILES;
	public $PHP_SELF;

	/**
	 * 
	 * Die sogenannten "Roots" Verzeichnisse diese Verzeichnisse werden
	 * genutzt um die eigentlichen Daten oder weitere Module für das
	 * Projekt zur Verfügung zu stellen. Erweitert werden diese mit der
	 * Funktion $RD->addRoot('/path'). Das Verzeichniss welches als
	 * letztes hinzugefügt wird hat die grüüte Gewichtung. Das eigentliche
	 * Framework befindet sich daher grundsützlich vorab schon in dieser
	 * Variable.
	 * 
     * @var     Array
     */
	private $Roots = Array(RD_PATH);
	
	/**
	 * 
	 * Das "Cache" Verzeichniss wird vom Framework genutzt um Dateien
	 * zur Performance Optimierung oder Session übergreifenden Kommunikation
	 * anzulegen. Gesetzt wird es mit $RD->setCacheDir();
	 * 
     * @var     Array
     */
	private $CacheDir;

	/**
	 * TODO // Gedanke eines Array was übergreifend einige Hauptfaktoren konfiguriert.
	 */
	public $Config;

	/**
	 * Diese Variable gibt an welche Module geladen werden sollen. Bei true werden
	 * alle Module geladen die das Framework finden kann, ansonsten gibt man ein
	 * Array an mit den Modulen die man laden müchte. Es funktioniert intern wie ein
	 * Filter, d.h. Module die hier angegeben werden und nicht existieren erzeugen
	 * keinen Fehler.
	 * 
	 * TODO: private machen / Modifikationsfunktionen (abgesicherte)
	 * 
     * @var     Array/Bool
     */
	public $LoadModules = true;
	protected $DontLoadModules = Array();
	protected $StopInitModule;
	public $CoreCache = true;
	
	/**
	 * Diese Verzeichnisse in den "Roots" sind freigegeben für den Download
	 * über die ?file=images/image.jpg Methode runtergeladen zu werden.
	 * ACHTUNG: Wenn die per Default enthaltenden Verzeichnisse nicht in
	 * der Liste bleiben, werden einige Framework Funktionalitüten nicht
	 * zur Verfügung stehen oder ggf. sogar Fehlfunktionen erzeugen.
	 * 
	 * TODO: private machen / Modifikationsfunktionen (abgesicherte)
	 * 
     * @var     Array/Bool
     */
	public $PublicDirs = Array(
		'js' => 'javascripts',
		'img' => 'images',
		'images' => 'images',
		'css' => 'css',
	);

	/**
	 * Diese Verzeichniss wird genutzt um temporüre Installationsdateien
	 * anzulegen. Bisher ist es z.b. das Debugging Logfile der Installation
	 * 
	 * TODO: private machen / getter setter
	 * 
     * @var     String
     */
	public $InstallCacheDir = '/tmp/rapidev';
	
	/**
	 * In diesen Variablen speichert das Framework seine gesammelten Daten
	 * welches Module welche Funktionen und Hooks bereitstellt und hier
	 * lagern auch die eigentlichen Modul-Instanzen. Wie dieser Bereich
	 * abgesichert werden soll steht noch zur Diskussion, derzeit muss das
	 * $Modules auf public stehen weil Module darauf zugreifen. Dies sollte
	 * überdacht werden.
	 * 
	 * @var 	Array 
	 */
	protected $HooksModules = Array();
	public $Modules = Array();
	protected $ModulesFunctions = Array();
	protected $ModulesFunctionSpaces = Array();
	protected $ModulesCoreFunctions = Array();
	protected $LoadedModules = Array();
	
	protected $FileCache = Array();
	protected $DirsCache = Array();
	protected $DirCache = Array();
	
	protected $CLI;
	protected $InitRand = false;
	
	/**
	 * Diese Variablen definiren wie sich das Framework bei Fehlern zu
	 * verhalten hat. $TryAgain definiert ob bei einem Fehler der Programm-
	 * ablauf wiederholt werden soll, falls dieses genutzt wird, definiert
	 * $ErrorCountMax wieviele Versuche gemacht werden.
	 * 
	 * TODO: getter setter
	 * 		 Module mit Fallback Verfahren wie POST lüschen usw.
	 *
	 * @var Integer/Bool
	 */
	private $ErrorCountMax = 1; // Wann bricht das Framework die Bearbeitung ab
	private $TryAgain = true; // Soll nach einem Error ein weitere Start Versuch gemacht werden
	
	/**
	 * Diese Variable beinhaltet die Anzahl der bisher gemachten Fehler
	 * 
	 * TODO: getter / setter
	 *
	 * @var Integer
	 * @access private
	 */
	private $ErrorCount = 0;
	
	public $URLControl = false;
	
	public $GetFile = true;

	public static $Self;
	
	/*************************  Konstruktor und Installation  *******************************/
	
	/**
	 * 
	 * Der Konstruktor der Klasse instanziiert die eigentliche Funktionalitüt. Mit dem
	 * Parameter kann man definieren ob die Installations Routinen (false) oder die 
	 * eigentlichen Ablauf Routinen (true) gestarten werden sollen. Die eigentliche
	 * Installation wird aber NICHT vom Konstruktor gestartet, sondern muss von der
	 * erweiterten Klasse durchgeführt werden mit $RD->Install().
	 * 
	 * Im Konstruktor wird auch geprüft ob ein "Direct Module Call" gemacht wurde, oder
	 * ein "File Request", letzteres noch vor der Initalisierung der Module, um die 
	 * Ladegeschwindigkeit zu optimieren.
	 *
	 * @param Bool $Start
	 */
	public function __construct($Start = true) {

		RDD::Log('Starting RD Constructor',INFO);

		if (self::$DisableSession) {
			RDD::Log('ATTENTION: Session is disabled by RD::$DisableSession !!!', WARN);
		}
		
		self::$Self = $this;

		if (!defined('STAGE')) { trigger_error('no stage defined',E_USER_ERROR); }
		
		if ($this->MethodExists('SetupRD')) {
			$this->SetupRD();
		}

		if ($this->MethodExists('AllRoot')) {
			$this->AddAllRoot();
		}
		
		if ($this->MethodExists('AllModules')) {
			$this->AllModules();
		}

		// Crap => TODO
		if (!$Start && !isset($this->CacheDir)) {
			RDD::Log('Going into installation mode and using installation cache dir',TRACE,1009);
			$this->useInstallCacheDir();
		}
		
		$this->SERVER = $_SERVER;
		
		if (!isset($this->PHP_SELF)) {
			$this->PHP_SELF = $this->SERVER['PHP_SELF'];
		}

		$this->CheckForFile();
		
		if ($this->CoreCache) {
			$this->LoadCoreCache();
		}

		$this->Init();
		$this->StopInit(); // Checken ab ob ein Modul gerufen wird.

		if ($Start) {
			$this->Start();	
		} else {
			$this->Save();
		}
		
		return;
		
	}

	public function CheckForFile() {

		if ($this->GetFile && isset($_GET['file'])) {
			$file = $_GET['file'];
			$this->FetchFile($file);
			exit(0);
		} elseif ($this->URLControl && !RD::IsCLI()) {
			$fileparts = explode(DIR_SEP,substr($_SERVER['REQUEST_URI'],1));

			if (in_array($fileparts[0],array_keys($this->PublicDirs))) {
				$this->FetchFile(substr($_SERVER['REQUEST_URI'],1));
				exit(0);
			}

		}
		
	}
	
	public function Random($Min = NULL, $Max = NULL) {
		if (!$this->InitRand) {
			mt_srand((double)microtime()*1000000);
			$this->InitRand = true;
		}
		if ($Max === NULL) {
			if ($Min === NULL) {
				return mt_rand();				
			} else {
				return mt_rand($Min);
			}			
		} else {
			return mt_rand($Min,$Max);
		}
	}
	
	public function IsCLI() {
		if (!isset($this->CLI)) {
			$this->CLI = RD_Util::IsCLI();
		}
		return $this->CLI;
	}
	
	public static function PHP_SELF() {
		return self::$Self->PHP_SELF;
	}
	
	/**
	 * Anlegen des Installationsverzeichniss und einstellen von diesem als Cache
	 * Verzeichniss. Eine überlagerung durch das Projekt ist hier angedacht um
	 * eventuelle spezielle Installationsprozeduren zum anlegen des Verzeichnisses
	 * zu ermüglichen oder dieses Verzeichniss erst noch zu ermitteln.
	 */
	protected function useInstallCacheDir() {
		if (!file_exists($this->InstallCacheDir)) {
			mkdir($this->InstallCacheDir);
		}
		$this->SetCacheDir($this->InstallCacheDir);
		// unlink($this->InstallCacheDir);
		return;
	}
	
	protected function AddModule($ModuleName = NULL) {
		if ($ModuleName === NULL || !is_array($this->LoadModules)) {
			$this->LoadModules = Array();
		}
		if ($ModuleName !== NULL) {
			$this->LoadModules[] = $ModuleName;
		}
		return $this;
	}
	
	/**
	 * Installationsprozedur einleiten und den Hook "Install" aufrufen. Bei
	 * Abschluss ohne eine Exception wird im Cache Verzeichniss eine
	 * "installed" Datei angelegt.
	 * 
	 * TODO: installed Datei darf nicht im temporüren Cache Verzeichnisses
	 *       liegen.
	 */
	public function Install() {
		
		if (!file_exists($this->CacheDir.DIR_SEP.'installed')) {
			$this->useInstallCacheDir();
			try {
				$error = false;
				$this->Hook('Install');
			} catch (Exception $e) {
				$error = true;
			}
			if (!$error) {
				touch($this->CacheDir.DIR_SEP.'installed');
			} else {
				echo $e->getTraceAsString();
				throw new RDE('RD: Install failed with:'.$e->getMessage());
			}
		}
		return;
		
	}
	
	/**
	 * Initalisierung der Module, starten der Hooks "PreInit", "Init" und "PostInit"
	 * 
	 * TODO: Entschlackung durch Session Modul
	 */
	
	public function Init() {

		RDD::Log('Init RD',TRACE,1000);

		$this->Hook('PreInit');

		if (!isset($this->CacheDir)) {
			throw new RDE('need a cache directory');
		}

		RDD::Log('Starting Session',TRACE,1000);
		if (function_exists('session_start') && !isset($_SESSION) && !$this->IsCLI() && !self::$DisableSession) {
			@session_start();
		}

		$this->LoadFunctions();
		$this->LoadModules();
		
		RDD::Log('Preparing Environment',TRACE,1000);
		$this->ResetEnv(true);
		
		$this->Hook('Init');

		$this->Hook('PostInit');
		
		// TODO Workaround wegen fehlenden Modulabhaengigkeiten
		$this->Hook('PostPostInit');
		$this->Hook('PostPostPostInit');

		return;

	}
	
	/**
	 * Checkt ab ob ein Modul gerufen wird. Z.B. fuer AJAX. Es wird auf dem 
	 * angegebenen Module (GET/POST)die Methode RDM_{MODULENAME}::HookModule{MODULENAME}() 
	 * aufgerufen. Die weitere Ausfuehrung von Rapidev wird danach abgebrochen.
	 *
	 * @access public
	 */
	public function StopInit() {
		if (isset($_GET['module'])) {
			$Module = $_GET['module'];
		} elseif (isset($_POST['module'])) {
			$Module = $_POST['module'];
		} elseif (isset($this->StopInitModule)) {
			$Module = $this->StopInitModule;
		}
		if (isset($Module)) {
			if (isset($this->Modules[$Module])) {
				RDD::Log('Direct module call found for '.$Module.', calling hook',TRACE,1001);
				$Hook = 'Module'.ucfirst($Module);
				$this->Hook($Hook);
				exit(0);
			}
			RDD::Log('Cant find module '.$Module.' for direct module call',ERROR,1001);
			exit(1);
		}
	}
	
	public function DontLoad($Module = NULL) {
		if ($Module === NULL) {
			$this->LoadModules = false;
		} else {
			if (!in_array($Module,$this->DontLoadModules)) {
				$this->DontLoadModules[] = $Module;
			}
		}
		return $this;
	}
	
	public function Load($Module = NULL) {
		if ($Module === true || $Module === NULL) {
			$this->LoadModules = true;
		} elseif ($Module === false) {
			$this->LoadModules = false;
		} else {
			if (!is_array($this->LoadModules)) {
				$this->LoadModules = Array();
			}
			if (!in_array($Module,$this->LoadModules)) {
				$this->LoadModules[] = $Module;
			}
			RDD::Log($this->LoadModules);
		}
		return $this;
	}
	
	public function DirIt($Dirname) {
		return new DirectoryIterator($Dirname);
	}
	
	public function LoadRDOnly() {
		foreach($this->DirIt(RD_PATH.DIR_SEP.'modules'.DIR_SEP) as $Module) {
			if (substr($Module,0,1) != '.') {
				$this->Load(strval($Module));
			}
		}
	}
	
	/*************************** System Ablauf ****************************************/
	
	/**
	 * Primüre Ablaufsfunktion. Hier lüuft das eigentliche Framework ab und definiert
	 * die Ablaufstrukturen. Regulür ist diese Funktion diejenige die überschrieben wird,
	 * wenn man z.b. nicht den klassischen Ablauf einer HTTP Abfrage hat, sondern das
	 * Framework für Einzelaufgaben oder dauerhaften Diensten nutzt. In den "Direct Module
	 * Calls" über ?module=first kann auch so über $this->Start() vom Modul aus direkt
	 * der gesamte Ablauf gestartet werden. Zumeist wird dies genutzt um z.b. zuerst
	 * dem View Modul zu sagen das es nur das "content.tpl" rendern soll, aber die
	 * eigentlichen Programmablüufe regulür wie bei jedem anderen Request ablaufen
	 * lüüt. Dadurch kann man jede Seite quasi stündig über Ajax Requests nur im Content
	 * Bereich zu erneuern ohne einen echten reload durchzufuehren.
	 * 
	 * Hier werden die Hooks "PreStart", "Start" und "PostStart" ausgeführt. Hier ist
	 * auch die erste Exception Sicherung. Falls vorher eine Exception geworfen wird,
	 * werden diese nur von dem Exception Handler abgefangen, der in der Debugging Klasse
	 * gesetzt worden ist. Eine Funktion RD_FATAL() wird im allerschlimmsten Fall auf-
	 * gerufen um z.b. ein letzte Seite für den User anzuzeigen.
	 *
	 */
	public function Start() {

		try {
		
			$this->Hook('PreStart');
			$this->Hook('Start');
			$this->Hook('PostStart');
			
			$this->Finish();
			
		} catch (Exception $Exception) {
			
			RDD::Exception($Exception);
			
			$this->Error();
			
		}
		
		return;
		
	}
	
	/**
	 * Die Abschlussfunktionen zum Beenden des Durchlaufs. Hier wird regulür vom View 
	 * Module die eigentliche Webseite gerendert und dargestellt.
	 *
	 */
	public function Finish() {
		
		try {
		
			$this->Hook('PreFinish');
			$this->Hook('Finish');
			$this->Hook('PostFinish');
			
			$this->Save();
			
			$this->Cleanup();
			
		} catch (Exception $Exception) {
			
			RDD::Exception($Exception);
			
			$this->Error();
			
		}
		
		return;
		
	}
	
	/**
	 * Aufrüum arbeiten nach der Abarbeitung (Debugging Console)
	 * Errors die in diesem Bereich passieren werde grundsützlich ignoriert
	 * Bitte beachte das ->Save() schon durchgelaufen ist, d.h. es werden alle
	 * projektbezogenen Daten sowieso verworfen.
	 *
	 */
	public function Cleanup() {
		
		static $Done = false;

		if (!$Done) {
			
			$Done = true;
		
			try {
			
				$this->Hook('PreCleanup');
				$this->Hook('Cleanup');
				$this->Hook('PostCleanup');
			} catch (Exception $Exception) {
				
				/* WE IGNORE ERRORS HERE */
				
			}
			
			if (function_exists('session_write_close') && !$this->IsCLI() && !self::$DisableSession) {
				session_write_close();
			}
		}
		
		return;
		
	}
	
	/****************************** Funktionalitüten *******************************************/
	
	/**
	 * Eine Datei direkt vom Dateisystem zu holen. Bei $echo true wird diese direkt zurückgegeben
	 * was beim "File Request" benutzt wird. Wenn $echo false ist wird der Inhalt der Datei
	 * zurückgegeben. Der $file wird in allen "Roots" gesucht, daher darf kein absoluter Dateiname
	 * angegeben werden, wie bei allen RapiDev Funktionalitüten mit Dateinamen.
	 * 
	 * TODO: Innen drin werden derzeit noch ein paar Austauschungen gemacht in JavaScripts,
	 *       dafür sollte ein System entwickelt werden womit der User Funktionen dafür ablegen
	 *       kann.
	 *
	 * @param String $file
	 * @param Bool   $echo
	 */	
	protected function FetchFile($file,$echo = true) {
		RDD::Log('FetchFile of '.$file,TRACE,1500);

		if (strpos($file, "?") !== false) {
			$urlparts = explode("?", $file);
			$file = $urlparts[0];
			if (isset($urlparts[1])) {
				$param = $urlparts[1];
			}
		}

		$fileparts = explode('/',$file);

		foreach($fileparts as $filepart) {
			if (strpos($filepart,'..') !== false) {
				throw new RDE('RD: access denied to relative parts in directory structure');
			}
		}
		
		if (isset($param)) {
			if (strpos($param, 'x') !== false && strpos($param, '&') === false) {
				$xy = explode('x',$param);
				$x = $xy[0]+0;
				$y = 0;
				if (isset($xy[1])) {
					$y = $xy[1]+0;
				}
				$resize = true;
				/* $lastone = $fileparts[count($fileparts)-1];
				$lastfour = substr($lastone,-4);
				if ($lastfour == '.jpg') {
					$fileparts[count($fileparts)-1] = substr($lastone,0,-4);
					$resize = true;
				} */
			}
		}
		
		if (in_array($fileparts[0],array_keys($this->PublicDirs)) || file_exists($_SERVER['DOCUMENT_ROOT'].DIR_SEP.implode(DIR_SEP,$fileparts))) {

			foreach($this->PublicDirs as $From => $To) {
				if ($fileparts[0] == $From || file_exists($_SERVER['DOCUMENT_ROOT'].DIR_SEP.implode(DIR_SEP,$fileparts))) {
					if (file_exists($_SERVER['DOCUMENT_ROOT'].DIR_SEP.implode(DIR_SEP,$fileparts))) {
						$Filename = $_SERVER['DOCUMENT_ROOT'].DIR_SEP.implode(DIR_SEP,$fileparts);
					} else {
						$fileparts[0] = $To;
						$Filename = $this->File(implode(DIR_SEP,$fileparts));
					}
					if (!$Filename) {
						RDD::Log("cant find file ".implode(DIR_SEP,$fileparts));
						exit(0);
					}
					header('Last-Modified: '.date("r", filemtime($Filename)));
					$this->RequireOnceFile('functions/get_mime_type.php');
					$this->RequireOnceFile('functions/smart_image_resize.php');
					$mime_type = get_mime_type($Filename);
					if (!$mime_type) {
						$mime_type = 'text/plain';
					}
					if (strpos($mime_type,'x-httpd-php') !== false) {
						include($Filename);
					} else {
						if (isset($resize)) {
							/* $filemd5 = md5_file($Filename);
							$firsttwo = substr($filemd5,0,2);
							$rest = substr($filemd5,2);
							if (!is_dir($this->CacheDir.DIR_SEP.'resizecache')) {
								mkdir($this->CacheDir.DIR_SEP.'resizecache');
								chmod($this->CacheDir.DIR_SEP.'resizecache',777);
							}
							if (!is_dir($this->CacheDir.DIR_SEP.'resizecache'.DIR_SEP.$firsttwo)) {
								mkdir($this->CacheDir.DIR_SEP.'resizecache'.DIR_SEP.$firsttwo);
								chmod($this->CacheDir.DIR_SEP.'resizecache'.DIR_SEP.$firsttwo,777);
							} */
							smart_resize_image($Filename,$x,$y,true,'browser');
						} else {
							header('Content-type: '.$mime_type);
							readfile($Filename);
						}
					}
					exit(0);
				}

			}

		} else {
			throw new RDE('RD: access denied to this file');
		}
	}
	
	/**
	 * Die Umgebungsvariablen absichern für eine spütere Rücksicherung
	 * im Fehlerfall, oder zur Modifikation der POST und GET Variablen
	 * für "Dirty Hacks". $init wird direkt an die Module bei den Hooks
	 * "PreResetEnv" und "PostResetEnv" als Parameter übergeben. $init
	 * soll nur true gesetzt werden bei aller erster Nutzung der
	 * Funktion. Dies passiert durch die $RD->Init() Funktion.
	 *
	 * @param Bool $init
	 */
	public function ResetEnv($init = false) {

		if ($init) {
			RDD::Log('ResetEnv called with init',TRACE,1050);
		} else {
			RDD::Log('ResetEnv called',TRACE,1050);
		}

		$this->Hook('PreResetEnv',$init);

		$this->POST = $_POST;
		$this->GET = $_GET;
		$this->COOKIE = $_COOKIE;
		$this->FILES = $_FILES;

		if (isset($this->GET['killsession'])) {
			RDD::Log('Found killsession!',TRACE,1500);
			$this->SESSION = Array();
		} else if (!isset($_SESSION)) {
			RDD::Log('$_SESSION is not set!',TRACE,1500);
			$this->SESSION = Array();
		} else {
			$this->SESSION = $_SESSION;
		}
		
		$this->Hook('PostResetEnv',$init);
		return;

	}
	
	/**
	 * Ruft nur den Hook "ResetModules" auf. Wird genutzt um Module
	 * wieder auf Urzustand zu setzen um mehrere Durchlüufe in einem
	 * PHP Durchlauf zu machen. Dies wird bisher in Kombination mit
	 * der Funktion $RD->StartPageContentOnly() vom "Page" Modul
	 * genutzt.
	 *
	 */
	public function ResetModules() {

		$this->Hook('ResetModules');
		return;
		
	}
	
	/**
	 * Die Variable $ErrorCount wird hier hochgezühlt und die Error
	 * Hooks "PreError" und "PostError" aufgerufen. Bei maximaler 
	 * Versuchanzahl wird die Funktion RD_FATAL() aufgerufen oder ein 
	 * die() aufgerufen.
	 * 
	 */
	public function Error() {
		
		$this->Hook('PreError',$this->ErrorCount);
		$this->ErrorCount++;
		$this->Hook('PostError',$this->ErrorCount);
		
		if ($this->ErrorCount < $this->ErrorCountMax) {
			if ($this->TryAgain) {
				$this->Start();
				return;
			}
		} elseif (function_exists('RD_FATAL')) {
			RD_FATAL();
			exit(0);
		} else {
			throw new RDE('RD: maximum error count reached');
		}
		
	}
	
	/**
	 * Hier wird die $_SESSION und der $_COOKIE bei erfolgreichem
	 * Ablauf abgespeichert. Auch wird der Hook "Save" aufgerufen.
	 * 
	 */
	public function Save() {

		$this->Hook('Save');
		
		if (!self::$DisableSession) {
			$_SESSION = $this->SESSION;
		}
		
		$_COOKIE = $this->COOKIE;
		
		return;

	}

	public function AddAllRoot() {
		static $Done;
		if (!$Done) {
			$this->AllRoot();
			$Done = true;
		}
		return $this;
	}
	
	/**
	 * Neues Verzeichniss zu den "Roots" hinzufügen. Liefert die
	 * Anzahl der "Roots" zurück
	 *
	 * @param String $dir
	 * @return Integer
	 */
	public function AddRoot($Root,$Key = NULL) {

		if (is_dir($Root)) {
			$Root = realpath($Root);
			RDD::Log('Adding root '.$Root,TRACE,1001);
			$Key === NULL ? $this->Roots[] = $Root : $this->Roots[$Key] = $Root;
			return $this;
		} else {
			if ($Key === NULL) {
				throw new RDE('RD: root doesnt exist or is not a directory');
			} else {
				throw new RDE('RD: root doesnt exist or is not a directory (key: '.$Key.')');				
			}
		}
		
	}
	
	public function RemoveRoot($Key) {
		if (isset($this->Roots[$Key])) {
			unset($this->Roots[$Key]);
		}
		return $this;
	}

	/**
	 * Die "Roots" zurückgeben.
	 *
	 * @return Array
	 */
	public function GetRoots() {
		return $this->Roots;
	}

	public function GetLastRoot() {
		$Reverse = array_values(array_reverse($this->Roots));
		return $Reverse[0];
	}

	public function GetRootsMd5() {
		$MD5String = "";
		foreach($this->Roots as $Root) {
			$MD5String .= $Root;
		}
		return md5($MD5String);
	}

	/**
	 * "Cache" Verzeichniss setzen.
	 *
	 * @param String $dir
	 */
	public function SetCacheDir($dir) {
		
		RDD::Log('Setting cache to '.$dir,TRACE,1001);
		
		if (!is_dir($dir)) {
			throw new RDE('RD: cache directory is not a dir');
		} elseif (!is_writeable($dir)) {
			throw new RDE('RD: cache directory is not writeable');
		}
		
		$this->CacheDir = $dir;		
		return;
		
	}

	/**
	 * "Cache" Verzeichniss holen
	 *
	 * @return String
	 */
	public function GetCacheDir() {
		return $this->CacheDir;
	}
	
	/**
	 * Dieser "Allround" Getter holt eine Variable aus allen Umgebungscontainer
	 * die zur Verfügung stehen. Zuerst POST, dann GET, dann SESSION, dann der
	 * COOKIE.
	 *
	 * @param String $var
	 * @param Mixed $fallback
	 * @return Mixed
	 */
	public function Get($key,$fallback = false) {
		if ($key === NULL) {
			throw new RDE('RD: TODO NULL key not supported on Get() so far');
		}
		if (isset($this->POST[$key])) {
			return $this->POST[$key];
		} elseif (isset($this->GET[$key])) {
			return $this->GET[$key];
		} elseif (isset($this->SESSION[$key])) {
			return $this->SESSION[$key];
		} elseif (isset($this->COOKIE[$key])) {
			return $this->COOKIE[$key];
		}
		RDD::Log('Cant find key '.$key,TRACE,1011);
		return $fallback;
	}

	public function GetRequest($key,$fallback = false) {
		if ($key === NULL) {
			throw new RDE('RD: TODO NULL key not supported on GetRequest() so far');
		}
		if (isset($this->POST[$key])) {
			return $this->POST[$key];
		} elseif (isset($this->GET[$key])) {
			return $this->GET[$key];
		}
		RDD::Log('Cant find key '.$key,TRACE,1011);
		return $fallback;
	}

	public function GetFiles($key = null, $fallback = false) {
		if ($key === NULL) {
			return $this->FILES;
		} elseif (isset($this->FILES[$key])) {
			return $this->FILES[$key];
		} 
		
		RDD::Log('Cant find var '.$key,TRACE,1011);
		return $fallback;
	}
	
	public function GetSession($key = NULL,$fallback = false) {
		if ($key === NULL) {
			return $this->SESSION;
		} elseif (isset($this->SESSION[$key])) {
			return $this->SESSION[$key];
		}
		RDD::Log('Cant find var '.$key,TRACE,1011);
		return $fallback;
	}

	public function SetSession($key,$value) {
		if ($key === NULL) {
			return $this->SESSION = $value;
		}
		return $this->SESSION[$key] = $value;
	}

	public function UnsetSession($key) {
		if (isset($this->SESSION[$key])) {
			unset($this->SESSION[$key]);
			return true;
		}
		return false;
	}

	public function GetCookie($key = NULL,$fallback = false) {
		if ($key === NULL) {
			return $this->COOKIE;
		} elseif (isset($this->COOKIE[$key])) {
			return $this->COOKIE[$key];
		}
		RDD::Log('Cant find var '.$key,TRACE,1011);
		return $fallback;
	}

	public function SetCookie($key,$value) {
		if ($key === NULL) {
			return $this->COOKIE = $value;
		}
		return $this->COOKIE[$key] = $value;
	}
	
	public function UnsetCookie($key) {
		if (isset($this->COOKIE[$key])) {
			unset($this->COOKIE[$key]);
			return true;
		}
		return false;
	}

	public function GetGet($key = NULL,$fallback = false) {
		if ($key === NULL) {
			return $this->GET;
		} elseif (isset($this->GET[$key])) {
			return $this->GET[$key];
		}
		RDD::Log('Cant find key '.$key,TRACE,1011);
		return $fallback;
	}

	public function SetGet($key,$value) {
		if ($key === NULL) {
			return $this->GET = $value;
		}
		return $this->GET[$key] = $value;
	}
	
	public function UnsetGet($key) {
		if (isset($this->GET[$key])) {
			unset($this->GET[$key]);
			return true;
		}
		return false;
	}

	public function GetPost($var = NULL,$fallback = false) {
		if ($var === NULL) {
			return $this->POST;
		} elseif (isset($this->POST[$var])) {
			return $this->POST[$var];
		}
		RDD::Log('Cant find var '.$var,TRACE,1011);
		return $fallback;
	}

	public function IssetGet($var = NULL) {
		if (isset($this->GET[$var])) {
			return true;
		}
		return false;
	}

	public function IssetPost($var = NULL) {
		if (isset($this->POST[$var])) {
			return true;
		}
		return false;
	}

	public function IssetRequest($key) {
		if ($key === NULL) {
			throw new RDE('RD: TODO NULL key not supported on IssetRequest() so far');
		}
		if (isset($this->POST[$key])) {
			return true;
		} elseif (isset($this->GET[$key])) {
			return true;
		}
		RDD::Log('Cant find key '.$key,TRACE,1011);
		return false;
	}

	public function SetPost($key,$value) {
		if ($key === NULL) {
			return $this->POST = $value;
		}
		return $this->POST[$key] = $value;
	}
	
	public function UnsetPost($key) {
		if (isset($this->POST[$key])) {
			unset($this->POST[$key]);
			return true;
		}
		return false;
	}
	
	public function HasFunction($name) {
		if ($this->MethodExists($name)) {
			return 'main';
		} elseif (array_key_exists($name,$this->ModulesCoreFunctions)) {
			return $this->ModulesCoreFunctions[$name];
		} elseif (array_key_exists($name,$this->ModulesFunctions)) {
			return $this->ModulesFunctions[$name];
		}
		return false;
	}

	/* DEPRECATED */
	public function DeSet($var) {
		return $this->UnsetAll($var);
	}
	
	/**
	 * Dieser "Allround" UnSetter sucht die Variable in POST, GET, SESSION und COOKIE
	 * und lüscht diese daraus.
	 *
	 * @param String $var
	 */
	public function UnsetAll($var) {
		if (isset($this->POST[$var])) {
			unset($this->POST[$var]);
		}
		if (isset($this->GET[$var])) {
			unset($this->GET[$var]);
		}
		if (isset($this->SESSION[$var])) {
			unset($this->SESSION[$var]);
		}
		if (isset($this->COOKIE[$var])) {
			unset($this->COOKIE[$var]);
		}
		return;
	}
	
	/**
	 * Durchsucht alle "Roots" nach dem Dateinamen $file, und gibt dann den
	 * vollstündigen absoluten Dateinamen des ersten Treffers in den Verzeichnissen
	 * zurück. Falls die Datei nicht gefunden wird, gibt die Funktion false zurück
	 *
	 * TODO: Auslagerung des $FileCache in das "Cache" Verzeichniss
	 * 
	 * @param String $file
	 * @return String/Bool
	 */
	public function File($file) {
		
		RDD::Log('Searching file '.$file,TRACE,1010);

		if (!isset($this->FileCache[$file])) {
			foreach(array_reverse($this->Roots) as $Root) {
				RDD::Log('Trying '.$Root.DIR_SEP.$file,TRACE,1010);
				if (file_exists($Root.DIR_SEP.$file)) {
					RDD::Log('Found file in '.$Root,TRACE,1010);
					$this->FileCache[$file] = $Root.DIR_SEP.$file;
					return $this->FileCache[$file];
				}
			}
		} else {
			return $this->FileCache[$file];
		}
		
		RDD::Log('File '.$file.' Not Found',WARN);

		return false;
		
	}
	
	public function IncludeFile($File) {
		if ($Filename = $this->File($File)) {
			return include($Filename);
		}

		RDD::Log('File '.$File.' Not Found',WARN);
		
		return false;
	}

	public function RequireOnceFile($File) {
		if ($Filename = $this->File($File)) {
			return require_once($Filename);
		}

		throw new RDE('RD->RequireOnceFile: Cant find File "'.$File.'"');
	}

	public function RequireFile($File) {
		if ($Return = $this->File($File)) {
			return $Return;
		}
		throw new RDE('RD->RequireFile: Cant find File "'.$File.'"');
	}

	/**
	 * Durchsucht alle "Roots" nach einem bestimmten Verzeichniss $dir, und gibt
	 * ein Array zurück mit allen absoluten Verzeichnissnamen die auf $dir passen.
	 * Falls keine gefunden werden gibt er ein leeres Array zurück.
	 *
	 * @param String $dir
	 * @return Array
	 */
	public function Dirs($dir) {
		
		RDD::Log('Searching dir '.$dir,TRACE,1010);

		if (!isset($this->DirsCache[$dir])) {
			$Dirs = Array();		
			foreach(array_reverse($this->Roots) as $Root) {
				if (file_exists($Root.DIR_SEP.$dir) && dir($Root.DIR_SEP.$dir)) {
					RDD::Log('Found dir in '.$Root,TRACE,1010);
					$Dirs[] = $Root.DIR_SEP.$dir;
				}
			}
			$this->DirsCache[$dir] = $Dirs;
			return $this->DirsCache[$dir];
		} else {
			return $this->DirsCache[$dir];
		}

	}

	public function Dir($dir) {
		
		RDD::Log('Searching dir '.$dir,TRACE,1010);

		if (!isset($this->DirCache[$dir])) {
			foreach(array_reverse($this->Roots) as $Root) {
				RDD::Log('Trying '.$Root.DIR_SEP.$dir,TRACE,1010);
				if (is_dir($Root.DIR_SEP.$dir)) {
					RDD::Log('Found dir in '.$Root,TRACE,1010);
					$this->DirCache[$dir] = $Root.DIR_SEP.$dir;
					return $this->DirCache[$dir];
				}
			}
		} else {
			return $this->DirCache[$dir];
		}

		RDD::Log('Directory '.$dir.' Not Found',WARN);

		return false;
		
	}

	public function Lib($lib) {

		RDD::Log('Searching lib '.$lib,TRACE,1010);

		return $this->Dir('libs'.DIR_SEP.$lib);

	}

	public function AddIncludePath() {

		foreach (func_get_args() AS $path)
		{
			RDD::Log('Adding '.$path.' to include_path',TRACE,1010);

			if (!file_exists($path) OR (file_exists($path) && filetype($path) !== 'dir'))
			{
				throw new RDE($path.' is a file or doesnt exist, so cant be added to include path!');
			}

			$paths = explode(PATH_SEP, get_include_path());

			if (array_search($path, $paths) === false) array_push($paths, $path);

			set_include_path(implode(PATH_SEP, $paths));
		}

	}

	public function RemoveIncludePath() {

		foreach (func_get_args() AS $path)
		{
			RDD::Log('Remove '.$path.' from include_path',TRACE,1010);

			$paths = explode(PATH_SEP, get_include_path());
       
			if (($k = array_search($path, $paths)) !== false)
				unset($paths[$k]);
			else
				continue;
       
			set_include_path(implode(PATH_SEP, $paths));
		}

	}
	
	public function GetConfig($Name) {
		if ($File = $this->File('configs'.DIR_SEP.$Name.'.php')) {
			return $this->ReadFileVarDump($File);
		}
		return false;
	}

	public function SetFileVarDump($var,$value) {
		return $this->TellEvilMonkey($var,$value);
	}
	
	public function TellEvilMonkey($Var,$Value) {
		$FileName = $this->GetCacheDir().DIR_SEP.get_class($this).'-'.$Var.'.php';
		return $this->WriteFileVarDump($FileName,$Value);
	}
	
	public function GetFileVarDump($Var) {
		return $this->AskEvilMonkey($Var);
	}
	
	public function AskEvilMonkey($Var) {
		$FileName = $this->GetCacheDir().DIR_SEP.get_class($this).'-'.$Var.'.php';
		return $this->ReadFileVarDump($FileName.'.php');
	}
	
	public function WriteFileVarDump($Filename,$Value) {
		return file_put_contents($Filename,'<? $return = '.var_export($Value,true).';');
	}

	public function ReadFileVarDump($Filename) {
		if (is_file($Filename)) {
			include($Filename);
			if (isset($return)) {
				return $return;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	protected function LoadCoreCache() {
		$this->FileCache = $this->GetFileVarDump('RDFileCache');
		$this->DirsCache = $this->GetFileVarDump('RDDirsCache');
		return;
	}
	
	/**
	 * Laden aller PHP Dateien aus allen "functions" Verzeichnissen in allen
	 * "Roots". Hier werden regulür auch z.b. Validatoren für das Form Modul
	 * abgelegt.
	 *
	 */
	protected function LoadFunctions() {
		
		static $LoadedFunctions = false;
		
		RDD::Log('Loading Functions',TRACE,1010);
		
		if ($LoadedFunctions === false) {
			$LoadedFunctions = Array();
			foreach(array_reverse($this->Roots) as $Root) {			
				$dir = $Root.DIR_SEP.'functions';
				if (is_dir($dir)) {
					RDD::Log('Found functions in '.$Root,TRACE,1011);
					if ($handle = opendir($dir)) {
						while (false !== ($file = readdir($handle))) {
							if (!in_array($file,$LoadedFunctions)) {
								$LoadedFunctions[] = $file;
								if (is_file($dir.DIR_SEP.$file) && substr($file, -4) == '.php') {
									require_once($dir.DIR_SEP.$file);
								}
							}
						}
						closedir($handle);
					}
				}
			}
		}
		
	}
	
	/**
	 * Instanziieren der Module des Frameworks. Befindet sich ein
	 * Modul in mehreren "Roots" wird grundsützlich, wie bei allen Dateien
	 * immer das erste gefundene genommen.
	 *
	 */
	protected function LoadModules() {

		$Cached = false;

		if ($this->CoreCache) {
			RDD::Log('Checking Cache for Modules',TRACE,1010);
			if ($Cache = $this->GetFileVarDump('RDModuleCache')) {
				$this->ModulesFunctions = $Cache['ModulesFunctions'];
				$this->ModulesCoreFunctions = $Cache['ModulesCoreFunctions'];
				$this->ModulesFunctionSpaces = $Cache['ModulesFunctionSpaces'];
				$this->HooksModules = $Cache['HooksModules'];
				$this->LoadedModules = $modules = $Cache['LoadModules_modules'];
				$Cached = true;
			}
		}

		RDD::Log('Loading Modules',TRACE,1010);

		if (!$Cached) {

			$modules_load = Array();

			foreach($this->Roots as $Root) {
				$dir = $Root.DIR_SEP.'modules';
				if (is_dir($dir)) {
					RDD::Log('Found modules in '.$Root,TRACE,1011);
					if ($handle = opendir($dir)) {
						while (false !== ($file = readdir($handle))) {
							if (is_dir($dir.DIR_SEP.$file) && $file != "." && $file != ".." && $file != ".svn" && $file != 'CVS' && $file != '.git') {

								if (file_exists($dir.DIR_SEP.$file.DIR_SEP.$file.'.php')) {
									$modules_load[$file] = $dir.DIR_SEP.$file.DIR_SEP.$file.'.php';
								} else {
									RDD::Log('Module '.$dir.' in '.$Root.' got no php',WARN);
								}

							}
						}
						closedir($handle);
					}
				}
			}

			$modules = Array();
			
			ksort($modules_load);

			foreach($modules_load as $module_base => $module_file) {
				if (!in_array($module_base,$this->DontLoadModules) && 
					($this->LoadModules === true || (is_array($this->LoadModules) && in_array($module_base,$this->LoadModules)))) {
					$modules[$module_base] = $module_file;
				} else {
					RDD::Log('Not loading '.$module_base.', not in modules list',TRACE,1010);
				}
			}

		}
		
		foreach($modules as $module_base => $module_file) {
			RDD::Log('Loading '.$module_file.' as '.$module_base,TRACE,1010);
			require_once($module_file);
			$classname = 'RDM_'.ucfirst($module_base);
			$this->Modules[$module_base] = new $classname($this);
		}

		foreach($modules as $module_base => $module_file) {
			$classname = 'RDM_'.ucfirst($module_base);
			RDD::Log('PreSetup '.$classname,TRACE,1010);

			if (!$Cached) {

				$ref = new ReflectionClass($classname);
				// Get the methods from the class
				$methods =$ref->getMethods();
		
				foreach($methods as $method) {

					foreach($method as $methodpart => $methodvalue){

						if($methodpart == 'name') {

							if(stripos($methodvalue,'Hook')===0) {

								if(!isset($this->HooksModules[$methodvalue])) {
									$this->HooksModules[$methodvalue] = Array();
								}
							
								$this->HooksModules[$methodvalue][] = $module_base;

							}
						}
					}
				}
				
				try {
					
					$functions = $ref->getStaticPropertyValue('RD_CoreFunctions');
					foreach($functions as $function) {
						if (isset($this->ModulesCoreFunctions[$function])) {
							throw new RDE('RD: module core function collision on core function '.$function.' with '.$this->ModulesFunctions[$function].' and '.$module_base);
						}
						$this->ModulesCoreFunctions[$function] = $module_base;
					}

				} catch (Exception $e) {
					RDD::Log('RD: exception on '.$module_base.' while trying to fetch core functions',TRACE,1001);
				}

			}

			$this->ModuleSetupExecute($module_base,'PreSetup');
		}

		foreach($modules as $module_base => $module_file) {
			if (!$Cached) {
				// MAGIC!!!!! also known as TODO
				$classname = 'RDM_'.ucfirst($module_base);
				$ref = new ReflectionClass($classname);
				$methods = $ref->getMethods();
				$functions = $ref->getStaticPropertyValue('RD_Functions');
				foreach($functions as $function) {
					if (strpos($function,'*') === strlen($function)-1) {
						$FunctionSpace = substr($function,0,strlen($function)-1);
						if (isset($this->ModulesFunctionSpaces[$FunctionSpace])) {
							throw new RDE('RD: module function space collision on function space '.$FunctionSpace.' with '.$this->ModulesFunctionSpaces[$FunctionSpace].' and '.$module_base);
						}
						$this->ModulesFunctionSpaces[$FunctionSpace] = $module_base;
					} else {
						if (isset($this->ModulesFunctions[$function])) {
							throw new RDE('RD: module function collision on function '.$function.' with '.$this->ModulesFunctions[$function].' and '.$module_base);
						} elseif (isset($this->ModulesCoreFunctions[$function])) {
							throw new RDE('RD: module core function collision on function '.$function.' with core function of '.$this->ModulesCoreFunctions[$function].' and '.$module_base);
						}
						$this->ModulesFunctions[$function] = $module_base;
					}
				}
			}

			$this->ModuleSetupExecute($module_base,'Setup');
		}

		foreach($modules as $module_base => $module_file) {
			$this->ModuleSetupExecute($module_base,'PostSetup');
		}
		
		$this->LoadedModules = $modules;

		RDD::Log('Modules information gathered',TRACE,1001);

		if ($this->CoreCache && !$Cached) {
			RDD::Log('Setting up Cache for Modules',TRACE,1010);
			$Cache = Array();
			$Cache['ModulesFunctions']= $this->ModulesFunctions;
			$Cache['ModulesCoreFunctions'] = $this->ModulesCoreFunctions;
			$Cache['ModulesFunctionSpaces'] = $this->ModulesFunctionSpaces;
			$Cache['HooksModules'] = $this->HooksModules;
			$Cache['LoadedModules'] = $this->LoadedModules;
			$this->SetFileVarDump('RDModuleCache',$Cache);
		}

		return;

	}
	
	public function GetLoadedModules() {
		return $this->LoadedModules;
	}
	
	public function GetCoreCache() {
		$CoreCache = Array();
		$CoreCache['ModulesFunctions']= $this->ModulesFunctions;
		$CoreCache['ModulesCoreFunctions'] = $this->ModulesCoreFunctions;
		$CoreCache['ModulesFunctionSpaces'] = $this->ModulesFunctionSpaces;
		$CoreCache['HooksModules'] = $this->HooksModules;
		ksort($this->FileCache,SORT_STRING);
		$CoreCache['RDFileCache'] = $this->FileCache;
		$CoreCache['RDDirsCache'] = $this->DirsCache;
		return $CoreCache;
	}

	protected function ModuleSetupExecute($module,$command) {
		
		RDD::Log('Modules Setup Execute Start '.$module,TRACE,1001);

		$setup_method = $command.ucfirst($module);
		
		if ($this->MethodExists($setup_method)) {
			$this->$setup_method();
		}

		foreach($this->Modules as $OtherModuleName => $OtherModule) {
			if ($OtherModuleName != $module) {
				if ($this->Modules[$OtherModuleName]->MethodExists($setup_method)) {
					$this->Modules[$OtherModuleName]->$setup_method();
				}
			}
		}
		
		if ($this->Modules[$module]->MethodExists($command)) {
			$this->Modules[$module]->$command();
		}
		
		RDD::Log('Modules Setup Execute End '.$module,TRACE,1001);

		return;
		
	}
	
	/**
	 * Starting Hooks
	 *
	 * @param String $hook
	 * @param Mixed $data
	 */	
	public function Hook($hook,$data = NULL) {

		RDD::Log('Activating Hook '.$hook,TRACE,1010);

		$hook_function = 'Hook'.$hook;

		if ($this->MethodExists($hook_function)) {
			RDC::call_object($this,$hook_function,Array($data));
		}
		
		if (!isset($this->HooksModules['Hook'.$hook])) {
			return;
		}

		foreach($this->HooksModules[$hook_function] as $hookkey => $modulename) {

			RDD::Log('Looking for hook function '.$hook_function.' at module '.$modulename,TRACE,1010);

			if ($data === NULL) {
				$this->Modules[$modulename]->$hook_function();
			} else {
				$this->Modules[$modulename]->$hook_function($data);
			}
		}

		return;

	}

	// backward compatibility
	public function ClassRequire($ClassName) {
		return self::$Self->RequireClass($ClassName);
	}
	
	public function ClassExist($ClassName) {
		return self::ExistClass($ClassName);
	}

	public function ExistClass($ClassName) {
		if (class_exists($ClassName, false) || interface_exists($ClassName, false)) {
			return true;
		} elseif ($ClassFile = $this->File('classes'.DIR_SEP.str_replace('_',DIR_SEP,$ClassName).'.php')) {
			return $ClassFile;
		} elseif ($ClassFile = $this->File('classes'.DIR_SEP.$ClassName.'.php')) {
			return $ClassFile;
		}
		return false;		
	}

	public function RequireClass($ClassName) {
		$Exist = RD::ExistClass($ClassName);
		if ($Exist === true) {
			return;
		} elseif ($Exist) {
			require_once($Exist);
		}
		if (!class_exists($ClassName, false) && !interface_exists($ClassName, false)) {
			 $File = $Line = 'unknown';
			$BackTrace = reset(debug_backtrace());

			if (isset($BackTrace['file']) && !empty($BackTrace['file'])) {
				$File = $BackTrace['file'];
			}

			if (isset($BackTrace['line']) && !empty($BackTrace['line'])) {
				$Line = $BackTrace['line'];
			}

			$Message   = 'Cant requirre the Class or Interface "';
			$Message  .= $ClassName . '" You required in file: "';
			$Message   .= $File . '" on line: "' . $Line . '"';
			throw new RDE($Message);
		}
		
		return;
	}

	public function __call($name,$arguments) {
		RDD::Log('RD::__call('.$name.',Array('.count($arguments).'))',TRACE,1001);
		if ($this->MethodExists('_'.$name)) {
			return RDC::call_object($this,'_'.$name,$arguments);
		} elseif (array_key_exists($name,$this->ModulesCoreFunctions)) {
			return RDC::call_object($this->Modules[$this->ModulesCoreFunctions[$name]],$name,$arguments);
		} elseif (array_key_exists($name,$this->ModulesFunctions)) {
			return RDC::call_object($this->Modules[$this->ModulesFunctions[$name]],$name,$arguments);
		} else {
			foreach($this->ModulesFunctionSpaces as $FunctionSpace => $Module) {
				if (substr($name,0,strlen($FunctionSpace)) == $FunctionSpace) {
					if ($this->Modules[$Module]->MethodExists($name)) {
						return RDC::call_object($this->Modules[$Module],$name,$arguments);						
					} else {
						throw new RDE('RD: module '.$Module.' is unable to handle this method');
					}
				}
			}
			if (empty($this->LoadedModules)) {
				throw new RDE('RD: framework cant find function '.$name.' (probably cause no modules are loaded)');
			} else {
				throw new RDE('RD: framework cant find function '.$name);
			}
		}
	}
	
	public function __get($var) {
		throw new RDE('RD: framework doesnt support overloading properties right now (Your requested property was: "'.$var.'")');
	}

	public function __set($var,$value) {
		throw new RDE('RD: framework doesnt support overloading properties right now (Your requested property was: "'.$var.'")');
	}

	public function __destruct() {

		if ($this->CoreCache) {
			$this->SetFileVarDump('RDFileCache',$this->FileCache);
			$this->SetFileVarDump('RDDirsCache',$this->DirsCache);
		}
		
		RDD::Log('Destructing RD - bye bye',INFO);

		return;
	}
		
}

if (!function_exists('RD_Cleanup')) {
	function RD_Cleanup() {
		if (isset(RD::$Self)) {
			return RD::$Self->Cleanup();		
		}
	}
}

if (!function_exists('RD_Shutdown_Function')) {
	function RD_Shutdown_Function() {
	    if (function_exists('RD_Cleanup')) { RD_Cleanup(); }
	}
	register_shutdown_function('RD_Shutdown_Function');
}
