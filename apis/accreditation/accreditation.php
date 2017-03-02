<?php

class accreditation_API extends hapi {

	public function logsegmentview() {
		$this->bind( 'staff', $this->segments[0] );
		$this->bind( 'segment', $this->segments[1] );
		$this->write( "segment view", "insert into traininganalytics ( staff, day, segment, views, duration ) values ( '|staff|', curdate(), '|segment|', 1, 1 ) on duplicate key update views = views+1, duration = duration+1" );
	}
	
	public function logsegmentduration() {
		$this->bind( 'staff', $this->segments[0] );
		$this->bind( 'segment', $this->segments[1] );
		$this->write( "segment duration", "update traininganalytics set duration = duration+10 where staff = '|staff|' and segment = '|segment|' and day = curdate()" );	
	}

	public function recent() {
		return $this->read( "select segment.id as segmentid, segment.type as segmenttype, segment.name as segmentname, if(course.published>segment.created,course.published,segment.created) as segmentcreated, section.id as sectionid, section.name as sectionname, chapter.id as chapterid, chapter.name as chaptername, course.id as courseid, course.name as coursename, library.id as libraryid, library.name as libraryname from common.segment inner join common.section on segment.section = section.id inner join common.chapter on section.chapter = chapter.id inner join common.course on chapter.course =course.id inner join common.library on course.library = library.id where (select if(published is null,0,1) from common.course where id = (select course from common.chapter where id = (select chapter from common.section where id = segment.section))) = 1 and params is not null and segment.params > '' and segment.created > curdate() - interval 2 month order by segmentcreated desc, library.id");
//		return $this->read( "select segment.id as segmentid, segment.type as segmenttype, segment.name as segmentname, segment.created as segmentcreated, section.id as sectionid, section.name as sectionname, chapter.id as chapterid, chapter.name as chaptername, course.id as courseid, course.name as coursename, library.id as libraryid, library.name as libraryname from common.segment inner join common.section on segment.section = section.id inner join common.chapter on section.chapter = chapter.id inner join common.course on chapter.course = course.id inner join common.library on course.library = library.id where (select if(published is null,0,1) from common.course where id = (select course from common.chapter where id = (select chapter from common.section where id = segment.section))) = 1 and params is not null and segment.params > '' and segment.created > curdate() - interval 2 month order by segment.created desc, library.id" );
	}

	public function courseinfo() {
		$this->bind( 'course', $this->segments[0] );
		return $this->read( "select course.*, library.id as libraryid, library.name as libraryname from common.course inner join common.library on course.library = library.id where course.id = '|course|'" );
	}
	
	public function editcourse() {
		$this->bind( 'field', $this->segments[0] );
		$this->bind( 'course', $this->segments[1] );
		$this->bind( 'value', $_POST['value'] );
		
		switch( $this->segments[0] ) {
			case "photourl": $q = "update course set photourl = '|value|' where id = '|course|'"; break;
			case "description": $q = "update course set description = '|value|' where id = '|course|'"; break;
			case "completed": $q = "update course set completed = if('|value|'='1',now(),null) where id = '|course|'"; break;
			case "published": $q = "update course set published = if('|value|'='1',now(),null) where id = '|course|'"; break;
			case "deleted": $q = "update course set deleted = if('|value|'='1',now(),null) where id = '|course|'"; break;
			case "name": $q = "update course set name = '|value|' where id = '|course|'"; break;
		}
		return $this->write( "common::edit course", $q );
	}

	public function libraryinfo() {
		$this->bind( 'library', $this->segments[0] );
		$tmp = $this->read("select * from common.library where id = '|library|'" );
		$tmp[0]['description'] = base64_encode($tmp[0]['description']);
		return $tmp;
	}

	public function getall() {
		$db = $this->read( "select database() as db" );
		if( $db[0]['db'] != 'hurdmantest' ) {
			return $this->read( "select library.*, count(course.library) as total from common.library left join common.course on library.id = course.library and course.published is not null where course.id is not null group by library.id" );
		} else return $this->read( "select * from common.library" );
	}
	
	public function coursesbylib() {
		$this->bind( 'library', $this->segments[0] );
		$db = $this->read( "select database() as db" );
		if( $db[0]['db'] != 'hurdmantest' ) {
			return $this->read( "select * from common.course where library = '|library|' and deleted is null and course.published is not null" );
		} else {
			return $this->read( "select * from common.course where library = '|library|' and deleted is null" );
		}
	}
	
	public function course() {
		$this->bind( 'course', $this->segments[0] );
		// get the chapters for this course
		$chapters = $this->read( "select * from common.chapter where course = '|course|' order by sort, id" );
		// go through each chapter 
		foreach( $chapters as $i => $chapter ) {
			$this->bind( 'chapter', $chapter['id'] );
			$chapters[$i]['sections'] = $this->read( "select * from common.section where chapter = '|chapter|' order by sort, id" );
			// now go through each section and get each segment
			foreach( $chapters[$i]['sections'] as $l => $section ) {
				$this->bind( 'section', $section['id'] );
				// get the docs
				$chapters[$i]['sections'][$l]['docs'] = $this->read( "select * from common.sectiondocs where section = '|section|'" );
				// get the segments
				$chapters[$i]['sections'][$l]['segments'] = $this->read( "select * from common.segment where section = '|section|' order by sort, id" );
			}
		}
		
		return $chapters;
	}
	
	public function createcourse() {
		//$_POST['outline'] = '[{"name":"Introduction\r","sections":[{"name":" About This Course\r","segments":[" Introduction\r"," Course Overview\r"," About the Author\r"]}]},{"name":"The OCE\r","sections":[{"name":" What is the OCE?\r","segments":[" Where is is found online?\r"," Where is it in eBINDr?\r"," Who has access to it?\r"," What is it\'s cost?\r"]},{"name":" Who should use the OCE?\r","segments":[" Users\r"," Administrators\r"," Charities\r"]}]},{"name":"Conslusion\r","sections":[{"name":" Wrap Up\r","segments":[" Review\r"," For more Information"]}]}]';
		
		
		$this->bind( 'library', $this->segments[0] );
		$this->bind( 'name', $_POST['name'] );
		$this->bind( 'desc' , $_POST['desc'] );
		$outline = json_decode(base64_decode($_POST['outline']));
		
		// 1. first we need to create the course
		$this->write( "common::create course", "insert into common.course (library, name, description, created) values ('|library|', '|name|', '|desc|', now() )" );
		// get the course id
		$tmp = $this->read( "common::select max(id) as id from common.course limit 1" );
		$courseid = $tmp[0]['id'];
		$this->bind( 'course', $courseid );
		
		// 2. then we need to create all the chapters
		foreach( $outline as $chapter ) {
			$this->bind( 'name', str_replace( array("\n","\r\n","\r"), " ", preg_replace( "/^\s/", "", $chapter->name ) ) );
			$this->write( "common::create chapter", "insert into common.chapter ( course, name, created ) values ( '|course|', '|name|', now() )" );
			// get the chapter id
			$tmp = $this->read( "common::select max(id) as id from common.chapter where course = '|course|'" );
			$this->bind( 'chapter', $tmp[0]['id'] );
			
			// 3. go through each section and add them
			foreach( $chapter->sections as $section ) {
				$this->bind( 'name', str_replace( array("\n","\r\n","\r"), " ", preg_replace( "/^\s/", "", $section->name ) ) );
				$this->write( "common::create section", "insert into common.section (chapter, name, created) values ( '|chapter|', '|name|', now() )" );
				// get the section id
				$tmp = $this->read( "common::select max(id) as id from common.section where chapter = '|chapter|'" );
				$this->bind( 'section', $tmp[0]['id'] );
				
				// 4. go through each segment and add them
				foreach( $section->segments as $segment ) {
					$this->bind( 'name', str_replace( array("\n","\r\n","\r"), " ", preg_replace( "/^\s/", "", $segment ) ) );
					$this->write( "common::create segment", "insert into common.segment ( section, name, created ) values ( '|section|', '|name|', now() )" );
				}
			}
		}
		
		return array( 'courseid' => $courseid );
	}
	
	public function coursereorder() {
		$this->bind( 'course', $this->segments[0] );
		$order = json_decode(stripslashes(urldecode($_POST['order'])));
		$chapter = 0;
		$section = 0;
		$segment = 0;
		foreach( $order as $c => $chapters ) {
			$this->bind( "chapter", str_replace( "li-chapter-", "", $chapters->id ) );
			$this->bind( "sort", $c );
			$this->write( "common::chapter order", "update common.chapter set sort = '|sort|' where id = '|chapter|' and course = '|course|'" );
			// set the sections
			foreach( $chapters->children as $s => $sections ) {
				$this->bind( "section", str_replace( "li-section-", "", $sections->id ) );
				$this->bind( "sectionsort", $s );
				$this->write( "common::section order", "update common.section set sort = '|sectionsort|' where id = '|section|' and chapter = '|chapter|'" );
				// set the segments
				foreach( $sections->children as $g => $segments ) {
					$this->bind( "segment", str_replace( "li-segment-", "", $segments->id ) );
					$this->bind( "segmentsort", $g );
					$this->write( "common::segment order", "update segment set sort = '|segmentsort|' where id = '|segment|' and section = '|section|'" );
				}
			}
		}
	}
	
	public function chapterinfo() {
		$this->bind( 'chapter', $this->segments[0] );
		return $this->read( "common::select * from common.chapter where id = '|chapter|'" );
	}

	public function sectioninfo() {
		$this->bind( 'section', $this->segments[0] );
		return array( $this->read( "common::select * from common.section where id = '|section|'" ), $this->read("common::select * from common.sectiondocs where section = '|section|'" ) );
	}
	
	public function segmentinfo() {
		$this->bind( 'segment', $this->segments[0] );
		return $this->read( "common::select * from common.segment where id = '|segment|'" );
	}
	
	public function updatesegment() {
		$this->bind( 'segment', $this->segments[0] );
		$this->bind( 'name', $_POST['name'] );
		$this->bind( 'params', $_POST['params'] );
		$this->bind( 'type', $_POST['type'] );
		return $this->write( "common::update segment", "update common.segment set name = '|name|', params = '|params|', type = '|type|' where id = '|segment|'" );
	}
	
	public function delsegment() {
		$this->bind( 'segment', $this->segments[0] );
		return $this->write( "common::delete segment", "delete from common.segment where id = '|segment|'" );
	}

	public function delsection() {
		$this->bind( 'section', $this->segments[0] );
		$this->write( "common::delete section", "delete from common.segment where section = '|section|'" );
		return $this->write( "common::delete section", "delete from common.section where id = '|section|'" );
	}

	public function delchapter() {
		$this->bind( 'chapter', $this->segments[0] );
		// get all of the sections
		$tmp = $this->read( "commonn::select * from common.section where chapter = '|chapter|'" );
		foreach( $tmp as $section ) {
			$this->bind( 'section', $section->id );
			$this->write( "common::delete segments", "delete from common.segment where section = '|section|'" );
		}
		$this->write( "common::delete sections", "delete from common.section where chapter = '|chapter|'" );
		return $this->write( "common::delete chapter", "delete from common.chapter where id = '|chapter|'" );
	}

	public function updatesection() {
		$this->bind( 'section', $this->segments[0] );
		$this->bind( 'name', $_POST['name'] );
		$this->bind( 'segment-name', $_POST['newsegmentname'] );
		$tmp = $this->write( "common::update section", "update common.section set name = '|name|' where id = '|section|'" );

		// add the docs if we have some
		if( isset($_POST['url'][0]) && strlen($_POST['url']) > 0 ) {
			// remove existing docs for this section
			$this->write( "common::remove docs", "delete from common.sectiondocs where section = '|section|'" );
			for( $i=0; $i<sizeof($_POST['url']); $i++ ) {
				// make sure we have some data
				if( strlen($_POST['url'][$i]) > 0 && strlen($_POST['title'][$i]) > 0 ) {
					$this->bind( 'dtype', $_POST['type'][$i] );
					$this->bind( 'dname', $_POST['title'][$i] );
					$this->bind( 'durl', $_POST['url'][$i] );
					$this->write( "common::add supporting docs", "insert into common.sectiondocs (section, type, name, path) values ( '|section|', '|dtype|', '|dname|', '|durl|')" );
				}
			}
		}

		if( $_POST['newsegment'] == 'y' && strlen($_POST['newsegmentname']) > 0 ) {
			$tmp2 = $this->write( "common::new segment", "insert into common.segment ( section, name, created ) values ( '|section|', '|segment-name|', now() )" );
			return array($tmp, $tmp2);
		}
		

		
		return $tmp;
	}

	public function updatechapter() {
		$this->bind( 'chapter', $this->segments[0] );
		$this->bind( 'name', $_POST['name'] );
		$this->bind( 'section-name', $_POST['newsectionname'] );
		$tmp = $this->write( "common::update chapter", "update common.chapter set name = '|name|' where id = '|chapter|'" );
		if( $_POST['newsection'] == 'y' && strlen($_POST['newsectionname']) > 0 ) {
			$tmp2 = $this->write( "common::new section", "insert into common.section (chapter, name, created) values ( '|chapter|', '|section-name|', now() )" );
			return array($tmp, $tmp2);
		}
		return $tmp;
	}
	
	public function newchapter() {
		$this->bind( 'library', $this->segments[0] );
		$this->bind( 'course', $this->segments[1] );
		$this->bind( 'name', $_POST['name'] );
		return $this->write( "common::new chapter", "insert into common.chapter ( course, name, created ) values ( '|course|', '|name|', now() )" );
	}
	
	public function addsupportingdoc() {
		$this->bind( 'section', $this->segments[0] );
	}

	public function searchCourse() {
		$this->bind( 'text', $this->segments[0] );
		$course['name'] = $this->read("select * from common.course where name like '%|text|%'");
		$course['description'] = $this->read("select * from common.course where description like '%|text|%'");
		return $course;
	}

	public function searchSection() {
		$this->bind( 'text', $this->segments[0] );
		$section['name'] = $this->read("select s.*, l.name as coursename, c.name as chapname, co.library as courselib ,co.id as courseid from common.section s left join common.chapter c on s.chapter = c.id left join common.course co on c.course = co.id left join common.library l on co.library = l.id where s.name like '%|text|%'");
		return $section;
	}

	public function searchTopic() {
		$this->bind( 'text', $this->segments[0] );
		$topic['name'] = $this->read("select se.*, s.name as secname, l.name as coursename, c.name as chapname, c.id as chapid, co.library as courselib ,co.id as courseid from common.segment se left join common.section s on se.section = s.id left join common.chapter c on s.chapter = c.id left join common.course co on c.course = co.id left join common.library l on co.library = l.id where se.name like '%|text|%'");
		return $topic;
	}

	public function getAnalytics() {
		$this->bind( 'staff', $this->segments[0] );
		return $this->read("select * from traininganalytics where staff = '|staff|'" );
	}

}

?>