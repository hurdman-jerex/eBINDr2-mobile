<?php

class dba_API extends hapi {

    public function listing() {
        $this->bind('bid', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/address/dbaname]", 0);
    }
    
    public function get() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('pid', $this->segments[1]);
        
        //return $this->read("[e2mobile/api/business/basic/person/get]", 0);
    }
    
    public function add() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('prename',$_POST['prename']);
        $this->bind('firstname',$_POST['firstname']);
        $this->bind('lastname',$_POST['lastname']);
        $this->bind('postname',$_POST['postname']);
        $this->bind('title',$_POST['title']);
        $this->bind('mailcode',$_POST['mailcode']);
        $this->bind('main',$_POST['main']);
        $this->bind('complaint',$_POST['complaint']);
        $this->bind('servicecontact',$_POST['servicecontact']);
        $this->bind('billing',$_POST['billing']);
        $this->bind('salescontact',$_POST['salescontact']);
        $this->bind('report',$_POST['report']);
        $this->bind('complaintassign',$_POST['complaintassign']);
        
        //return $this->write('person:add','[e2mobile/api/business/basic/person/add]');
    }

    public function edit() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('pid', $this->segments[1]);
        
        $this->bind('prename',$_POST['prename']);
        $this->bind('firstname',$_POST['firstname']);
        $this->bind('lastname',$_POST['lastname']);
        $this->bind('postname',$_POST['postname']);
        $this->bind('title',$_POST['title']);
        $this->bind('mailcode',$_POST['mailcode']);
        $this->bind('main',$_POST['main']);
        $this->bind('complaint',$_POST['complaint']);
        $this->bind('servicecontact',$_POST['servicecontact']);
        $this->bind('billing',$_POST['billing']);
        $this->bind('salescontact',$_POST['salescontact']);
        $this->bind('report',$_POST['report']);
        $this->bind('complaintassign',$_POST['complaintassign']);
        
       // return $this->write('person:edit','[e2mobile/api/business/basic/person/edit]');
    }

}

?>