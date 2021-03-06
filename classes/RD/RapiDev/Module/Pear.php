<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * PEAR Module
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

class RD_RapiDev_Module_Pear extends RD_Module {
	
	public static $Self;

	public static $RD_Functions = Array();
	public static $RD_Depencies = Array();
	
	public function __construct($main) {
		self::$Self = $this;
		
		parent::__construct($main);
		
		RDD::Log('Loading PEAR',TRACE,1201);

		$PEARLibs = $this->Dirs('libs/pear');

		foreach($PEARLibs as $PEARLib) {
			$this->AddIncludePath($PEARLib);
		}
		
		require_once('PEAR.php');
		
	}

	public function Setup() {


	}

}
