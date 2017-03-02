<?php

class journal_API extends hapi {

	public function get() {
		$this->bind( 'bid', $this->segments[0] );
		return $this->read("[e2mobile/api/business/journal]", 0);
	}

	public function getByJid() {
		$this->bind( 'bid', $this->segments[0] );
		$this->bind( 'jid', $this->segments[1] );
		$result = $this->read("[e2mobile/api/business/journal/get]", 0);
		// $result = $result->results;
		return array(
			"post_vars" => $_POST,
			"error" => $result['error'],
			'query' => $result['sql'],
			'segments' => $this->segments,
			'results' => $result
		);		
	}

	public function type() {
		return $this->read("select code, description from common.dropdown where type='jt' and code<>''", 0);
	}

	public function add() {
		$this->bind('bid', $this->segments[0]);
		$this->bind('staff', $_POST['staff']);
		
		$date_array = explode("-", $_POST['date']);
		$date = $date_array[2] . '-' . $date_array[0] . '-' . $date_array[1];
		
		$this->bind('date', $date);
		$this->bind('type', $_POST['type']);
		$this->bind('journal_notes', $_POST['journal_notes']);
		$result = $this->write("journal:add", "[e2mobile/api/business/journal/add]");

		return array(
			"post_vars" => $_POST,
			"error" => $result['error'],
			'query' => $result['sql'],
			'segments' => $this->segments
		);
	}

	public function edit() {
		$this->bind('bid', $this->segments[0]);
		$this->bind('jid', $this->segments[1]);
		$this->bind('staff', $_POST['staff']);
		
		$date_array = explode("-", $_POST['date']);
		$date = $date_array[2] . '-' . $date_array[0] . '-' . $date_array[1];
		
		$this->bind('date', $date);
		$this->bind('type', $_POST['type']);
		$this->bind('journal_notes', $_POST['journal_notes']);
		$result = $this->write("journal:edit", "[e2mobile/api/business/journal/edit]");

		return array(
			"post_vars" => $_POST,
			"error" => $result['error'],
			'query' => $result['sql'],
			'segments' => $this->segments
		);
	}

	public function testedit() {
		$this->bind('bid', $this->segments[0]);
		$this->bind('staff', $this->segments[1]);
		
		$date_array = explode("-", $this->segments[2]);
		$date = $date_array[2] . '-' . $date_array[0] . '-' . $date_array[1];
		
		$this->bind('date', $date);
		$this->bind('type', $this->segments[3]);
		$this->bind('journal_notes', $this->segments[4]);
		$this->bind('jid', $this->segments[5]);
		$result = $this->write("journal:edit", "[e2mobile/api/business/journal/edit]");

		return array(
			"post_vars" => $_POST,
			"error" => $result['error'],
			'query' => $result['sql'],
			'segments' => $result
		);
	}		

}

?>