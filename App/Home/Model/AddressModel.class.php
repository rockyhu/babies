<?php
namespace Home\Model;
use Think\Model;

class AddressModel extends Model{
	
	//自动验证
	protected $_validate = array();
	
	//自动完成
	protected $_auto = array();

    /**
     * 获取用户的收货地址
     * @param $userid 用户id
     * @return mixed
     */
    public function getUserAddress($addressid, $userid) {
        $addresslist =  $this->field('id,realname,phone,province,city,town,address,userid,isset')->where("userid='{$userid}'")->order(array('create'=>'DESC'))->select();
        if($addressid) {
            foreach ($addresslist as $key=>$value) {
                if($value['id'] == $addressid) $addresslist[$key]['isset'] = 1;
                else $addresslist[$key]['isset'] = 0;
            }
        }
        return $addresslist;
    }

    /**
     * 获取一个收货地址
     * @param $addressid 收货地址id
     * @return mixed
     */
	public function getOneUserAddress($addressid) {
	    return $this->field('id,realname,phone,province,city,town,address')->where("id='{$addressid}'")->find();
	}

    /**
     * 获取会员默认收货地址
     * @param $userid 会员id
     */
	public function getOneUserDefaultAddress($userid) {
        return $this->field('id,realname,phone,province,city,town,address')->where("userid='{$userid}' AND isset=1")->find();
    }

    /**
     * 创建收货地址
     * @param $consignee 收货人
     * @param $phone 联系电话
     * @param $province 省市区
     * @param $address 联系地址
     * @param $userid 用户id
     * @return int|mixed|string
     */
	public function addUserAddress($realname, $phone, $province, $city, $town, $address, $userid) {
	    $data = array(
	        'realname'=>$realname,
	        'phone'=>$phone,
	        'province'=>$province,
            'city'=>$city,
            'town'=>$town,
	        'address'=>$address,
	        'userid'=>$userid,
            'create'=>NOW_TIME
	    );
	    if($this->create($data)){
	        $addressid = $this->add();
            if($addressid>0) {
                $this->issetAddress($addressid, $userid);
            }
	        return $addressid ? $addressid : 0;
	    }else{
	        return $this->getError();
	    }
	}

    /**
     * 编辑收货地址
     * @param $addressid 收货地址id
     * @param $consignee 联系人
     * @param $phone 联系电话
     * @param $province 省市区
     * @param $address 联系地址
     * @return int|string
     */
	public function editUserAddress($addressid, $realname, $phone, $province, $city, $town, $address, $userid) {
	    $data = array(
	        'id'=>$addressid,
            'realname'=>$realname,
            'phone'=>$phone,
            'province'=>$province,
            'city'=>$city,
            'town'=>$town,
            'address'=>$address,
            'create'=>NOW_TIME
	    );
	    if($this->create($data)){
	        $this->save();
	        return 1;
	    }else{
	        return $this->getError();
	    }
	}

    /**
     * 设置默认收货地址
     * @param $addressid 收货地址
     * @param $userid 会员id
     */
	public function issetAddress($addressid, $userid) {
        $this->where("userid='{$userid}'")->setField('isset', 0);
        $this->where("id='{$addressid}'")->setField('isset', 1);
        return 1;
    }

    /**
     * 删除收货地址
     * @param $id 收货地址id
     * @return mixed
     */
	public function remove($id) {
        return $this->delete($id);
    }
	
}