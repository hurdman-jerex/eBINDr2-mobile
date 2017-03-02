<?php

class complaints_API extends hapi{

	public function get() {
        $this->bind( 'bid', $this->segments[0] );
		$this->bind( 'cid', $this->segments[1] );
        return $this->read("[e2mobile/api/business/complaints/get]", 0);    
	}

	public function summary() {
    $this->bind( 'bid', $this->segments[0] );
    return $this->read("[e2mobile/api/business/complaints/summary]", 0);	
	}
	
	public function info() {
    $this->bind( 'bid', $this->segments[0] );
    return $this->read("[e2mobile/api/business/complaints]", 0);		
	}

	public function latestComplaints() {
    $this->bind( 'bid', $this->segments[0] );
    return $this->read("[e2mobile/api/business/latestcomplaints]", 0);		
	}

	public function advance() {
    $this->bind( 'bid', $this->segments[0] );
    return $this->read("[e2mobile/api/business/adv]", 0);		
	}

	public function compliments() {
    $this->bind( 'bid', $this->segments[0] );
    return $this->read("[e2mobile/api/business/compliments]", 0);		
	}

	public function oldComplaints() {
    $this->bind( 'bid', $this->segments[0] );
    return $this->read("[e2mobile/api/business/oldcomplaints]", 0);		
	}	
    
    public function closeCode() {
    return $this->read("[e2mobile/api/business/complaints/closecode]", 0);        
    }
    
    public function types() {
    return $this->read("[e2mobile/api/business/complaints/types]", 0);        
    }
    
    public function concerning() {
    return $this->read("[!Concerning]", 0);        
    }
    
    public function CBIDisabled() {
    return $this->read("[e2mobile/api/business/complaints/CBIDisabled]", 0);        
    }
    
    public function getSubCloseCodeByCode() {
    $this->bind( 'code', $this->segments[0] );
    return $this->read("[e2mobile/api/business/complaints/get subclosecode by code]", 0);        
    }
    
    public function getSubConcerningByCode() {
    $this->bind( 'code', $this->segments[0] );
    return $this->read("[e2mobile/api/business/complaints/get subconcerning by code]", 0);        
    }
    
    public function getContacts() {
    $this->bind( 'bid', $this->segments[0] );
    return $this->read("[!Contact]", 0);        
    }
    
    public function getLocations() {
    $this->bind( 'bid', $this->segments[0] );
    return $this->read("[!Location]", 0);        
    }
    
    public function getDbas() {
    $this->bind( 'bid', $this->segments[0] );
    return $this->read("[!DbaName]", 0);        
    }
    
    public function seriousReason() {
    return $this->read("[!SeriousReason]", 0);        
    }
    
    public function settlementDesired() {
    return $this->read("[!SettlementDesired]", 0);        
    }
    
    public function edit() {
        $this->bind('cid', $this->segments[0] );
        $complaint = $this->read("select * from complaint where cid='|cid|'", 0);  
        $query = $this->generateQuery( 'complaint', array_keys( $complaint[0] ), $_POST );
        //return array( 'query' => $query );
        
        $result = $this->write("complaint:edit", $query);
        
        return array(
            "post_vars" => $_POST,
            "error" => $result['error'],
            'query' => $result['sql'],
            'segments' => $this->segments
        );
    }
    
    public function editextra() {
        $this->bind('cid', $this->segments[0] );
        $complaint = $this->read("select * from complaintextra where cid='|cid|'", 0);  
        $query = $this->generateQuery( 'complaintextra', array_keys( $complaint[0] ), $_POST );
        //return array( 'query' => $query );
    
        $result = $this->write("complaintextra:edit", $query);
        
        return array(
            "post_vars" => $_POST,
            "error" => $result['error'],
            'query' => $result['sql'],
            'segments' => $this->segments
        );
    }
    
    private function generateQuery( $table='complaint', $keys, $post )
    {
        $query = "update $table set ";

        $inputs = array();
        $valueArray = array();
        foreach($post as $key=>$value) {
            if( ! in_array( $key, $keys ) || $key == 'CID') continue;
        
            $inputs[] = " $key = '". mysql_escape_string( $value ) . "'";
        }
            
        $query .= implode( ',', $inputs );

        $query .= " where CID = " . $post['CID'];
        
        return $query;
    }
}

?>