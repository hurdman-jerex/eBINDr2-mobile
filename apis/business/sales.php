<?php

class sales_API extends hapi {

	public function get() {
        $this->bind( 'bid', $this->segments[0] );
        return $this->read("[e2mobile/api/business/sales]", 0);
    }
    
    public function getbyid() {
        $this->bind( 'bid', $this->segments[0] );
		$this->bind( 'sid', $this->segments[1] );
		return $this->read("[e2mobile/api/business/sales/get]", 0);
	}

	public function callresult() {
		return $this->read("select Code,concat(code,' - ',description) as Description from salesresults where manual='y' order by description", 0);
	}

	public function contact() {
		$this->bind( 'bid', $this->segments[0] );
		return $this->read("select pid, concat(prename,' ',firstname,' ',lastname,if(title>'',concat(' - ',title),''),ifnull(concat(' - ',address.street1),''),if(person.complaint='y',' - COMPLAINT','')) as ContactName from person left join address on person.bid=address.bid and person.location=address.aid where person.bid=|bid|", 0);
	}

	public function add() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('staff', $_POST['staff']);
        $this->bind('callresult', $_POST['callresult']);
        
        $date_array = explode("-", $_POST['callagain']);
        $date = $date_array[2] . '-' . $date_array[0] . '-' . $date_array[1];
        
        $this->bind('callagain', $date .' '. date("H:i:s", strtotime($_POST['calltime'])));
        $this->bind('contact', $_POST['contact']);
        $this->bind('reminder', $_POST['reminder']);
        $this->bind('comment', $_POST['comment']);

        $result = $this->write("sales:add", "[e2mobile/api/business/sales/add]");

        return array(
            "post_vars" => $_POST,
            "error" => $result['error'],
            'query' => $result['sql'],
            'segments' => $this->segments
        );
    }
    
    public function edit() {
        $this->bind('bid', $this->segments[0]);
		$this->bind('sid', $this->segments[1]);
		$this->bind('staff', $_POST['staff']);
		$this->bind('staff_old', $_POST['staff_old']);
		$this->bind('callresult', $_POST['callresult']);
		
		$date_array = explode("-", $_POST['callagain']);
		$date = $date_array[2] . '-' . $date_array[0] . '-' . $date_array[1];
		
		$this->bind('callagain', $date .' '. date("H:i:s", strtotime($_POST['calltime'])));
        $this->bind('callagain_old', $_POST['callagain_old']);
		$this->bind('contact', $_POST['contact']);
		$this->bind('reminder', $_POST['reminder']);
		$this->bind('comment', $_POST['comment']);

		$result = $this->write("sales:edit", "[e2mobile/api/business/sales/edit]");
        
        return array(
			"post_vars" => $_POST,
			"error" => $result['error'],
			'query' => $result['sql'],
			'segments' => $this->segments
		);
	}

}

?>