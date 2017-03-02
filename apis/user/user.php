<?php

class user_API extends hapi {

	public function login() {
		$this->bind( 'username', $this->segments[0] );
		$this->bind( 'db', LOCAL_DB );
		$this->bind( 'REQUEST_URI', $_POST['REQUEST_URI'] );
		$this->bind( 'REMOTE_ADDR', $_POST['REMOTE_ADDR'] );
		$this->bind( 'password', $_POST['password'] );
		return $this->read("[e2mobile/api/user/login/staff]");
	}

	public function test_login() {
		$this->bind( 'username', $this->segments[0] );
		$this->bind( 'db', LOCAL_DB );
		$this->bind( 'REQUEST_URI', $_POST['REQUEST_URI'] );
		$this->bind( 'REMOTE_ADDR', $_POST['REMOTE_ADDR'] );
		$this->bind( 'password', $_POST['password'] );
		
		return $this->read("[e2mobile/api/user/login/staff_test]");
	}
	
	public function logout() {
		$this->bind( 'username', $this->segments[0] );
		$this->bind( 'db', LOCAL_DB );
		$this->bind( 'REQUEST_URI', $_POST['REQUEST_URI'] );
		$this->bind( 'REMOTE_ADDR', $_POST['REMOTE_ADDR'] );
		return $this->read("[e2mobile/api/user/logout/staff]");
	}

	public function log() {
		$this->bind( 'username', $this->segments[0] );
		$this->bind( 'db', LOCAL_DB );
		$this->bind( 'REQUEST_URI', $_POST['REQUEST_URI'] );
		$this->bind( 'REMOTE_ADDR', $_POST['REMOTE_ADDR'] );
		$this->bind( 'password', $_POST['password'] );
		return $this->read("[e2mobile/api/user/log/staff]");
	}

}

?>