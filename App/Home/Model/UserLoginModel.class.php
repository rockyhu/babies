<?php
namespace Home\Model;
use Think\Model;

class UserLoginModel extends Model{
	
	//自动验证
	protected $_validate = array();
	
	//自动完成
	protected $_auto = array();
	
	/**
	 * 添加登陆记录消息
	 * @param number $userid 会员id
	 * @return boolean
	 */
	public function addUserLogin($userid) {
		$data = array(
			'userid'=>$userid,
			'loginip'=>get_client_ip(1),
			'logintime'=>NOW_TIME,
			'loginlocation'=>getPositionCitywithIP()
		);
		$this->add($data);
		return 1;
	}	
	
	
	
}