<?php

/**
 * Eine Klasse die beim Paging hilft. Sie versteckt die ganze Mathelogik und
 * bietet ein einfach zu benutzenden Interface.
 * 
 * Die Pager Klasse arbeitet nur mit Zahlen. Sie baut keine SQL Abfragen auf,  
 * sie instanziert keine Objekte und dient nicht als Container fuer z.B. 
 * Sortierung.
 *
 * Wenn Sie die oben gennanten features brauchen dann muessen sie die Klasse
 * extenden.
 * 
 * Die Klasse kommuniziert mit anderen Modellen durch 5 Parameter: 
 * aktuelle Seite, Anzahl der Elemente pro Seite, Zahlenbereich, Limit, Offset.
 * Mit View durch alle vorhandene Methoden oder als Array (ToArray()).
 * 
 * Beispiel:
 * <code>
   $this->paging->SetPageSize($this->limit)
                ->SetRangeSize(15)
                ->SetCount($this->merkliste->SetUser_ID($this->userId)->Find());
   
   $result = $this->merkliste->UseLimit($this->paging->GetLimit())
                          ->UseOffset($this->paging->GetOffset())
                          ->UseOrder('Datum', 'DESC')
                          ->FetchAssoc();

   $this->Assign('paging', $this->paging->ToArray());   

 * </code>
 * 
 * @author Dmytro Navrotskyy <navrotskyy@webix.de>
 * @todo   Validation
 */

if (!class_exists('RD_Util_Paging')) {

	class RD_Util_Paging extends RDO {

		/**
		 * Der Zahlenbereich der in der Page angezeigt wird.
		 * 
		 * @var array 
		 */
		public $Range = false;

		/**
		 * Die Anazhl der Zahlen die in die Range reingeschrieben werden.
		 * 
		 * @var int
		 */
		protected $rangeSize;

		/**
		 * Default Anzahl der Zahlen.
		 * 
		 * @var int
		 */
		public static $defRangeSize = 5;

		/**
		 * Die Zahl von der aktuellen Seite.
		 * 
		 * @var int
		 */
		protected $currentPage;

		/**
	     * Die Anzahl der Elementen die in einer Page angezeigt werden.
	     *
	     * @var int
	     */
		protected $limit;

		/**
	     * Die Gesamtanzahl der Elementen.
	     *
	     * @var int
	     */
		protected $count;

		/**
	     * Setzt die aktuelle Seite. Default 1.
	     *
	     * @param int $currentPage
	     */
		public function __construct($currentPage = null) {
			$this->currentPage = (null === $currentPage || false === $currentPage) ? 1 : (int) $currentPage;
			$this->currentPage = $this->currentPage < 1 ? 1 : $this->currentPage;
		}

		public function SetCurrentPage($CurrentPage) {
			$this->currentPage = $CurrentPage;
			return $this;
		}

		public function GetCurrentPage() {
			return $this->currentPage;
		}

		/**
	     * Setzt die Anzahl der Elementen pro Seite.
	     * 
	     * @param int $number
	     * @return $this
	     */
		public function SetPageSize($number) {
			$this->limit = (int) $number;
			return $this;
		}

		/**
	     * Setzt die goriesse des Nummerbereiches.
	     * 
	     * @return $this
	     */
		public function SetRangeSize($size) {
			$this->rangeSize = $size;
			return $this;
		}

		/**
	     * Setzt default Nummerbereich.
	     *
	     * @param int $size
	     */
		public static function SetDefaultRangeSize($size) {
			self::$defRangeSize = $size;
		}

		/**
	     * Liefert die Range size. Wenn default Range size da ist, dann die.
	     */
		public function GetRangeSize() {
			if(empty($this->rangeSize) && isset(self::$defRangeSize)) {
				$this->rangeSize = self::$defRangeSize;
			}

			return $this->rangeSize;
		}

		/**
	     * Liefert den Limit.
	     * 
	     * @return int
	     */
		public function GetLimit() {
			return empty($this->limit) ? false : $this->limit;
		}

		/**
	     * Brechnet und liefert den Offset.
	     * Wird fuer Datenbankabfragen benoetigt.
	     * Und vielleicht fuer Darstellungen wie:
	     * Elemente 15-25 werden angezeigt.
	     * 
	     * @return int
	     * @todo Eine Methode die ausm docblock die zweite Zahl liefert :)
	     */
		public function GetOffset() {
			return $this->GetLimit() * $this->GetCurrent() - $this->GetLimit();
		}

		/**
	     * Setzt die Anzahl aller Elemente.
	     * 
	     * @param int $number
	     * @return Pager $this
	     */
		public function SetCount($number) {
			$this->count = (int) $number;
			return $this;
		}

		/**
	     * Liefert die Gesamtanzahl der Elemente.
	     * 
	     * @return int
	     */
		public function GetCount() {
			return $this->count;
		}

		/**
	     * Liefert die Nummer der vorigen Seite oder 1 wenn die aktuelle Seite die
	     * erste ist.
	     * 
	     * @return int
	     */
		public function GetPrevious() {
			return ($this->currentPage <= 1) ? false : ($this->currentPage - 1);
		}

		/**
	     * Liefert die Nummer der aktuellen Seite.
	     * 
	     * @return int
	     */
		public function GetCurrent() {
			return $this->currentPage;
		}

		/**
	     * Liefert die Nummer der nächsten Seite.
	     *
	     * @return int
	     */
		public function GetNext() {
			return ($this->currentPage >= $this->GetPageCount()) ? false : ($this->currentPage + 1);
		}

		/**
	     * Deprecated.
	     * 
	     * Prueft ob die aktuelle Seite die erste ist.
	     * 
	     * @sxs: die methoden macht IMHO keinen wirklichen Sinn
	     * @dimi: hast recht. Hab sie durch die logik in GetPrevious ersetz. Das
	     * heisst wenn es die erste page ist, dann liefert GetPrevious false zuruek.
	     * 
	     * @return bool
	     */
		public function IsFirst() {
			return $this->currentPage == 1;
		}

		/**
	     * Deprecated.
	     * 
	     * Prueft ob die aktuelle Seite die letzte ist.
	     * 
	     * @sxs: die methoden macht IMHO keinen wirklichen Sinn
	     * @dimi: siehe oben.
	     * @getty: ich glaub ehr die kann bleiben, wenn schon dann "konsequent"
	     *
	     * @return bool
	     */
		public function IsLast() {
			return $this->currentPage == $this->GetPageCount();
		}

		/**
	     * Prueft ob das die ersten knoepfe sind. Das heisst wenn es 50 seiten gibt
	     * und die Nummern 1-10 angezeigt werden liefert die Methode true. Wenn
	     * es weniger Seiten gibt als die "rangeSize" liefert die Methode ebenfalls
	     * true.
	     * 
	     * @return bool
	     */
		protected function areFirstButtons() {
			if($this->areLessPagesAsRange()) {
				return true;
			}

	        $x = floor($this->getRangeSize() / 2);
            return ($this->currentPage - $x) <= 0;
		}

		/**
	     * Prueft ob das die letzten Knoepfe sind. Das heisst wenn es 55 Seiten gibt
	     * und die Nummer 45-55 gerade angezeigt werden, liefert die Methode true. 
	     * Sie liefert ebenfalls true wenn es weniger Seiten als die "rangeSize" 
	     * gibt.
	     * 
	     * @return bool
	     */
        protected function areLastButtons() {
            if($this->areLessPagesAsRange()) {
                return true;
            }
            $x = floor($this->getRangeSize() / 2);
            return ($this->currentPage + $x) >= $this->GetPageCount();        
        } 
	    
		/**
	     * Liefert die Anzahl der Seiten.
	     * 
	     * @return int
	     */
		public function GetPageCount() {
			return ceil($this->GetCount() / $this->limit);
		}

		/**
	     * Liefert die Nummer von dem Button das den naechsten Bereich anzeigt.
	     * 
	     * @return int
	     */
		public function GetJumpForward() {
			$this->DetermineRange();

			if($this->areLastButtons()) {
				return false;
			}

			return max($this->Range) + 1;
		}

		/**
	     * Liefert die Nummer von dem Button das den vorigen Bereich anzeigt.
	     * 
	     * @return int
	     */
		public function GetJumpBackward() {
			$this->DetermineRange();

			if($this->areFirstButtons()) {
				return false;
			}

			return min($this->Range) - 1;
		}

		/**
	     * Ermittelt den Zahlenbereich.
	     * 
	     * @todo Refactoring: Die Methode ist zu lang und hat zu viel Verantwortung.
	     * 
	     * @getty: bei weitem ausreichend, das aufzusplitten wuerde glaub ich mehr stress als sinn erzeugen.
	     */
		public function DetermineRange() {
			
			// @getty: Das sollte abgeschafft werden, kann ja sein, das jemand parameter aendert.
			if($this->Range !== false) {
				return;
			}

			//the paging class does not support even range sizes
			//because we want to have equal number of buttons on both sides of the
			//current page button
			if(!($this->getRangeSize() & 1)) {//bit operator
				$this->rangeSize = $this->getRangeSize() - 1;
			}

			$x = floor($this->getRangeSize() / 2);

			$start = $this->currentPage - $x;
			$end   = $this->currentPage + $x;

			if($this->areFirstButtons()) {
				$start = 1;
				$end   = $this->getRangeSize();
			} elseif ($this->areLastButtons()) {
				//dont ask me why -1. It works :)
				$start = $this->GetPageCount() - ($this->getRangeSize() - 1);
				$end   = $this->GetPageCount();
			}

			if($this->areLessPagesAsRange()) {
				$end = $this->GetPageCount();
			}

			$this->Range = range($start, $end);
			
			return $this;
		}

		/**
	     * Prueft ob es weniger Seiten gibt als der Seitenberreich erwartet.
	     *
	     * @return bool
	     */
		protected function areLessPagesAsRange() {
			return ($this->GetPageCount() - $this->getRangeSize()) < 0;
		}

		/**
	     * Bildet ein array aus den Eigenschaften der Klasse.
	     * 
	     * @return array
	     */
		public function GetPaging() {
			$this->DetermineRange();

			$output = array(
				'previous'	=> $this->GetPrevious(),
				'current'	=> $this->GetCurrent(),
				'next'		=> $this->GetNext(),
				'pageSize'	=> $this->GetLimit(),
				'offset'	=> $this->GetOffset(),
				'pageCount'	=> $this->GetPageCount(),
				'jumpForward'	=> $this->GetJumpForward(),
				'jumpBackward'	=> $this->GetJumpBackward(),
				'count'		=> $this->GetCount(),
				'range'		=> $this->Range
			);

			return $output;
		}

	}

}
