<?php
namespace Home\Model;
use Think\Model;

/**
 * 促销升级模型
 * Class UserAgentRecordModel
 * @package Home\Model
 */
class UserAgentRecordModel extends Model{

    /**
     * 促销升级记录
     * @param $beforeAgentid 升级前级别
     * @param $afterAgentid 升级后级别
     * @param $text 升级备注
     * @param $weekid 升级时间所在的结算周
     * @param $userid 升级会员
     * @return int
     */
	public function addUserAgentRecordUpgrade($beforeAgentid, $afterAgentid, $text, $weekid, $userid) {
		$data = array(
			'userid'=>$userid,
			'beforeAgentid'=>$beforeAgentid,
            'afterAgentid'=>$afterAgentid,
            'text'=>$text,
            'weekid'=>$weekid,
			'create'=>NOW_TIME
		);
		$this->add($data);
		return 1;
	}	
	
	
	
}