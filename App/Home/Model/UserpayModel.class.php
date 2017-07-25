<?php
namespace Home\Model;
use Think\Model;

/**
 * 账户充值模型
 * @author rockyhu
 *
 */
class UserpayModel extends Model{

    /**
     * 添加充值记录
     * @param $amount 充值金额
     * @param $banqueid 账户id
     * @param $paycode 支付密码
     * @param $info 充值备注
     * @param $userid 用户id
     * @return int|mixed|string
     */
	public function setUserPay($amount, $banqueid, $paycode, $info, $userid) {
	    //1.先判断支付密码是否正确,然后获取账户的信息
        if(D('User')->checkPaycode($userid, $paycode)) {
            //获取账户信息
            $banqueinfo = M('Banque')->field('id,name,bankno,bankname')->where("id='{$banqueid}'")->find();
            $data = array(
                'amount'=>$amount,
                'type'=>'汇款充值',
                'banque'=>!empty($banqueinfo) ? serialize($banqueinfo) : '',
                'info'=>$info,
                'userid'=>$userid,
                'create'=>NOW_TIME
            );
            if($this->create($data)){
                $userpayid = $this->add();
                return $userpayid ? $userpayid : 0;
            }else{
                return $this->getError();
            }
        }
    }

    /**
     * 获取当前用户充值列表
     * @param $userid 用户id
     */
    public function getUserPay($state, $type, $_date, $userid) {
        $map['userid'] = $userid;
        if(isNumeric($state) && $state >= 0) {
            $map['state'] = $state;
        }
        if(!empty($type)) {
            $map['type'] = $type;
        }
        if($_date) {
            $dateArr = explode(' - ', $_date);
            $start = strtotime($dateArr[0]);
            $end = strtotime('+1 day', $dateArr[1]);
            if($start && $end) {
                $map['create'] = array(array('egt', $start),array('elt', $end));
            }
        }
        $userpaylist = $this->field('id,banque,amount,type,info,userid,state,create')->where($map)->order(array('create'=>'DESC'))->select();
        foreach ($userpaylist as $key=>$value) {
            $userpaylist[$key]['create'] = date('Y/m/d H:i:s', $value['create']);
            //状态处理
            switch ($value['state']) {
                case 0://待审核
                    $userpaylist[$key]['state'] = '<span class="badge bg-red-gradient">待审核</span>';
                    break;
                case 1://审核通过
                    $userpaylist[$key]['state'] = '<span class="badge bg-green-gradient">审核通过</span>';
                    break;
                case 2://审核不通过
                    $userpaylist[$key]['state'] = '<span class="badge bg-yellow-gradient">审核不通过</span>';
                    break;
                case 3://已删除
                    $userpaylist[$key]['state'] = '<span class="badge bg-light-blue-gradient">已删除</span>';
                    break;
            }
            //充值类型
            $userpaylist[$key]['type'] = $value['type'] == '汇款充值' ? '<span class="badge bg-green-gradient">汇款充值</span>' : '<span class="badge bg-yellow-gradient">现金充值</span>';
            //账户信息处理
            if($value['banque']) $userpaylist[$key]['banqueinfo'] = unserialize(stripcslashes($value['banque']));
            //删除按钮处理
            if($value['state'] == 0) {
                $userpaylist[$key]['del_btn'] = '<a href="javascript:void(0);" class="btn btn-xs btn-primary del-btn" data-id="'.$value['id'].'"><i class="ion-ios-close-outline"></i></a>';
            }
        }
        return $userpaylist;
    }

    /**
     * 删除充值记录
     * @param $userpayid 充值记录id
     * @param $userid 用户id
     * @return bool
     */
    public function removeUserPay($userpayid, $userid) {
        $map = array(
            'id'=>$userpayid,
            'userid'=>$userid
        );
        return $this->where($map)->setField('state', 3);
    }

}