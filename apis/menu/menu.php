<?php

class menu_API extends hapi{

    public function main(){
        return $this->read( '[e2m main menu]' );
    }
}