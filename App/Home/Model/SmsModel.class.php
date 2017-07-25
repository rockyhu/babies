<?php
namespace Home\Model;
use Think\Model;

class SmsModel extends Model{
	
	/**
	 * 添加短信验证码
	 * @param string $phone 手机号码
	 * @param string $activecode 短信验证码
	 * @return Ambigous <number, string>
	 */
	public function addSms($phone, $activecode) {
		$data = array(
			'phone'=>$phone,
			'activecode'=>$activecode,
			'create'=>NOW_TIME
		);
		if($this->create($data)) {
			$sid = $this->add($data);
			return $sid ? $sid : 0;
		}else{
			return $this->getError();
		}
	}
	
	/**
	 * 验证短信验证码是否正确
	 * @param string $phone 手机号码
	 * @param string $activecode 短信验证码
	 * @return Ambigous <number, string>
	 */
	public function checkSMS($phone, $activecode) {
	    $map = array(
	        'phone'=>$phone,
	        'activecode'=>$activecode
	    );
	    $state = $this->field('state')->where($map)->find();
        return ($state && $state['state'] ==0) ? $this->where($map)->setField('state',1) : 0;
	}
	
	/**
	 * 验证短信验证码是否存在，并且被验证过（用于修改手机号码时的验证）
	 * @param string $phone 手机号码
	 * @param string $activecode 短信验证码
	 * @return Ambigous <number, string>
	 */
	public function checkSMSisCheckOk($phone, $activecode) {
	    $map = array(
	        'phone'=>$phone,
	        'activecode'=>$activecode
	    );
	    $state = $this->field('state')->where($map)->find();
        return ($state && $state['state'] ==1) ? true : false;
	}
	
	/**
	 * 验证码安全机制 - 验证码创建时间超过5分钟，就销毁激活码
	 */
	public function checkSMSexpires() {
		$list = $this->field('id,create')->where("state=0")->select();
		$flag = 'false';
		foreach ($list as $key=>$value) {
			//验证码创建时间超过5分钟，就销毁激活码
			if(NOW_TIME - $value['create'] > 300) {
				$state = $this->where("id='{$value['id']}'")->setField('state', 1);
				if($state) $flag = 'true';
			}
		}
		return $flag;
	}
	
	
}