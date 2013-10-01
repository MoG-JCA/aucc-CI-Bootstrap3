<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Auth extends MY_controller {
    function __construct()
    {       
        parent::__construct();
    }
	function login(){
		$this->_head();
		$this->load->helper('url');
		$this->load->view('login',array('returnURL'=>$this->input->get('returnURL')));
		$this->_footer();
	}
	
	function logout(){
		$this->session->sess_destroy();
		$this->load->helper('url');
		redirect('/topic');
	}
	
	function authentication(){
		$this->load->model('user_model');
		$user = $this->user_model->getByEmail(array('email'=>$this->input->post('email')));
		
		if(
			$this->input->post('email') == $user->email &&
			$this->input->post('password') == base64_decode($user->password)
		){
			$this->session->set_userdata('is_login', TRUE);
			$this->load->helper('url');
			$returnURL = $this->input->get('returnURL');

			redirect($returnURL ? $returnURL : '/topic');
		} else {
			$this->session->set_flashdata('message', '로그인에 실패 했습니다.');
			$this->load->helper('url');
			redirect('/auth/login');
		}
	}
	
	function register(){
		$this->_head();
		
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('email', '이메일 주소', 'required|valid_email|is_unique[user.email]');
		$this->form_validation->set_rules('nickname', '닉네임', 'required|min_length[5]|max_length[20]');
		$this->form_validation->set_rules('password', '비밀번호', 'required|min_length[6]|max_length[30]|matches[re_password]');
		$this->form_validation->set_rules('re_password', '비밀번호 확인', 'required');
		
		if($this->form_validation->run() === FALSE){
			$this->load->view('register');
		} else {
			//php 5.3.7 ~ 5.5 버전에서 헬퍼로 사용, 만약 php가 5.5버전 이상이면 php의 기본 API인 password_hash()를 사용한다.
			/*
			if(!function_exists('password_hash')){
				$this->load->helper('password');
			}
			$hash = password_hash($this->input->post('password'), PASSWORD_BCRYPT);
			*/
			$hash = base64_encode($this->input->post('password'));
			
			$this->load->model('user_model');
			$this->user_model->add(array(
				'email'=>$this->input->post('email'),
				'password'=>$hash,
				'nickname'=>$this->input->post('nickname')
			));
			
			$this->session->set_flashdata('message','회원가입에 성공했습니다.');
			$this->load->helper('url');
			redirect('/topic');
		}
		
		$this->_footer();
	}
}