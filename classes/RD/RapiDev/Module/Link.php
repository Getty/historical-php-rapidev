<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Link Module
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
 * @author	   Christoph Friedrich <christoph-friedrich@gmx.net>
 * @copyright  2007 Torsten Raudssus
 * @license    GPL-2
 *
 */

class RD_RapiDev_Module_Link extends RDM {

	public static $RD_Functions = Array(
											'Link',
											'SetLinkConfig',
											'GetLinkConfig',
											'SetLink',
											'GetLink',
											'SetLinkResult',
											'GetLinkResult',
										);

	public static $RD_Depencies = Array();
	
	public $LinkConfig;
	public $Link;
	public $LinkResult;

	public function urlEncode($String) {
		if (is_object($String) && is_a($String,'RDM_Link_NoUrlencode')) {
			return strval($String);
		}
		return rawurlencode($String);
	}
	
	public function GetLinkConfig() {
		return $this->LinkConfig;
	}
	
	public function SetLinkConfig($LinkConfig) {
		$this->LinkConfig = $LinkConfig;
		return $this;
	}
	
	public function GetLinkResult() {
		return $this->LinkResult;
	}
	
	public function SetLinkResult($LinkConfig) {
		$this->LinkResult = $LinkConfig;
		return $this;
	}

	public function GetLink() {
		return $this->Link;
	}
	
	public function SetLink($Link) {
		$this->Link = $Link;
		return $this;
	}
	
	public function Link($LinkConfig = Array()) {
		
		$this->Hook('LinkPreInit');

		$this->LinkConfig = $LinkConfig;

		$this->Hook('LinkInit');
		
		$js = false;
		$url = false;
		$ignore_entities = false;
		$define_anchor = false;
		$random_chunk = false;
		$use_query = true;
		$prefer_query = false;
		$nopage = false;

		if (isset($this->LinkConfig['js'])) {
			$js = (!$this->LinkConfig['js'] == '0');
			unset($this->LinkConfig['js']);
		}

		if (isset($this->LinkConfig['alt'])) {
			$alt = $this->LinkConfig['alt'];
			unset($this->LinkConfig['alt']);
		} else {
			$alt = '';
		}

		if (isset($this->LinkConfig['nopage'])) {
			$nopage = (!$this->LinkConfig['nopage'] == '0');
			unset($this->LinkConfig['nopage']);
		}

		if (isset($this->LinkConfig['defanchor'])) {
			$define_anchor = (!$this->LinkConfig['defanchor'] == '0');
			unset($this->LinkConfig['defanchor']);
		}

		if (isset($this->LinkConfig['random_chunk'])) {
			$random_chunk = (!$this->LinkConfig['random_chunk'] == '0');
			unset($this->LinkConfig['random_chunk']);
		}

		if (isset($this->LinkConfig['use_query'])) {
			$use_query = (!$this->LinkConfig['use_query'] == '0');
			unset($this->LinkConfig['use_query']);
		}

		if (isset($this->LinkConfig['prefer_query'])) {
			$prefer_query = (!$this->LinkConfig['prefer_query'] == '0');
			unset($this->LinkConfig['prefer_query']);
		}

		if (isset($this->LinkConfig['ignore_entities'])) {
			$ignore_entities = (!$this->LinkConfig['ignore_entities'] == '0');
			unset($this->LinkConfig['ignore_entities']);
		}

		if (isset($this->LinkConfig['img'])) {
			$img = '<img src="'.$this->LinkConfig['img'].'" alt="'.$alt.'" />';
			unset($this->LinkConfig['img']);
		}

		if (isset($this->LinkConfig['anchor'])) {
			$anchor = $this->LinkConfig['anchor'];
			unset($this->LinkConfig['anchor']);
		} else {
			$anchor = '';
		}

		if (!isset($this->LinkConfig['class'])) {
			$this->LinkConfig['class'] = '';
		}

		if (isset($this->LinkConfig['ifpageclass']) && $this->LinkConfig['page'] == $this->GetPage()) {
			$this->LinkConfig['class'] = $this->LinkConfig['ifpageclass'].' '.$this->LinkConfig['class'];
		}

		if (!empty($this->LinkConfig['class'])) {
			$class = ' class="'.$this->LinkConfig['class'].'"';
		} else {
			$class = '';
		}

		unset($this->LinkConfig['class']);
		if (isset($this->LinkConfig['ifpageclass'])) {
			unset($this->LinkConfig['ifpageclass']);
		}

		if (isset($this->LinkConfig['id'])) {
			$id = ' id="'.$this->LinkConfig['id'].'"';
			unset($this->LinkConfig['id']);
		} else {
			$id = '';
		}

		if (isset($this->LinkConfig['text'])) {
			if ($ignore_entities) {
				$text = $this->LinkConfig['text'];
			} else {
				$text = htmlentities(html_entity_decode($this->LinkConfig['text'], ENT_QUOTES), ENT_QUOTES);
			}
			unset($this->LinkConfig['text']);
		}

		if (!empty($this->LinkConfig['extra'])) {
			$extra = ' '.$this->LinkConfig['extra'];
			unset($this->LinkConfig['extra']);
		} else {
			$extra = '';
		}

		if (isset($this->LinkConfig['target'])) {
			$target = $this->LinkConfig['target'];

			if ($href) {
				$target .= '.';
			} else {
				$target = ' target="'.$target.'"';
			}

			unset($this->LinkConfig['target']);
		}

		if (isset($this->LinkConfig['url'])) {
			$url = (!$this->LinkConfig['url'] == '0');
			unset($this->LinkConfig['url']);
		}

		if (isset($this->LinkConfig['id_field']) && isset($this->LinkConfig['id_value'])) {
			$this->LinkConfig[$this->LinkConfig['id_field']] = $this->LinkConfig['id_value'];
			unset($this->LinkConfig['id_field']);
			unset($this->LinkConfig['id_value']);
		}

		if ($nopage) {
			if (isset($this->LinkConfig['page'])) {
				unset($this->LinkConfig['page']);
			}
		}

		$this->Hook('LinkPostInit');

		$this->Hook('LinkPreStart');

		$ConfigList = Array();

		// TODO: Formcount implementieren
		/* if (isset($_SESSION['formcount'])&&!isset($this->LinkConfig['nofc'])) {
			$this->LinkConfig['formcount'] = $_SESSION['formcount'];
		} else {
			unset($this->LinkConfig['nofc']);
		} */

		if ($use_query) {
			$query = $this->GetGet();
			if (isset($query['formclean'])) {
				unset($query['formclean']);
			}
			if ($prefer_query) {
				$this->LinkConfig = array_merge($this->LinkConfig, $query);
			} else {
				$this->LinkConfig = array_merge($query, $this->LinkConfig);
			}
		}

		$this->Hook('LinkStart');

		if (isset($this->LinkConfig['file']) && !empty($this->LinkConfig['file'])) {
			$file = $this->LinkConfig['file'];
			unset($this->LinkConfig['file']);
		} else {
			if (isset($_SERVER['REDIRECT_URL'])) {
				$file = $_SERVER['REDIRECT_URL'];
			} else {
				$file = RD::PHP_SELF();
			}
		}

		if (isset($this->LinkConfig['host'])) {
			$host = $this->LinkConfig['host'];
			unset($this->LinkConfig['host']);

			if (substr($host, -1, 1) == '/' && (substr($file, 0, 1) == '/' || substr($PHP_SELF, 0, 1) == '/')) {
				$host = substr($host, 0, strlen($host) -1);
			}
		} else {
			$host = '';
		}

		foreach($this->LinkConfig as $var=>$val) {
			if (is_array($val)) {
				foreach($val as $val_key => $val_val) {
					$ConfigList[] = self::urlEncode($var).'['.self::urlEncode($val_key).']='.self::urlEncode($val_val);
				}
			} else {
				$ConfigList[] = self::urlEncode($var) . '=' . self::urlEncode($val);
			}
		}

		$gets = implode(($js ? "&" : "&amp;"), $ConfigList);

		if ($random_chunk) {
			$random = ''.mt_rand();
		} else {
			$random = '';
		}

		if (!empty($gets)) {
			$gets = "?".$gets;
			if (!empty($random)) {
				$get .= ($js ? "&" : "&amp;").$random;
			}
		} else {
			$gets = '';
			if (!empty($random)) {
				$get .= "?".$random;
			}
		}

		$this->Hook('LinkPostStart');

		$this->Link = $host.$file.$gets;
		
		$this->Hook('LinkPreFinish');

		if ($js) {
			if (isset($target)) {
				$result = $target.".document.location.href='".$this->Link."'";
			} else {
				$result = "document.location.href='".$this->Link."'";
			}
		} elseif ($url) {
			$result = $this->Link;
		} else {
			$result = '<a '.$class.$id;

			if ($define_anchor) {
				$result .= ' name="'.$anchor.'" />';
			} else {

				if (isset($target)) {
					$result .= ' '.$target.' ';
				}
				$result .= ' href="'.$this->Link.'"'.$extra.'>';

				if (isset($img) && !empty($img)) {
					$result .= $img;
				} elseif (isset($text) && !empty($text)) {
					$result .= $text;
				} else {
					$result .= $this->Link;
				}

				$result .= '</a>';
			}
		}
		
		$this->LinkResult = $result;
		
		$this->Hook('LinkFinish');

		$result = $this->LinkResult;

		unset($this->LinkConfig);
		unset($this->LinkResult);
		unset($this->Link);

		$this->Hook('LinkPostFinish');

		return $result;
	}

}

/**
 * 
 * Workaround Class // TODO
 * 
 */

class RDM_Link_NoUrlencode {
	
	public $String;
	
	public function __construct($String) {
		$this->String = $String;
	}
	
	public function __toString() {
		return strval($this->String);
	}
	
}

if (!function_exists('link')) {
	function link() {
		$args = func_get_args();
		return RDC::CallObject(RD::$Self,'Link',$args);
	}
}
