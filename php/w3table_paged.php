<?php
# June 2008 - First/Last links added, thanks to Tim Sawyer for the suggestion and code

require_once 'w3table.php';

class w3_Table_Paged extends w3_Table {
	# Configurable variables
	var $total_items = 0;
	var $page_size = 10;
	var $pager_var = 'page';

	# Internal variables
	var $title = '';
	var $current_page = 1;
	var $total_pages = 0;
	var $start = 0;
	var $data = array();

	function w3_Table_Paged($title, $header, $attr = array()) {
		if (isset($_GET[$this->pager_var]) && $_GET[$this->pager_var] > 0) {
			$this->current_page = intval($_GET[$this->pager_var]);
		} else {
			$this->current_page = 1;
		}
		$this->title = $title;
		parent::w3_Table($title, $header, $attr);
	}

	# This function is used on small data sets. That is, not for database results
	function setData(&$data) {
		if (!is_array($data)) {
			$this->data = array($data); 
		} else {
			$this->data =& $data;
		}
		if ($this->page_size < 1) {
			return false;
		}

		$this->total_items = sizeof($this->data);
		$this->start = (($this->current_page - 1) * $this->page_size);
		$this->data = array_slice($this->data, $this->start, $this->page_size);

		foreach ($this->data as $row) {
			$this->addRow($row);
		}
	}

	# This function requires an ADODB <http://adodb.sf.net> database object.
	function fetchADODBData(&$db, $sql, $inputarr = FALSE) {
		if (!is_object($db) || !$db->IsConnected()) {
			return 0;
		}

		$sql_count = $this->_rewriteCountQuery($sql);
		$this->total_items = $db->GetOne($sql_count, $inputarr);
		$this->start = (($this->current_page - 1) * $this->page_size);
		// $sql .= " LIMIT " . $this->page_size . " OFFSET " . ($this->start);
		$sql.= ' OFFSET ' . $this->start . ' ROWS FETCH FIRST ' . $this->page_size . ' ROWS ONLY';

		$this->data = $db->GetAll($sql, $inputarr);
		foreach ($this->data as $row) {
			$this->addRow($row);
		}
	}

	function getLinks() {
		$nav = '';
		if ($this->total_items > $this->page_size) {
			$prev = ($this->current_page > 1) ? ($this->current_page -1) : "1";
			$next = 
				($this->current_page < ($this->total_items / $this->page_size)) 
				? ($this->current_page + 1) : $this->current_page;
			$last=ceil($this->total_items / $this->page_size);
			$nav = "Records ". ($this->start + 1) . "-" 
				. ($this->start + sizeof($this->data)) 
				. " of $this->total_items ";
			$prev_url = $this->_append_query($this->pager_var, $prev, 
				$this->pager_var);
			$next_url = $this->_append_query($this->pager_var, $next, 
				$this->pager_var);
			$first_url= $this->_append_query($this->pager_var, 1, $this->pager_var);
			$last_url = $this->_append_query($this->pager_var, $last, $this->pager_var);
			if ($prev == $this->current_page) {
				$nav .= "&lt; First | Previous | ";
			} else {
				$nav .= "&lt; <a href=\"$first_url\">First</a> | <a href=\"$prev_url\">Previous</a> | ";
			}
			if ($next == $this->current_page) {
				$nav .= "Next | Last &gt;";
			} else {
				$nav .= "<a href=\"$next_url\">Next</a> | <a href=\"$last_url\">Last</a> &gt;";
			}
		} else {
			$nav = "Records 1-$this->total_items of $this->total_items";
		}
		return $nav;
	}

	function toHtml() {
		# title and navigation row
		$title_html = "\n\t<span style=\"float: left; text-align: left;\">";
		$title_html .= $this->title . "</span>\n";
		if ($this->page_size > 0) { 
			$title_html .= "\t<span style=\"float: right; text-align: right; font-weight: normal;\">"; 
			$title_html .= $this->getlinks() . "</span>\n";
		}
	$this->setCaption($title_html, array('style' => 'white-space: nowrap;'));

		return parent::toHtml();
	}

	function setPageSize($size) {
		$size = intval($size);
		if ($size > 0) {
			$this->page_size = $size;
		} else {
			$size = 0;
		}
	}

	function setPagerVar($name) {
		$this->pager_var = $name;
	}

	function _append_query($new_key, $new_val, $id) {
		$url = $_SERVER['PHP_SELF'] . "?";
		$query = "";
		foreach ($_GET as $key => $val) {
			if ($key != $new_key) {
				if(is_array($val))	{
					foreach($val as $key1 => $val1)	{
						$url .= urlencode($key . "[" . $key1 . "]=" . $val1) . "&";
					}
				} else {
					$url .= $key . "=" . urlencode($val) ."&";
				}
			}
		}
		return $url .= $new_key . "=". urlencode($new_val);
	}

	# This function was taken from the BSD licensed PEAR::Pager http://pear.php.net/package/Pager

	/**
	 * Helper method - Rewrite the query into a "SELECT COUNT(*)" query.
	 * @param string $sql query
	 * @return string rewritten query OR false if the query can't be rewritten
	 * @access private
	 */
	function _rewriteCountQuery($sql) {
		if (preg_match('/^\s*SELECT\s+\bDISTINCT\b/is', $sql) 
			|| preg_match('/\s+GROUP\s+BY\s+/is', $sql)) {
			return false;
		}
		$queryCount = preg_replace('/(?:.*)\bFROM\b\s+/Uims', 
			'SELECT COUNT(*) FROM ', $sql, 1);
		list($queryCount, ) = preg_split('/\s+ORDER\s+BY\s+/is', $queryCount);
		list($queryCount, ) = preg_split('/\bLIMIT\b/is', $queryCount);
		return trim($queryCount);
	}
}

?>