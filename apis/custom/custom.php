<?php

class custom_API extends hapi{

    public function styles(){
        $this->dbconnect();
        return db::resolve_pipes(db::getquery( "e2m custom styles" ));
        //echo '<pre>'.print_r( $results, true ).'</pre>';
        //return $this->read( '[e2m custom styles]' );
    }

    public function scripts(){
        $this->dbconnect();
        return db::resolve_pipes(db::getquery( "e2m custom scripts" ));
    }
}