<?
/**
 * RapiDev, Rapid Development PHP Application Framework
 *
 * PHP version 5
 *
 * Page Class (the classic Router)
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


require_once(RD_PATH.DIR_SEP.'libs'.DIR_SEP.'phpmailer'.DIR_SEP.'class.phpmailer.php');

class RD_RapiDev_Module_Mail extends RDM {
	public static $RD_Functions = Array('Mail');
	public static $RD_Depencies = Array();

	public static $type = 'mail';

	public function Mail($addresses, $content, $isHtml = true, $attachment = array()) {
		$mail = new PHPMailer;
		
		if (isset($content['body']) && $content['body'] != '') {
			$mail->Body = trim($content['body']);
		}
		
		if (isset($content['altbody']) && $content['altbody'] != ''){
			$mail->AltBody = $content['altbody'];
		} else {
			if ($isHtml) {
				$mail->IsHTML(true);
			}
			
			$mail->AltBody = '';
		}
		
		if (isset($content['subject']) && $content['subject'] != ''){
			$mail->Subject = $content['subject'];
		}
		
		if (isset($content['from']) && $content['from'] != '') {
			$mail->From = $content['from'];
			$mail->Sender = $content['from'];
		}
		
		if (isset($content['fromName']) && $content['fromName'] != '') {
			$mail->FromName = $content['fromName'];
		}
		
		
		if(!empty($attachment)) {
			!isset($attachment['path']) 	&& $attachment['path'] 		= null;
			!isset($attachment['name']) 	&& $attachment['name'] 		= null;
			!isset($attachment['encoding']) && $attachment['encoding'] 	= 'base64';
			!isset($attachment['type']) 	&& $attachment['type'] 		= 'application/octet-stream';
			
			$mail->AddAttachment($attachment['path'],$attachment['name'],$attachment['encoding'],$attachment['type']);
		}
		
		$sendFailed = false;
		
		if ($addresses) {
			foreach ($addresses as $address){
				$mail->AddAddress($address['email'], $address['name']);
				
			}
			
			if (!$mail->Send()) {
				$sendFailed = true;
				RDD::Log('RDM_Mail: In PHPMailer an error occured: '.$mail->ErrorInfo, ERROR);
			}
				
			$mail->ClearAddresses();
		} else {
			RDD::Log('RDM_Mail: No mail addresses passed to method Mail()', ERROR);
			$sendFailed = true;
		}
		
		return !$sendFailed;
	}
}
