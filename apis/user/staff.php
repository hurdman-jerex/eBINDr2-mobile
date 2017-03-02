<?php

class staff_API extends hapi {

    public function get() {
        return $this->read("[e2mobile/api/user/staff/get]");
    }

}

?>