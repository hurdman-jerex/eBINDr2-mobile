<?php

class ratings_API extends hapi{

	public function get() {
    $this->bind( 'bid', $this->segments[0] );
    return $this->read("[e2mobile/api/business/ratings]", 0);	
	}
	
	public function test() {
		return $this->segments[0];
	}
}

?>