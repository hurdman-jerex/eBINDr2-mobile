<?php

class section_API extends hapi {

    public function segments() {
    	$this->bind( 'section', $this->segments[3] );
    	return $this->read("select seg.*, sec.name as section from common.segment seg inner join common.section sec on seg.section = sec.id where section = '|section|'", 0 );
    }
    
    public function segment() {
    	$this->bind( 'segment', $this->segments[3] );
    	return $this->read("select * from common.segment where id = '|segment|'", 0 );    	
    }
    
}