<?php
namespace Home\Model;
use Think\Model;

/**
 * 分销商模型
 * @author rockyhu
 *
 */
class ResellerModel extends Model{

    /**
     * @param $userid
     */
    public function getResellerTeam($userid, $t) {
        //判断当前会员是否是分销商
        $userinfo = $this
            ->join(array('a LEFT JOIN __RESELLER_LEVEL__ b ON a.level=b.id'))
            ->field('b.zigeone,b.zigetwo')
            ->where("a.userid='{$userid}'")
            ->find();
        if($userinfo) {
            if($t == 2) {
                $firstUserlist = M('User')
                    ->field('id')
                    ->where("referee='{$userid}' AND isreseller=1")
                    ->select();
                $twoUserlistArr = [];
                $secondcount = 0;
                foreach ($firstUserlist as $key=>$value) {
                    $twoUserlist = M('User')
                        ->join(array(
                            'a LEFT JOIN __RESELLER__ b ON a.id=b.userid',
                            'LEFT JOIN __RESELLER_SIGNUP__ c ON a.id=c.userid'))
                        ->field('a.id,a.nickname,a.openid,a.avatar,b.create,c.price')
                        ->where("a.referee='{$value['id']}' AND a.isreseller=1")
                        ->select();
                    $secondcount += count($twoUserlist);
                    foreach ($twoUserlist as $k=>$v) {
                        $twoUserlist[$k]['create'] = date('Y-m-d H:i', $v['create']);
                        $twoUserlist[$k]['count'] = 0;
                        $twoUserlist[$k]['yongjin'] = $v['price'] * $userinfo['zigetwo']/100;
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
                        'a LEFT JOIN __RESELLER__ b ON a.id=b.userid',
                        'LEFT JOIN __RESELLER_SIGNUP__ c ON a.id=c.userid'))
                    ->field('a.id,a.nickname,a.openid,a.avatar,b.level,b.create,c.price')
                    ->where("a.referee='{$userid}' AND a.isreseller=1")
                    ->select();
                $secondcount = 0;
                foreach ($firstUserlist as $key=>$value) {
                    $firstUserlist[$key]['create'] = date('Y-m-d H:i', $value['create']);
                    $firstUserlist[$key]['yongjin'] = $value['price'] * $userinfo['zigeone']/100;
                    $twoUserlist = M('User')->join(array(
                        'a LEFT JOIN __RESELLER__ b ON a.id=b.userid',
                        'LEFT JOIN __RESELLER_SIGNUP__ c ON a.id=c.userid'))
                        ->field('a.id,b.level,c.price')
                        ->where("a.referee='{$value['id']}' AND a.isreseller=1")->select();

                    foreach($twoUserlist as $k=>$v) {
                        $firstUserlist[$key]['yongjin'] += $v['price'] * $userinfo['zigetwo']/100;
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
     * 获取分销订单
     * @param $userid 会员编号
     * @param $type 订单类型
     */
    public function getResellerOrder($userid, $order_state) {
        switch ($order_state) {
            case 1:
                $map['a.order_state'] = '待付款';
                break;
            case 2:
                $map['a.order_state'] = array('notin', array('已关闭','待付款','待退款'));
                break;
            case 3:
                $map['a.order_state'] = '已完成';
                break;
            default:
                $map['a.order_state'] = array('in', array('待付款','待发货', '待收货','已完成'));
                break;
        }
        //判断当前会员是否是分销商
        $state = $this->where("userid='{$userid}'")->getField('id');
        if($state) {
            //获取分销商团队
            /*
             Array(
                [0] => Array([id] => 19[floor] => 1)
                [1] => Array([id] => 20[floor] => 2)
            )
             */
            $reselleruseridArr = D('User')->getResellerArrTeam($userid);
            if(!empty($reselleruseridArr)) {
                $useridArr = [];//会员id集合
                foreach ($reselleruseridArr as $k=>$v) {
                    $useridArr[] = $v['id'];
                }
                $map['a.userid'] = array('in', $useridArr);
                //获取分销订单
                $list = M('Order')
                    ->join(array('a LEFT JOIN __USER__ b ON a.userid=b.id'))
                    ->field('a.id,a.ordersn,a.ordersn_general,a.order_state,a.products,a.userid,a.create,b.avatar,b.nickname,b.weixin')
                    ->where($map)
                    ->order(array('a.create'=>'DESC'))
                    ->select();
                //分销级别对应星级产品分销比例参数
                /**
                Array (
                  [1] => Array ( [1] => 0.1 [2] => 0.18 )
                  [2] => Array ( [1] => 0.29 [2] => 0.43 )
                  [3] => Array ( [1] => 0.5 [2] => 0.8 )
                  )
                 */
                $resellerlevelArr = D('ResellerLevel')->getAllResellerLevelArr($userid);
                foreach($list as $key=>$value) {
                    $list[$key]['create'] = date('Y-m-d H:i', $value['create']);
                    //层级
                    $floor = $this->findFloor($reselleruseridArr, $value['userid']);
                    $list[$key]['floor'] = $floor == 1 ? '一级' : '二级';
                    $products = unserialize(stripcslashes($value['products']));
                    $yongjin = 0;
                    foreach ($products as $k1=>$v1) {
                        //图片处理
                        if(!empty($v1['thumb'])) {
                            $products[$k1]['thumb'] = $v1['thumb'] ? C('SITE_URL').substr($v1['thumb'], 2) : '';
                        }
                        //产品佣金处理
                        if($v1['genlisid']>0) {
                            $producttotalprice = $v1['marketprice']*$v1['num'];
                            $products[$k1]['yongjin'] = number_format($producttotalprice*$resellerlevelArr[$v1['genlisid']][$floor]/100, 2);
                        }else {
                            $products[$k1]['yongjin'] = 0;
                        }
                        $yongjin += $products[$k1]['yongjin'];
                    }
                    $list[$key]['yongjin'] = number_format($yongjin, 2);//累计佣金
                    $list[$key]['products'] = $products;
                }
                return $list;
            }
        }
    }

    /**
     * 获取分销商分销订单数量
     * @param $userid 分销商id
     * @return int 分销订单数量
     */
    public function getResellerOrderCount($userid) {
        $map['order_state'] = array('in', array('待付款','待发货', '待收货','已完成'));
        //判断当前会员是否是分销商
        $state = $this->where("userid='{$userid}'")->getField('id');
        if($state) {
            //获取分销商团队
            $reselleruseridArr = D('User')->getResellerArrTeam($userid);
            if(!empty($reselleruseridArr)) {
                $useridArr = [];//会员id集合
                foreach ($reselleruseridArr as $k=>$v) {
                    $useridArr[] = $v['id'];
                }
                $map['userid'] = array('in', $useridArr);
                //获取分销订单数量
                return M('Order')->where($map)->count();
            }else {
                return 0;
            }
        }else {
            return 0;
        }
    }

    /**
     * 从指定数组中查询floor值
     * @param array $array  Array(
    [0] => Array([id] => 19[floor] => 1)
    [1] => Array([id] => 20[floor] => 2)
    )
     * @param $userid
     */
    private function findFloor($array = array(), $userid) {
        if(empty($array)) return;
        foreach ($array as $key=>$value) {
            if($value['id'] == $userid) {
                $floor = $value['floor'];
                break;
            }
        }
        return $floor;
    }

    /**
     * 获取分销商等级数组
     * @return array
     */
    public function getResellerLevelArr() {
        $list = M('ResellerLevel')->field('id,minnum,maxnum,minpv,maxpv')->order('id ASC')->select();
        $newArr = [];
        foreach($list as $key=>$value) {
            $newArr[$value['id']] = [
                'num'=>[
                    'min'=>$value['minnum'],
                    'max'=>$value['maxnum']
                ],
                'pv'=>[
                    'min'=>$value['minpv'],
                    'max'=>$value['maxpv']
                ]
            ];
        }
        return $newArr;
    }
	
	
}