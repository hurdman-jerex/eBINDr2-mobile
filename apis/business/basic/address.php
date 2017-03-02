<?php

class address_API extends hapi {

    public function listing() {
        $this->bind('bid', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/address]", 0);
    }
    
    public function get(){
        $this->bind('bid', $this->segments[0]);
        $this->bind('aid', $this->segments[1]);
        return $this->read("[e2mobile/api/business/basic/address/get]", 0);
    }
		
		public function getPrimary() {
			$this->bind('bid', $this->segments[0]);
			return $this->read("select * from address where main = 'y' and bid = '|bid|'", 0);
		}

    public function add() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('street1', $_POST['street1']);
        $this->bind('street2', $_POST['street2']);
        $this->bind('postalcode', $_POST['postalcode']);
        $this->bind('county', $_POST['county']);
        $this->bind('city', $_POST['city']);
        $this->bind('stateprov', $_POST['stateprov']);
        $this->bind('label', $_POST['label']);
        $this->bind('locationid', $_POST['locationid']);
        $this->bind('locationclosed', $_POST['locationclosed']);
        $this->bind('main', $_POST['main']);
        $this->bind('billing', $_POST['billing']);
        $this->bind('mailing', $_POST['mailing']);
        $this->bind('complaint', $_POST['complaint']);
        $this->bind('report', $_POST['report']);
        $this->bind('addloc', $_POST['addloc']);
        $this->bind('cguide', $_POST['cguide']);
        $this->bind('dbaname', $_POST['dbaname']);
        $this->bind('complaintcontact', $_POST['complaintcontact']);
        $this->bind('AssignAddress', $_POST['AssignAddress']);
        $this->bind('AssignPhone', $_POST['AssignPhone']);
        $this->bind('AssignFax', $_POST['AssignFax']);

        $this->write("", "[e2mobile/api/business/basic/address/trimpostalcode]");
        return $this->write("address:add", "[e2mobile/api/business/basic/address/add]");
    }

    public function edit() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('aid', $this->segments[1]);
        $this->bind('street1', $_POST['street1']);
        $this->bind('street2', $_POST['street2']);
        $this->bind('postalcode', $_POST['postalcode']);
        $this->bind('county', $_POST['county']);
        $this->bind('city', $_POST['city']);
        $this->bind('stateprov', $_POST['stateprov']);
        $this->bind('label', $_POST['label']);
        $this->bind('locationid', $_POST['locationid']);
        $this->bind('locationclosed', $_POST['locationclosed']);
        $this->bind('main', $_POST['main']);
        $this->bind('billing', $_POST['billing']);
        $this->bind('mailing', $_POST['mailing']);
        $this->bind('complaint', $_POST['complaint']);
        $this->bind('report', $_POST['report']);
        $this->bind('addloc', $_POST['addloc']);
        $this->bind('cguide', $_POST['cguide']);
        $this->bind('dbaname', $_POST['dbaname']);
        $this->bind('complaintcontact', $_POST['complaintcontact']);
        $this->bind('AssignAddress', $_POST['AssignAddress']);
        $this->bind('AssignPhone', $_POST['AssignPhone']);
        $this->bind('AssignFax', $_POST['AssignFax']);

        return $this->write("address:add", "[e2mobile/api/business/basic/address/edit]");
    }

    public function stateprov() {
        $this->bind('country', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/address/stateprov]", 0);
    }

    public function dbaname() {
        $this->bind('bid', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/address/dbaname]", 0);
    }

    public function assigncontact() {
        $this->bind('bid', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/address/assigncontact]", 0);
    }

    public function assignphone() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('key2', $this->segments[1]);
        return $this->read("[e2mobile/api/business/basic/address/assignphone]", 0);
    }

    public function assignfax() {
        $this->bind('bid', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/address/assignfax]", 0);
    }

    public function complaint() {
        $this->bind('bid', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/address/complaint]", 0);
    }
    
    public function get_assigncontact() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('aid', $this->segments[1]);
        return $this->read("[e2mobile/api/business/basic/address/get_assigncontact]", 0);
    }
    
    public function get_assignphone() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('aid', $this->segments[1]);
        return $this->read("[e2mobile/api/business/basic/address/get_assignphone]", 0);
    }
    
    public function get_assignfax() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('aid', $this->segments[1]);
        return $this->read("[e2mobile/api/business/basic/address/get_assignfax]", 0);
    }

}

?>