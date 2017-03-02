<?php

class dba2_API extends hapi {

    public function listing() {
        $this->bind('bid', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/address/dbaname]", 0);
    }
    
    public function get() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('did', $this->segments[1]);
        
        return $this->read("[e2mobile/api/business/basic/dba/get]", 0);
    }

    public function edit() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('did', $this->segments[1]);
        
        $this->bind('name',$_POST['name']);
        $this->bind('label',$_POST['label']);
        $this->bind('main',$_POST['main']);
        $this->bind('report',$_POST['report']);
        $this->bind('legal',$_POST['legal']);
        $this->bind('cguide',$_POST['cguide']);
        $this->bind('billing',$_POST['billing']);
        $this->bind('rankid',$_POST['RankID']);
        $this->bind('vrsname',$_POST['VRSname']);
        $this->bind('sortby',$_POST['SortBy']);
        
       return $this->write('dba:edit','[e2mobile/api/business/basic/dba/edit]');
    }
    
    public function add() {
        $this->bind('bid', $this->segments[0]);
        
        $this->bind('name',$_POST['name']);
        $this->bind('label',$_POST['label']);
        $this->bind('main',$_POST['main']);
        $this->bind('report',$_POST['report']);
        $this->bind('legal',$_POST['legal']);
        $this->bind('cguide',$_POST['cguide']);
        $this->bind('billing',$_POST['billing']);
        $this->bind('rankid',$_POST['RankID']);
        $this->bind('vrsname',$_POST['VRSname']);
        $this->bind('sortby',$_POST['SortBy']);
        
       return $this->write('dba:add','[e2mobile/api/business/basic/dba/add]');
    }

}

?>