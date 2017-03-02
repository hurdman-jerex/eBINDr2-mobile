<?php

class sales_API extends hapi {

    public function callsbychoice() {
        echo '<pre>'.print_r( $this->segments, true ) .'</pre>';
        //return $this->read("[e2mobile/api/search/businessname]", 0);
    }

}

?>