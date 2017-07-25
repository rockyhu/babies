<?php
namespace Home\Model;
use Think\Model;

/**
 * 微信登陆扩展模型
 * @author rockyhu
 *
 */
class UserExtendModel extends Model{
	
	/**
	 * 用户初次登陆时保存微信信息和学员之间的关联
	 * @param string $userid 用户id
	 * @param string $wxid 微信id
	 * @param string $wxname 微信昵称
	 * @param string $wxface 微信头像
	 * @param string $wxtoken 微信token
	 * @return number
	 */
	public function addUserExtendLogin($userid, $wxid, $wxname, $wxface, $wxtoken) {
		$data = array(
			'userid'=>$userid,
			'wxid'=>$wxid,
			'wxname'=>$wxname,
			'wxface'=>$wxface,
			'wxtoken'=>$wxtoken
		);
		$id = $this->where("userid='{$userid}'")->getField('wxid');
		if(!$id) $this->add($data);
		return 1;
	}

    /**
     * 判断当前微信id对应的会员是否存在，存在则返回会员id，不存在返回0
     * @param string $wxid 微信id
     * @return Ambigous
     */
    public function studentExtendLogin($wxid)
    {
        $map['wxid'] = $wxid;
        return $this->where($map)->getField('studentid');
    }
	
	
}