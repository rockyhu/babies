<?php
namespace Home\Model;
use Think\Model;

/**
 * 建设奖与区域奖记录模型
 * @author rockyhu
 *
 */
class RecordModel extends Model{
	
	/**
	 * 
	 * @param string $areaauserid A区域会员id
	 * @param string $areabuserid B区域会员id
	 * @param string $rehouseuserid 安置人会员id
	 * @param string $jine 奖金
	 * @param string $floor 层碰奖层级
	 * @param string $type 记录类型，层奖 OR 量奖
	 * @return Ambigous <number, \Think\mixed, boolean, unknown, string>
	 */
	public function addUserRecord($areaauserid, $areabuserid, $rehouseuserid, $jine, $floor, $type='层奖') {
		$data = array(
			'areaauserid'=>$areaauserid,
			'areabuserid'=>$areabuserid,
			'rehouseuserid'=>$rehouseuserid,
		    'jine'=>$jine,
		    'floor'=>$floor,
			'type'=>$type,
			'create'=>NOW_TIME
		);
		$recordid = $this->add($data);
		return $recordid ? $recordid : 0;
	}
	
	/**
	 * 获取日结佣金记录明细
	 * @param string $userids 用户id
	 * @param string $time 时间戳，需要换算成当天的日期时间戳
	 */
	public function getUserDayRecord($userid, $time) {
	    $date = strtotime(date('Y-m-d', $time));
	    $recordlist = $this->field('id,areaauserid,areabuserid,rehouseuserid,jine,type,create')->where("rehouseuserid='{$userid}' AND FROM_UNIXTIME(`create`,'%Y%m%d')=FROM_UNIXTIME({$date},'%Y%m%d')")->select();
	    
	    $User = M('User');
	    foreach ($recordlist as $key=>$value) {
	        $recordlist[$key]['create'] = date('Y/m/d H:i:s', $value['create']);
	        $areaauser = $User->field('id,username,realname')->where("id='{$value['areaauserid']}'")->find();
	        $recordlist[$key]['areaauser'] = array(
	            'username'=>$areaauser['username'],
	            'realname'=>$areaauser['realname']
	        );
	        $arebauser = $User->field('id,username,realname')->where("id='{$value['areabuserid']}'")->find();
	        $recordlist[$key]['areabuser'] = array(
	            'username'=>$arebauser['username'],
	            'realname'=>$arebauser['realname']
	        );
	    }
	    return $recordlist;
	}
	
}