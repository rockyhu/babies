<?php
namespace Home\Model;
use Think\Model;

/**
 * 站内信模型
 * @author rockyhu
 *
 */
class MessageModel extends Model{
	
	//自动验证
	protected $_validate = array();
	
	//自动完成
	protected $_auto = array(
	    array('create','time',self::MODEL_INSERT,'function')
	);
	
	/**
	 * 获取前6条站内信
	 * @param string $userid 用户id
	 */
	public function getNewMessage($userid) {
		$newList = $this->field('id,fromuserid,touserid,content,state,create')->where("sid=0 AND (fromuserid='{$userid}' OR touserid='{$userid}')")->order(array('create'=>'DESC'))->limit(6)->select();
		foreach ($newList as $key=>$value) {
			$newList[$key]['create'] = date('Y/m/d H:i:s', $value['create']);
            //根据消息的发件人fromuser和消息最后一次回复的发件人fromuser是否一致,如果一致说明消息未读;如果不一致,说明消息已读。
            $state = $this->getLastReplyState($value['fromuserid'], $value['id']);
            $newList[$key]['stateText'] = $state==2 ? '<span class="badge bg-green-gradient">已处理</span>' : ($state==1 ? '<span class="badge bg-red-gradient">待处理</span>' : '<span class="badge bg-red-gradient">有新消息</span>');
		}
		return $newList;
	}
	
	/**
	 * 获取站内信列表
	 * @param string $userid 用户id
	 * @return array
	 */
	public function getUserMessage($userid) {
		$messageList = $this->field('id,fromuserid,touserid,content,state,create')->where("sid=0 AND (fromuserid='{$userid}' OR touserid='{$userid}')")->order(array('create'=>'DESC'))->select();
		foreach ($messageList as $key=>$value) {
			$messageList[$key]['create'] = date('Y/m/d H:i:s', $value['create']);
            //根据消息的发件人fromuser和消息最后一次回复的发件人fromuser是否一致,如果一致说明消息未读;如果不一致,说明消息已读。
            $state = $this->getLastReplyState($value['fromuserid'], $value['id']);
            $messageList[$key]['stateText'] = $state==2 ? '<span class="badge bg-green-gradient">已处理</span>' : ($state==1 ? '<span class="badge bg-red-gradient">待处理</span>' : '<span class="badge bg-red-gradient">有新消息</span>');
		}
		return $messageList;
	}
	
	/**
	 * 获取会员一条站内信
	 * @param string $messageid 站内信id
	 * @param string $userid 用户id
	 * @return string
	 */
	public function getOneMessage($messageid, $userid) {
		$messageList = $this->field('id,fromuserid,touserid,content,state,create')->where("id='{$messageid}' OR sid='{$messageid}' AND (fromuserid='{$userid}' OR touserid='{$userid}')")->order(array('create'=>'ASC'))->select();
		$User = M('User');
		foreach ($messageList as $key=>$value) {
			$messageList[$key]['create'] = date('Y/m/d H:i:s', $value['create']);
			//收件人查询
			$messageList[$key]['touser'] = $value['touserid'] ? $User->where("id='{$value['touserid']}'")->getField('realname') : '管理员';
			//发件人查询
			$messageList[$key]['fromuser'] = $value['fromuserid'] ? $User->where("id='{$value['fromuserid']}'")->getField('realname') : '管理员';
			/**
			 * 分析：
			 * 1.当第一条消息是公司发送的时候，第一条消息就向左靠齐；否则，向右靠齐；
			 */
			//更新当前短信的状态
			$this->setMessageState($messageList[0]['id']);
		}
		//print_r($messageList);
		return $messageList;
	}
	
	/**
	 * 发送站内信
	 * @param string $touserid 站内信写给的人
	 * @param string $fromuserid 站内信写信的人
	 * @param string $content 站内信内容
	 * @param string $sid 站内信父id
	 * @return string
	 */
	public function sendMessage($touserid, $fromuserid, $content, $sid) {
		$data = array(
			'touserid'=>$touserid,
			'fromuserid'=>$fromuserid,
			'content'=>$content,
			'sid'=>$sid
		);
		if($this->create($data)){
			$messageid = $this->add();
			return $messageid ? $messageid : 0;
		}else{
			return $this->getError();
		}
	}

    /**
     * 获取消息是未读还是已读
     * @param $fromuserid 发件人
     * @param int $sid
     * @return bool
     */
    private function getLastReplyState($fromuserid, $sid = 0) {
        $lastReplyOne = $this->field('fromuserid')->where("sid='{$sid}'")->order(array('create'=>'DESC'))->find();
        if($lastReplyOne) {
            if($lastReplyOne['fromuserid'] == $fromuserid) {
                return 3;//未读,您有新消息
            }else {
                return 2;//已读
            }
        }else {
            return 1;//未读
        }
    }
	
	/**
	 * 设置站内信状态
	 * @param string $messageid 站内信id
	 * @return Ambigous <boolean, unknown>
	 */
	public function setMessageState($messageid) {
		return $this->where("id='{$messageid}' OR sid='{$messageid}'")->setField('state', 1);
	}
	
	/**
	 * 获取未读的站内信
	 * @param string $userid 用户id
	 */
	public function getNoReadMessage($userid) {
		return $this->where("(touserid='{$userid}' OR fromuserid='{$userid}') AND state=0")->count();
	}
	
}