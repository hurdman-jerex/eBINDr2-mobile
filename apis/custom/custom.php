<?php

class custom_API extends hapi{

    public function styles(){
        return $this->read( '[e2m custom styles]' );
    }

    public function scripts(){
        return $this->read( '[e2m custom scripts]' );
    }
}