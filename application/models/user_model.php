<?php
class User_model extends CI_Model {
 
    function __construct()
    {       
        parent::__construct();
    }
	
	function add($option){ // 배열로 입력정보를 받는다.
		$this->db->set('email', $option['email']);
		$this->db->set('password', $option['password']);
		$this->db->set('created', 'NOW()', FALSE);
		$this->db->insert('user');
		$result = $this->db->insert_id();
		return $result;
	}
	
	function getByEmail($option){
		$result = $this->db->get_where('user', array('email'=>$option['email']))->row();
		return $result;
	}
	
	function gets(){
		return $this->db->query("select * from user")->result();
	}
}