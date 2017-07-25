<?php
namespace Home\Model;
use Think\Model;

/**
 * 商家支付订单模型
 * @author rockyhu
 *
 */
class ShopSignupModel extends Model{

    /**
     * 添加商家支付订单
     * @param $userid
     * @return int|mixed
     */
    public function addShopSignup($merchantphone, $type, $email, $shoppassword, $shopkind, $shopname, $realname, $phone, $province, $city, $town, $address, $images = array(), $userid) {
        $refereeid = M('User')->where("phone='{$merchantphone}'")->getField('id');
        //先更新会员资料信息
        M('User')->where("id='{$userid}'")->setField(array(
            'shopreferee'=>$refereeid,
            'phone'=>$phone,
            'realname'=>$realname
        ));
        //创建商家数据
        M('Shop')->add([
            'type'=>$type,
            'email'=>$email,
            'shoppassword'=>sha1($shoppassword),
            'shopkind'=>$shopkind,
            'shopname'=>$shopname,
            'shoprealname'=>$realname,
            'shopphone'=>$phone,
            'province'=>$province,
            'city'=>$city,
            'town'=>$town,
            'address'=>$address,
            'images'=>!empty($images) ? serialize($images) : '',
            'userid'=>$userid,
            'merchant_uid'=>$refereeid,
            'create'=>time()
        ]);
        // 创建支付订单之前先清空之前没有报名成功的
        if ($userid) {
            $map['userid'] = $userid;
            $map['signup_state'] = '待付款';
            $this->where($map)->delete();
        }
        $signup_sn = getSignOrderNum($userid);
        $signupid = $this->add([
            'userid'=>$userid,
            'price'=>'1000',
            'signup_sn'=>$signup_sn,
            'create'=>time()
        ]);
        if($signupid>0) {
            return $this->field('price,signup_sn,userid')->where("id='{$signupid}'")->find();
        }else {
            return 0;
        }
    }

    /**
     * 更新商家的支付状态,并分配佣金
     * @param $signup_sn 支付订单编号
     * @param $transaction_id 微信支付订单号
     * @return bool
     */
    public function updateShopSignup($signup_sn, $transaction_id){
        $map['signup_sn'] = $signup_sn;
        $this->where($map)->setField(array(
            'signup_state'=>'已付款',
            'transaction_id'=>$transaction_id,
            'paytime'=>time()
        ));
    }
	
	
}