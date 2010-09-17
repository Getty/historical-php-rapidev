<?php

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
 * @author     Harald Doderer <harrykan@gmx.de>
 * @copyright  2007 Sven Strittmatter
 * @license    GPL-2 
 * 
 */


class RDM_Validate_Email extends RDM {

	/**
	 * Validates Input against a minimum length and maximum length
	 *
	 * @param String $input
	 * @param String $options
	 * @return -
	 */
	
	public function Start($input, $options = array()) {
		if ("" === $input) return false;
		
		// from http://iamcal.com/publish/articles/php/parsing_email
		# qtext = <any CHAR excepting <">, "\" & CR, and including, linear-white-space>
		$qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
		# dtext = <any CHAR excluding "[", "]", "\" & CR, & including linear-white-space>
		$dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
		# atom = 1*<any CHAR except specials, SPACE and CTLs>
		$atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'.
		   		'\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
		# quoted-pair =  "\" CHAR
		$quoted_pair = '\\x5c\\x00-\\x7f';
		# domain-literal =  "[" *(dtext / quoted-pair) "]"
		$domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
		# quoted-string = <"> *(qtext/quoted-pair) <">
		$quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
		# domain-ref = atom
		$domain_ref = $atom;
		# sub-domain = domain-ref / domain-literal
		$sub_domain = "($domain_ref|$domain_literal)";
		# word = atom / quoted-string
		$word = "($atom|$quoted_string)";
		# domain = sub-domain *("." sub-domain)
		$domain = "$sub_domain(\\x2e$sub_domain)*";
		# local-part = word *("." word)
		$local_part = "$word(\\x2e$word)*";
		# addr-spec = local-part "@" domain
		$addr_spec = "$local_part\\x40$domain";
		$regexp = "!^$addr_spec$!";
		
		$valideRFC = preg_match($regexp, $input);
		
		$valideDomain = false;
		
		// Zum Verständnis, warum hier das ganze noch einmal geprüft wird:
		// Der Algorithmus oben prüft ob die E-Mail nachm RFC 2822 richtig ist. (http://www.ietf.org/rfc/rfc2822.txt)
		// Das Problem hierbei ist aber, dass auch eine E-Mail wie mail@localhost richtig wäre,
		// deshalb schauen wir hier nochmal ob eine gültige TLD vorhanden ist.
		
		$EMail = explode('@',$input);
		if(count($EMail) === 2) {
			$Domain = end($EMail);
			$DomainParts = explode('.',$Domain);
			if(count($DomainParts) > 1) {
				$TLD = end($DomainParts);
				if(strlen($TLD) > 1) {
					$valideDomain = true;
				}
			}
		}
		
		if(!$valideRFC || !$valideDomain){
			if(isset($options['error'])){
				return $options['error'];
			}else{
				return "Dies ist keine g&uuml;ltige E-Mailadresse.";
			}
		}else{
			return true;
		}
	}
}