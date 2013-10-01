<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * 사용자 경험과 작업의 효율성을 위하여 본 파일은 서버의 cron에서 실행할 수 있도록 한다.
 * crontab 편집화면에서 아래의 내용을 입력한다.
 * (날짜/시간부분 생략) php /home/aucc/public_html/index.php cli/batch process > /home/aucc/public_html/application/logs/batch.access.log 2> /var/www/lecture/application/logs/batch.error.log
 */
class Batch extends MY_Controller {
    function __construct(){
        parent::__construct();
    }
    function process(){
        $this->load->model('batch_model');
        $queue = $this->batch_model->gets();
        foreach($queue as $job){
            switch($job->job_name){
                case 'notify_email_add_topic':
                    $context = json_decode($job->context);
                    $this->load->model('topic_model');
                    $topic = $this->topic_model->get($context->topic_id);
                    $this->load->model('user_model');
                    $users = $this->user_model->gets();     
                    $this->load->library('email');
                    $this->email->initialize(array('mailtype'=>'html'));
                    foreach($users as $user){
                        $this->email->from('master@domain.com', 'master');
                        $this->email->to($user->email);
                        $this->email->subject($topic->title);
                        $this->email->message($topic->description);
                        $this->email->send();
                        echo "{$user->email}로 메일 전송을 성공 했습니다.\n";
                    }
                    $this->batch_model->delete(array('id'=>$job->id));
                    break;
            }
        }
 
    }
}
