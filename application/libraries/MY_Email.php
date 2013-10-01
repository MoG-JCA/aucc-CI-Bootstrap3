<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * /application/config/config.php 에 dev_receive_email 설정이 존재한다면 이 값으로 모든 이메일을 전송하는 방법이다. 
 * 
 * 이메일은 한번 발송되면 주어담을 수 없는 커뮤니케이션 도구이기 때문에 개발 시에 상당히 까다로운 시스템이다. 
 * 
 * 개발환경에서는 무조건 특정 이메일로 전송되게 한다면 보다 안심하고 개발 할 수 있을 것이다.
 */
class MY_Email extends CI_Email {
    public function to($to)
    {
        $this->ci = &get_instance();
        $_to = $this->ci->config->item('dev_receive_email');
        $to = $_to ? $_to : $to;
        return parent::to($to);
    }
}