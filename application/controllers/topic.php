<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Topic extends MY_controller {
    function __construct()
    {       
        parent::__construct();
        $this->load->database();
        $this->load->model('topic_model');
    }
    function index(){        
        $this->_head(); //MY_controller 에 정의됨
        $this->_sidebar(); //MY_controller 에 정의됨
        $this->load->view('main');
        
        $this->_footer(); //MY_controller 에 정의됨
    }
    function get($id){        
        $this->_head(); 
		$this->_sidebar();
        $topic = $this->topic_model->get($id);
		$this->load->helper(array('url','HTML','korean'));
        $this->load->view('get', array('topic'=>$topic));
        
        $this->_footer();
    }
	function add(){
		//로그인 필요
		// 로그인이 되어 있지 않다면 로그인 페이지로 리다이렉션
		
		if(!$this->session->userdata('is_login')){
			$this->load->helper('url');
			redirect('/auth/login?returnURL='.rawurlencode(site_url('/topic/add')));
		}
		
        $this->_head();
		$this->_sidebar();
		$this->load->library('form_validation');
		$this->form_validation->set_rules('title','제목','required');
		$this->form_validation->set_rules('description','본문','required');
		if ($this->form_validation->run() == FALSE)
		{
			$this->load->view('add');
		}
		else
		{
			$topic_id = $this->topic_model->add($this->input->post('title'), $this->input->post('description'));
			//등록된 글을 회원들에게 메일 발송을 하기위해 데이터를 가져옴.
			$this->load->model('user_model');
			$users = $this->user_model->gets();
			
			// Batch Queue에 notify_email_add_topic 추가
     	   	$this->load->model('batch_model');
        	$this->batch_model->add(array('job_name'=>'notify_email_add_topic', 'context'=>json_encode(array('topic_id'=>$topic_id))));
			/*
			 * 개개인 별로 메일을 발송하는 소스
			$this->load->library('email');
			// 전송할 데이터가 html 문서임을 옵션으로 설정
        	$this->email->initialize(array('mailtype'=>'html'));
			$this->load->helper('url');
			foreach($users as $user){
	            // 송신자의 이메일과 이름 정보
	            $this->email->from('master@domain.com', 'master');            
	            // 이메이 제목
	            $this->email->subject('글이 발행 됐습니다.');
	            // 이메일 본문
	            $this->email->message('<a href="'.site_url('/topic/get/').$topic_id.'">'.$this->input->post('title').'</a>');
	            // 이메일 수신자.
	            $this->email->to($user->email);
	            // 이메일 발송
	            $this->email->send();
        	}
			*/
			$this->cache->delete('topics'); // 부분 캐시 무효화 하여 추가된 리스트 바로 반영
			$this->load->helper('url');
			redirect('/topic/get/'.$topic_id);
		}
		
        
        $this->_footer();
	}
	function upload_receive_from_ck(){
		// 사용자가 업로드 한 파일을 /static/user/ 디렉토리에 저장한다.
		$config['upload_path'] = './static/user';
		// git,jpg,png 파일만 업로드를 허용한다.
		$config['allowed_types'] = 'gif|jpg|png';
		// 허용되는 파일의 최대 사이즈
		$config['max_size'] = '100';
		// 이미지인 경우 허용되는 최대 폭
		$config['max_width']  = '1024';
		// 이미지인 경우 허용되는 최대 높이
		$config['max_height']  = '768';
		$this->load->library('upload', $config);
		
		if ( ! $this->upload->do_upload("upload"))
		{
			echo $this->upload->display_errors();
			echo "<script>alert('업로드에 실패했습니다. ".$this->upload->display_errors('','')."')</script>"; // display_errors('','')->경고창에 html태그 제거
		}	
		else
		{
			$CKEditorFuncNum = $this->input->get('CKEditorFuncNum'); // CKEditor는 get방식을 기본을 진행하기 때문에 get로 해당 값을 받는다.
			$data = $this->upload->data();
			$filename = $data['file_name'];
			
			$url = '/static/user/'.$filename;
			
			echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction('".$CKEditorFuncNum."', '".$url."', '전송에 성공했습니다.')</script>";
		}			
	}	
	
	function upload_receive(){
		// 사용자가 업로드 한 파일을 /static/user/ 디렉토리에 저장한다.
		$config['upload_path'] = './static/user';
		// git,jpg,png 파일만 업로드를 허용한다.
		$config['allowed_types'] = 'gif|jpg|png';
		// 허용되는 파일의 최대 사이즈
		$config['max_size'] = '100';
		// 이미지인 경우 허용되는 최대 폭
		$config['max_width']  = '1024';
		// 이미지인 경우 허용되는 최대 높이
		$config['max_height']  = '768';
		$this->load->library('upload', $config);
		
		if ( ! $this->upload->do_upload("user_upload_file"))
		{
			
			echo $this->upload->display_errors();
		}	
		else
		{
			$data = array('upload_data' => $this->upload->data());
			
			echo "성공";
			var_dump($data);
		}			
	}
	
	function upload_form(){
		$this->_head();
		$this->_sidebar();
		$this->load->view('upload_form');
		$this->_footer();
	}
	
	function delete(){
	    $topic_id = $this->input->post('topic_id');
	    $this->_require_login(site_url('/topic/get/'.$topic_id)); //_require_login()를 사용하기위해 MY_controller에 포함시킴
	    $this->load->model('topic_model');
	    $this->topic_model->delete($topic_id);
		$this->cache->delete('topics'); // 부분 캐시 무효화하여 삭제된 리스트 바로 반영
	    redirect('/topic');
	}
	
}
?>