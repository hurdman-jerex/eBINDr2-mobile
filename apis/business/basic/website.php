<?php

class website_API extends hapi {

    public function listing() {
        $this->bind('bid', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/website]", 0);
    }
    
    public function get(){
        $this->bind('bid', $this->segments[0]);
        $this->bind('wid', $this->segments[1]);
        
        return $this->read("[e2mobile/api/business/basic/website/get]", 0);
    }

    public function add() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('url', $_POST['url']);
        $this->bind('label', $_POST['label']);
        $this->bind('main', $_POST['main']);
        $this->bind('report', $_POST['report']);
        $this->bind('cguide', $_POST['cguide']);
        $this->bind('facebook', $_POST['facebook']);
        $this->bind('twitter', $_POST['twitter']);
        $this->bind('inactive', $_POST['inactive']);
        $this->bind('bbbonline', $_POST['bbbonline']);
        $this->bind('ratingadvertised', $_POST['ratingadvertised']);
        $this->bind('displayurl', $_POST['displayurl']);
        
        return $this->write("website:add","[e2mobile/api/business/basic/website/add]");
    }

    public function edit() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('wid', $this->segments[1]);
        
        $this->bind('url', $_POST['url']);
        $this->bind('label', $_POST['label']);
        $this->bind('main', $_POST['main']);
        $this->bind('report', $_POST['report']);
        $this->bind('cguide', $_POST['cguide']);
        $this->bind('facebook', $_POST['facebook']);
        $this->bind('twitter', $_POST['twitter']);
        $this->bind('inactive', $_POST['inactive']);
        $this->bind('bbbonline', $_POST['bbbonline']);
        $this->bind('ratingadvertised', $_POST['ratingadvertised']);
        $this->bind('displayurl', $_POST['displayurl']);
        
        return $this->write("website:edit","[e2mobile/api/business/basic/website/edit]");
        
    }

}

?>