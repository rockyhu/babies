<?php
namespace Home\Model;
use Think\Model;

/**
 * 招商员模型
 * @author rockyhu
 *
 */
class MerchantModel extends Model{

    /**
     * @param $userid
     */
    public function getMerchantTeam($userid, $t) {
        //判断当前会员是否是招商员
        $userinfo = $this
            ->join(array('a LEFT JOIN __MERCHANT_LEVEL__ b ON a.level=b.id'))
            ->field('b.firstfee,b.secondfee')
            ->where("a.userid='{$userid}'")
            ->find();
        if($userinfo) {
            if($t == 2) {
                $firstUserlist = M('User')
                    ->field('id')
                    ->where("merchantreferee='{$userid}' AND ismerchant=1")
                    ->select();
                $twoUserlistArr = [];
                $secondcount = 0;
                foreach ($firstUserlist as $key=>$value) {
                    $twoUserlist = M('User')
                        ->join(array(
                            'a LEFT JOIN __MERCHANT__ b ON a.id=b.userid',
                            'LEFT JOIN __MERCHANT_SIGNUP__ c ON a.id=c.userid'))
                        ->field('a.id,a.nickname,a.openid,a.avatar,b.create,c.price')
                        ->where("a.merchantreferee='{$value['id']}' AND a.ismerchant=1")
                        ->select();
                    $secondcount += count($twoUserlist);
                    foreach ($twoUserlist as $k=>$v) {
                        $twoUserlist[$k]['create'] = date('Y-m-d H:i', $v['create']);
                        $twoUserlist[$k]['count'] = 0;
                        $twoUserlist[$k]['yongjin'] = $v['price'] * $userinfo['secondfee']/100;
                        $twoUserlistArr[] = $twoUserlist[$k];
                    }
                }
                return [
                    'list'=>$twoUserlistArr,
                    'firstcount'=>count($firstUserlist),
                    'secondcount'=>$secondcount
                ];
            }else {
                $firstUserlist = M('User')
                    ->join(array(
                        'a LEFT JOIN __MERCHANT__ b ON a.id=b.userid',
                        'LEFT JOIN __MERCHANT_SIGNUP__ c ON a.id=c.userid'))
                    ->field('a.id,a.nickname,a.openid,a.avatar,b.level,b.create,c.price')
                    ->where("a.merchantreferee='{$userid}' AND a.ismerchant=1")
                    ->select();
                $secondcount = 0;
                foreach ($firstUserlist as $key=>$value) {
                    $firstUserlist[$key]['create'] = date('Y-m-d H:i', $value['create']);
                    $firstUserlist[$key]['yongjin'] = $value['price'] * $userinfo['firstfee']/100;
                    $twoUserlist = M('User')->join(array(
                        'a LEFT JOIN __MERCHANT__ b ON a.id=b.userid',
                        'LEFT JOIN __MERCHANT_SIGNUP__ c ON a.id=c.userid'))
                        ->field('a.id,b.level,c.price')
                        ->where("a.merchantreferee='{$value['id']}' AND a.ismerchant=1")->select();

                    foreach($twoUserlist as $k=>$v) {
                        $firstUserlist[$key]['yongjin'] += $v['price'] * $userinfo['secondfee']/100;
                    }
                    $firstUserlist[$key]['count'] = count($twoUserlist);
                    $secondcount += $firstUserlist[$key]['count'];
                }
                return [
                    'list'=>$firstUserlist,
                    'firstcount'=>count($firstUserlist),
                    'secondcount'=>$secondcount
                ];
            }
        }
    }

    /**
     * 获取招商员招商的商家
     * @param $userid 会员id
     */
    public function getMerchantShop($userid) {
        $shoplist = M('Shop')
            ->join(array('a LEFT JOIN __USER__ b ON a.userid=b.id'))
            ->field('a.shopname,a.shopkind,a.province,a.city,a.town,b.realname,b.avatar,b.nickname')
            ->where("a.merchant_uid='{$userid}' AND a.status='审核通过'")
            ->select();
        return $shoplist;
    }

    /**
     * 获取招商员级别数组
     */
    public function getMerchantLevelArr() {
        $list = M('MerchantLevel')->field('id,minnum,maxnum')->order('id ASC')->select();
        $newArr = [];
        foreach ($list as $key=>$value) {
            $newArr[$value['id']] = [
                'min'=>$value['minnum'],
                'max'=>$value['maxnum']
            ];
        }
        return $newArr;
    }
	
}