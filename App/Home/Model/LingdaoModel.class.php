<?php
namespace Home\Model;
use Think\Model;

/**
 * 领导奖相关量奖记录模型
 * @author rockyhu
 *
 */
class LingdaoModel extends Model{
	
	/**
	 * 添加领导奖相关记录
	 * @param string $jine 量奖金额
	 * @param string $aboutid 量奖的所属人id
	 * @param string $userid 领导奖用户id
	 * @param string $type 类型
	 */
	public function addUserLingdaoRecord($jine, $rate, $aboutid, $userid, $type='量奖') {
		$data = array(
			'jine'=>$jine,
		    'rate'=>$rate,
			'aboutid'=>$aboutid,
		    'userid'=>$userid,
		    'type'=>$type,
			'create'=>NOW_TIME
		);
		$recordid = $this->add($data);
		return $recordid ? $recordid : 0;
	}
	
	/**
	 * 获取领导奖量奖记录明细
	 * @param string $userid 用户id
	 * @param string $time 时间戳，需要换算成当天的日期时间戳
	 */
	public function getUserDayLingdaoRecord($userid, $time) {
	    $date = strtotime(date('Y-m-d', $time));
	    $list = $this->field('jine,rate,aboutid,userid,type,create')->where("userid='{$userid}' AND FROM_UNIXTIME(`create`,'%Y%m%d')=FROM_UNIXTIME({$date},'%Y%m%d')")->select();
	    $User = M('User');
	    foreach ($list as $key=>$value) {
	        $list[$key]['create'] = date('Y/m/d H:i:s', $value['create']);
	        $aboutuser = $User->field('username,realname')->where("id='{$value['aboutid']}'")->find();
	        $list[$key]['aboutuser'] = array(
	            'username'=>$aboutuser['username'],
	            'realname'=>$aboutuser['realname']
	        );
	    }
	    return $list;
	}
	
}