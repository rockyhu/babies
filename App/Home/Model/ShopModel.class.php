<?php
namespace Home\Model;
use Think\Model;

/**
 * 店铺模型
 * @author rockyhu
 *
 */
class ShopModel extends Model{
	
	//自动验证
	protected $_validate = array();
	
	//自动完成
	protected $_auto = array();

    /**
     * 获取当前用户的店铺信息
     */
	public function getUserShopInfo($userid) {
        $oneShop = $this->join(array('a LEFT JOIN __SHOP_LEVEL__ b ON a.shoplevel=b.id','LEFT JOIN __USER__ c ON a.userid=c.id'))->field('a.id,a.shopname,a.shoplevel,a.province,a.city,a.town,a.address,a.shopinfo,a.userid,a.isempty,a.state,b.name as shoplevelname,c.username,c.realname')->where("a.userid='{$userid}'")->find();
        if($oneShop) {
            $oneShop['shopinfo'] = htmlspecialchars_decode($oneShop['shopinfo']);
        }
        return $oneShop;
    }

    /**
     * 编辑店铺信息
     * @param $shopid 店铺id
     * @param $address 店铺经营地址
     * @param $shopinfo 店铺描述
     */
    public function setUserShopInfo($shopid, $address, $shopinfo, $userid) {
        return $this->where("id='{$shopid}' AND userid='{$userid}'")->setField(array(
            'shopinfo'=>$shopinfo,
            'address'=>$address
        ));
    }

    /**
     *判断当前的会员是否已经提交商家入驻申请并完成支付
     * @param $userid 会员id
     */
    public function checkShopUserStatus($userid) {
        $shopinfo = $this
            ->join(array('a LEFT JOIN __SHOP_SIGNUP__ b ON a.userid=b.userid'))
            ->field('a.status')->where("a.userid='{$userid}' AND ((a.type='线上店铺' AND b.signup_state='已付款') OR a.type='实体店铺')")->find();
        return $shopinfo;
    }

    /**
     * 获取商家资料
     * @param $userid 会员id
     */
    public function getRegisterShopInfo($userid, $status = '审核不通过') {
        $shopinfo = $this
            ->join(array('a LEFT JOIN __USER__ c ON a.merchant_uid=c.id'))
            ->field('a.id,a.shopname,a.shoprealname,a.shopphone,a.shopkind,a.email,a.type,a.province,a.city,a.town,a.address,a.images,a.merchant_uid,c.phone as shoprefereephone,c.realname as shoprefereerealname')
            ->where("a.userid='{$userid}' AND a.status='{$status}'")
            ->find();
        if($shopinfo) {
            if(!empty($shopinfo['images'])) {
                $shopinfo['images'] = unserialize(stripcslashes($shopinfo['images']));
                foreach ($shopinfo['images'] as $key=>$value) {
                    $shopinfo['images'][$key] = C('SITE_URL').substr($value, 2);
                }
            }
        }
        return $shopinfo;
    }

    /**
     * 商家入驻
     * @param $merchantphone 招商员手机号
     * @param $type 店铺类型
     * @param $email 电子邮件
     * @param $shopkind 商户类别
     * @param $shopname 商户名称
     * @param $realname 真实姓名
     * @param $phone 手机号码
     * @param $province 省
     * @param $city 市
     * @param $town 区
     * @param $address 地址
     * @param array $images 证件图片集合
     * @param $userid 会员id
     */
    public function registerShop($merchantphone, $type, $email, $shoppassword, $shopkind, $shopname, $realname, $phone, $province, $city, $town, $address, $images = array(), $userid) {
        $refereeid = M('User')->where("phone='{$merchantphone}'")->getField('id');
        //先更新会员资料信息
        M('User')->where("id='{$userid}'")->setField(array(
            'shopreferee'=>$refereeid,
            'phone'=>$phone,
            'realname'=>$realname
        ));
        //创建商家数据
        return M('Shop')->add([
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
    }

    /**
     * 重新提交商家信息资料
     * @param $id 商家id
     * @param $merchantphone 招商员id
     * @param $type 店铺类型
     * @param $email 电子邮件
     * @param $shopkind 商户类别
     * @param $shopname 商户名称
     * @param $realname 真实姓名
     * @param $phone 手机号码
     * @param $province 省
     * @param $city 市
     * @param $town 区
     * @param $address 地址
     * @param array $images 证件图片集合
     * @param $userid 会员id
     * @return int|mixed
     */
    public function updateShopInfo($id, $type, $email, $shopkind, $shopname, $realname, $phone, $province, $city, $town, $address, $images = array(), $userid) {
        //先更新会员资料信息
        M('User')->where("id='{$userid}'")->setField(array(
            'phone'=>$phone,
            'realname'=>$realname
        ));
        //创建商家数据
        M('Shop')->save([
            'id'=>$id,
            'type'=>$type,
            'email'=>$email,
            'shopkind'=>$shopkind,
            'shopname'=>$shopname,
            'shoprealname'=>$realname,
            'shopphone'=>$phone,
            'province'=>$province,
            'city'=>$city,
            'town'=>$town,
            'address'=>$address,
            'status'=>'审核中',
            'images'=>!empty($images) ? serialize($images) : '',
            'userid'=>$userid,
            'create'=>time()
        ]);
        return 1;
    }

    /**
     * 获取商家信息
     * @param $userid 会员id
     */
    public function getShopInfo($userid) {
        $shopinfo = M('User')
            ->join(array(
                'a LEFT JOIN __SHOP__ b ON a.id=b.userid',
                'LEFT JOIN __TOTAL__ c ON a.id=c.userid'
            ))
            ->field('a.realname,a.avatar,b.id as shopid,b.shopname,b.type,b.passtime,c.shopbi,c.o2oshopsharebi,c.shopsharebi,c.o2oshopvpsbi,c.shopvpsbi,c.shopbiout')
            ->where("a.id='{$userid}'")
            ->find();
        if($shopinfo) {
            $shopinfo['shopsharebi'] = bcadd($shopinfo['shopsharebi'], $shopinfo['o2oshopsharebi'], 2);
            $shopinfo['shopvpsbi'] = bcadd($shopinfo['shopvpsbi'], $shopinfo['o2oshopvpsbi'], 2);
            $shopinfo['passtime'] = date('Y-m-d H:i', $shopinfo['passtime']);
            if($shopinfo['type'] == '线上店铺') {
                $shopinfo['ordercount'] = M('Order')->where("shopid='{$shopinfo['shopid']}' AND order_state<>'已关闭'")->count();
            }else if($shopinfo['type'] == '实体店铺') {
                $shopinfo['ordercount'] = M('StoreOrder')->where("shopid='{$shopinfo['shopid']}' AND order_state='已完成'")->count();
            }
            $shopinfo['teamcount'] = M('User')->where("referee='{$userid}'")->count();
        }
        return $shopinfo;
    }
	
}