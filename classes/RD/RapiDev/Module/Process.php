<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Process Management Class
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

class RD_RapiDev_Module_Process extends RD_Module {

	protected $CPUCount = 4;
	
	public static $RD_Functions = Array(
									'ProcessStatus',
									'ProcessTerminate',
									'ProcessOpen',
									'ProcessExecutor',
									'ProcessStdOut',
									'ProcessStdIn',
									'ProcessStdErr',
									'GetProcessPhpBin',
									'SetProcessPhpBin',
									'GetProcessPhpInclude',
									'SetProcessPhpInclude',
								  );

	public static $RD_GetterSetter = Array(
									'CPUCount',
									'ProcessPhpBin',
									'ProcessPhpInclude',
									'ProcessPhpExtension',
								  );

	public static $RD_Depencies = Array();
	
	const OUT_IGNORE = 0;
	const OUT_RETURN = 1;
	const OUT_ECHO = 2;
	
	protected $ProcessPhpBin;
	protected $ProcessPhpInclude;
	protected $ProcessPhpExtension = 'php';
	
	protected $Processes;
	
	public static $ExecutorWaitingTime = 3;
	public $NextProcessKey = 0;
	
	public function Setup() {
		if (!$this->IsCLI()) { return; }
		if (!isset($this->ProcessPhpBin)) {
			if (isset($_SERVER['_'])) {
				$this->SetProcessPhpBin($_SERVER['_']);
			} else {
				$this->SetProcessPhpBin('php');
			}
		}
		if (!isset($this->ProcessPhpInclude)) {
			$this->SetProcessPhpInclude(ini_get('include_path'));
		}
		return;
	}
	
	public function GetProcessPhpBin() {
		if (!$this->IsCLI()) { return false; }
		return $this->ProcessPhpBin;
	}
	
	public function SetProcessPhpBin($ProcessPhpBin) {
		if (!$this->IsCLI()) { return $this->main; }
		/* if (!file_exists($ProcessPhpBin)) {
			throw new RDE('RDM_Process: $ProcessPhpBin doesnt exist');
		}
		if (!is_executable($ProcessPhpBin)) {
			throw new RDE('RDM_Process: $ProcessPhpBin is not executable');
		} */
		$this->ProcessPhpBin = escapeshellcmd($ProcessPhpBin);
		return $this->main;
	}

	public function GetProcessPhpInclude() {
		if (!$this->IsCLI()) { return false; }
		return $this->ProcessPhpInclude;
	}
	
	public function SetProcessPhpInclude($ProcessPhpInclude) {
		if (!$this->IsCLI()) { return $this->main; }
		$this->ProcessPhpInclude = escapeshellarg(strtr($ProcessPhpInclude,'"',''));
		return $this->main;
	}

	// CHANGING OF $OutputHandling NOT ADVICED!!!! BUGGY!!!!
	public function ProcessExecutor($CommandList,$OutputHandling = self::OUT_IGNORE,&$Error = NULL) {
		if (!$this->IsCLI()) { return $this->main; }
		$Count = count($CommandList);
		RDD::Log('Executing '.$Count.' Commands with max. '.$this->CPUCount.' running.',INFO);
		$Running = Array();
		$End = false;
		$NothingLeft = false;
		$CommandList = array_reverse($CommandList);
		if ($OutputHandling === self::OUT_RETURN) {
			$Return = Array();
		}
		while(!$End) {
			foreach($Running as $Key => $ProcessKey) {
				if ($OutputHandling === self::OUT_ECHO) {
					$StdOut = $this->ProcessStdOut($ProcessKey);
					echo $StdOut;
					if (strlen($StdOut) > 0) {
						echo "\n";
					}
				} elseif ($OutputHandling === self::OUT_RETURN) {
					if (!isset($Return[$Key])) { $Return[$Key] = ''; }
					$Return[$Key] .= $this->ProcessStdOut($ProcessKey);
				}
				if ($Error !== NULL) {
					if (is_array($Error)) {
						if (!isset($Error[$Key])) { $Error[$Key] = ''; }
						$Error[$Key] .= $this->ProcessStdErr($ProcessKey);
					} elseif (is_string($Error)) {
						$Error .= $this->ProcessStdErr($ProcessKey);
					}
				}
			}
			if (count($Running) < $this->CPUCount && !$NothingLeft) {
				$NextCommand = array_pop($CommandList);
				if ($NextCommand !== NULL) {
					$Running[] = $this->ProcessOpen($NextCommand);
				} else {
					$NothingLeft = true;
				}
			} else {
				sleep(self::$ExecutorWaitingTime);
				RDD::Log('Running '.count($Running));
				foreach($Running as $Key => $ProcessKey) {
					$Status = $this->ProcessStatus($ProcessKey);
					if (!$Status['running']) {
						unset($Running[$Key]);
					}
				}
				if ($NothingLeft && count($Running) == 0) {
					$End = true;
				}
			}
		}
		if (isset($Return)) {
			return $Return;
		} else {
			return $this->main;
		}
	}
	
	public function ProcessStatus($ProcessKey) {

		if (isset($this->Processes[$ProcessKey])) {
			return proc_get_status($this->Processes[$ProcessKey]['Resource']);
		}
		return Array();

	}
	
	public function ProcessTerminate($ProcessKey,$Signal = NULL) {

		if (isset($this->Processes[$ProcessKey])) {
			if ($Signal === NULL) {
				return proc_terminate($this->Processes[$ProcessKey]['Resource']);				
			} else {
				return proc_terminate($this->Processes[$ProcessKey]['Resource'],$Signal);
			}
		}
		return false;

	}
	
	public function ProcessStdIn($ProcessKey,$Data) {

		if (isset($this->Processes[$ProcessKey])) {
			fwrite($this->Processes[$ProcessKey]['StdIn'], $Data);
		}

		return $this->main;
	}

	public function ProcessStdOut($ProcessKey) {

		if (isset($this->Processes[$ProcessKey])) {
			return stream_get_contents($this->Processes[$ProcessKey]['StdOut']);
		}

		return false;
	}

	public function ProcessStdErr($ProcessKey) {

		if (isset($this->Processes[$ProcessKey])) {
			return stream_get_contents($this->Processes[$ProcessKey]['StdErr']);
		}

		return false;
	}

	public function ProcessOpen($Command, $Cwd = NULL, $Environment = NULL) {

		if (!$this->IsCLI()) { return false; }
		
		$CommandArgs = explode(' ',$Command);
		
		if (substr($CommandArgs[0],strlen($CommandArgs[0])-strlen($this->ProcessPhpExtension)) == $this->ProcessPhpExtension) {
			$Command = $this->ProcessPhpBin.' -d include_path=\"'.$this->ProcessPhpInclude.'\" '.$Command;
		}

		$DescriptorSpec = Array(
         /*   0 => Array('pipe', 'r'),
            1 => Array('pipe', 'w'),
            2 => Array('pipe', 'w') */
        );

        if ($Environment === NULL) {
        	$Environment = $_ENV;
        }

        $Resource = proc_open($Command, $DescriptorSpec, $Pipes, $Cwd, $Environment);

        $ProcessKey = $this->NextProcessKey;
        $this->NextProcessKey++;

        $this->Processes[$ProcessKey] = Array(
        									'Resource' => $Resource,
        								/*	'StdIn' => $Pipes[0],
        									'StdOut' => $Pipes[1],
        									'StdErr' => $Pipes[2] */
        								);

        return $ProcessKey;

	}

}

