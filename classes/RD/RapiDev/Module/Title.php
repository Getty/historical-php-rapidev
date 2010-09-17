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

class RD_RapiDev_Module_Title extends RDM {

	public static $RD_Functions = Array(
									'SetTitle',
									'SetTitlePrefix',
									'SetTitleSuffix',
									'SetDefaultTitle',
								  );

	public static $RD_Depencies = Array();


	/**
	 * Assigns the title.
	 *
	 * 
	 * 
	 * 
	 */
	
	public function HookPreFinish() {
		if (isset($this->setTitle)) {
			$this->title = $this->titlePrefix.$this->setTitle.$this->titleSuffix;
		} else {
			$this->title = $this->titlePrefix.$this->defaultTitle.$this->titleSuffix;
		}
		$this->Assign('title', $this->title);
		return;
	}

	/**
	 * Sets the title. If the title is empty it will be taken the defaulttitle.
	 *
	 * @param String $defaultTitle
	 * 
	 * 
	 */
	
	public function SetTitle($title) {
		$this->setTitle = $title;
	}

	public $defaultTitle = "";
	public $titlePrefix = "";
	public $titleSuffix = "";
	public $title;
	public $setTitle;
	
	/**
	 * Sets the defaulttitle.
	 *
	 * @param String $defaultTitle
	 * 
	 * 
	 */

	public function SetDefaultTitle($defaultTitle) {
			$this->defaultTitle = $defaultTitle;
	}

	/**
	 * Sets the titleprefix
	 *
	 * @param String $titlePrefix 
	 *
	 * 
	 */

	public function SetTitlePrefix($titlePrefix) {
		$this->titlePrefix = $titlePrefix;
	}

	/**
	 * Sets the titlesuffix
	 *
	 * @param String $titleSuffix 
	 *
	 * 
	 */
	
	public function SetTitleSuffix($titleSuffix) {
		$this->titleSuffix = $titleSuffix;
	}

}

