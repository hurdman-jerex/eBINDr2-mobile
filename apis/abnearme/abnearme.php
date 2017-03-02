<?php

class abnearme_API extends hapi {

	public function find() {
		$this->bind( 'lat', $this->segments[3] );
		$this->bind( 'long', $this->segments[3] );
	}

	public function getByLatLong() {
		$this->bind( 'lat', $this->segments[0] );
		$this->bind( 'long', $this->segments[1] );
		return $this->read("select * from addressgeo where lat = '|lat|' and long = '|long|'", 0);
	}	
}

?>