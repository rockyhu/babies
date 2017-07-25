<?php
namespace Home\Model;

use Think\Model;

/**
 * 会员退出登陆记录模型
 *
 * @author rockyhu
 *
 */
class UserLogoutModel extends Model
{

    /**
     * 获取会员是否存在登陆记录
     * @param number $userid 学员id
     */
    public function getOneUserLogout($userid)
    {
        $map['userid'] = $userid;
        return $this->where($map)->getField('userid') ? 1 : 0;
    }

    /**
     * 添加会员退出登陆记录
     * @param number $userid 学员id
     * @return boolean
     */
    public function addUserLogout($userid)
    {
        return $this->add(array(
            'userid' => $userid,
            'create' => NOW_TIME
        ));
    }

    /**
     * 删除会员退出记录
     * @param number $userid 学员id
     * @return boolean
     */
    public function removeUserLogout($userid)
    {
        $map['userid'] = $userid;
        return $this->where($map)->delete();
    }
}