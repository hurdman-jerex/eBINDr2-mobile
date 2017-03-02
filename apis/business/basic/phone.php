<?php

class phone_API extends hapi {

    public function get() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('pid', $this->segments[1]);
        return $this->read("[e2mobile/api/business/basic/phone/get]", 0);
    }

    public function listing() {
        $this->bind('bid', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/phone]", 0);
    }

    public function add() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('number', $_POST['number']);
        $this->bind('main', $_POST['main']);
        $this->bind('report', $_POST['report']);
        $this->bind('label', $_POST['label']);
        $this->bind('location', $_POST['location']);
        $this->bind('disconnected', $_POST['disconnected']);
        $this->bind('SMS', $_POST['SMS']);
        $this->bind('eQuote', $_POST['eQuote']);
        $this->bind('CellPhone', $_POST['CellPhone']);

        return $this->write("phone:add", "[e2mobile/api/business/basic/phone/add]");
    }

    public function edit() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('pid', $this->segments[1]);
        $this->bind('number', $_POST['number']);
        $this->bind('main', $_POST['main']);
        $this->bind('report', $_POST['report']);
        $this->bind('label', $_POST['label']);
        $this->bind('location', $_POST['location']);
        $this->bind('disconnected', $_POST['disconnected']);
        $this->bind('SMS', $_POST['SMS']);
        $this->bind('eQuote', $_POST['eQuote']);
        $this->bind('CellPhone', $_POST['CellPhone']);

        return $this->write("phone:edit", "[e2mobile/api/business/basic/phone/edit]");
    }

}

?>