<?php

class menu_API extends hapi{

    public function main(){
        $this->bind( 'staff',  $this->segments[0] );
        $menu = $this->read( '[e2m main menu]' );

        //echo '<pre>'.print_r( $this->segments, true ).'</pre>';

        foreach( $menu[0] as $text => $link ){
            // resolve all mergecode e.g. [mergecode name]
            $mergecode = next_merge($link);
            do {
                if (!$mergecode) break;

                $content = $this->read($mergecode[0] , 0);
                $link = str_replace($mergecode, $content[0], $link);
            }while($mergecode = next_merge($link));

            $menu[0][$text] = $link;
        }

        return $menu;
    }
}

if( ! function_exists( 'next_merge' ) ){
    // get next mergecode
    function next_merge($mytext){
        preg_match("/\[[^]]*\]/",  $mytext, $returned);
        return $returned;
    }
}