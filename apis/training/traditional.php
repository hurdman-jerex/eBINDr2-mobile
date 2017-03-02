<?php

class traditional_API extends hapi {

	public function categories() {
		$this->bind( 'library', (int) $this->segments[3] );
		$this->bind( 'parent', (int) $this->segments[4] );
		return $this->read( "select * from common.videoscategories where library = '|library|' and parent = '|parent|'" );
	}
	
	public function breadcrumbcats() {
		$this->bind('categories', $this->segments[3]);
		return $this->read( "select id, title from common.videoscategories where id in (|categories|) order by id" );
	}
	
	public function content() {
		$this->bind( 'category', $this->segments[3] );
		return $this->read( "select * from common.videoscategory c inner join common.videos v using(vid) where c.category = '|category|'" );
	}

}

?>