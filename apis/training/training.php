<?php

class training_API extends hapi {

	/*
	 * Get a training piece of content by id
	 */
	public function get() {
		$this->bind( 'account', $this->segments[3] );
		$this->bind( 'tid', $this->segments[4] );
		
		return $this->read( "select * from trainingcontent where tid = '|tid|' and account = '|account|' and deleted is null and tid > 0", 3600 );
	}
	
	public function test() {
		return $this->read( "select * from common.videos", 0 );
	}
	
	public function watch() {
		$this->bind( 'video', $_POST['video'] );
		return $this->read( "select * from common.videos where vid = '|video|'", 0 );
	}

	public function duty() {
		$columns = explode("%20",$_POST['duty']);
		
		foreach ($columns as $column)
			$where[] = ' name like \'%'.$column.'%\'';
		
		$sql = "select * from common.videos WHERE " . join($where, ' OR ');

		return $this->read( $sql, 0 );
	}
	
}

?>