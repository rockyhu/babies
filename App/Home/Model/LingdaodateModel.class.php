<?php
namespace Home\Model;
use Think\Model;

/**
 * 领导奖日结算记录
 * @author rockyhu
 *
 */
class LingdaodateModel extends Model{
	
	/**
	 * 添加领导奖日结算记录
	 */
	public function addLingdaodate() {
	    $today = strtotime(date('Y-m-d', time()));
		$data = array(
			'lingdaodate'=>$today,
		);
		$recordid = $this->add($data);
		return $recordid ? $recordid : 0;
	}
	
	/**
	 * 今日的领导奖是否结算过
	 */
	public function checkLingdaodate() {
	    $today = strtotime(date('Y-m-d', time()));
	    $count = $this->where("lingdaodate='{$today}'")->count();
	    return $count>0 ? false : true;
	}
	
}