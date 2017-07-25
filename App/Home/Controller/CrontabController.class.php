<?php
namespace Home\Controller;
use Think\Controller;


//UPDATE wanlian_purse SET `create`=1493645041 WHERE id>=150426 AND id<=153197 AND `info` LIKE '%0501';
/**
 * linux定期执行类控制器
 * @author rockyhu
 */
class CrontabController extends Controller {

    public function isshop() {
        exit();
        $list = M('Shop')->field('userid')->where("status='审核通过'")->select();
        foreach ($list as $key=>$value) {
            M('User')->where("id='{$value['userid']}'")->setField('isshop',1);
        }
    }

    public function updateMer() {
        exit();
        $list = M('Shop')->field('userid,merchant_uid')->where("status='审核通过'")->select();
        print_r($list);
        exit();
        foreach ($list as $key=>$value) {
            M('User')->where("id='{$value['userid']}'")->setField('shopreferee',$value['merchant_uid']);
        }
    }
    
    /**
     * 1.清除未支付的订单 清空30分钟内没有支付的订单
     */
    public function clearNopayOrder() {
        echo D('Order')->clearNopayOrder();
    }

    /**
     * 1.清除未支付的订单 清空30分钟内没有支付的订单
     */
    public function clearNopayOnlineOrder() {
        echo D('StoreOrder')->clearNopayOnlineOrder();
    }

    /**
     * 2.每5分钟执行一次,清空充值未支付的记录
     */
    public function clearNopayCharge() {
        echo D('Charge')->clearNopayCharge();
    }

    /**
     * 3.自动处理增值积分
     */
    public function setUserFanli() {
        //设置获取指定哪一天的数据
        $date = date('md');
        if($date>='0605' && $date<='0611') {//只有大于等于1月24日,小于等于2月4日时才返利
            $field = 'money'.$date;
            $isset = 'isset'.$date;
            //获取所有的待分数据
            $fanlilist = M('Fanliu')
                ->field("id,userid,nickname,realname,$field,$isset,info")
                ->order('id ASC')
                ->select();
            $allMemberLogArray = [];
            $nowtime = time();//当前的时间点
            foreach ($fanlilist as $key=>$value) {
                //更新会员余额
                if(!$value[$isset]) {
                    $newepurse = $value[$field]*0.7;
                    $newgouwubi = $value[$field]*0.3;
                    //累计分红到余额
                    M('Total')->where("userid='{$value['userid']}'")->setField(array(
                        'epurse'=>array('exp', 'epurse+'.$newepurse),
                        'gouwubi'=>array('exp', 'gouwubi+'.$newgouwubi),
                        'sharebi'=>array('exp', 'sharebi+'.$value[$field])
                    ));
                    //设置以分红标记
                    M('Fanliu')->where("id='{$value['id']}'")->setField(array(
                        $isset=>1
                    ));
                    $usertotal = M('Total')->field('sharebi,epurse,gouwubi')->where("userid='{$value['userid']}'")->find();
                    //分红记录数组
                    $allMemberLogArray[] = [
                        'jine'=>$value[$field],
                        'yue'=>numberFormat('+', $usertotal['sharebi'], $value[$field]),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'共享积分账户',
                        'info'=>$value['info'].$date,
                        'userid'=>$value['userid'],
                        'create'=>$nowtime
                    ];
                    $allMemberLogArray[] = [
                        'jine'=>$newepurse,
                        'yue'=>numberFormat('+', $usertotal['epurse'], $newepurse),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'钱包余额账户',
                        'info'=>$value['info'].$date,
                        'userid'=>$value['userid'],
                        'create'=>$nowtime
                    ];
                    $allMemberLogArray[] = [
                        'jine'=>$newgouwubi,
                        'yue'=>numberFormat('+', $usertotal['gouwubi'], $newgouwubi),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'消费积分账户',
                        'info'=>$value['info'].$date,
                        'userid'=>$value['userid'],
                        'create'=>$nowtime
                    ];
                }
            }
            //最终添加分红记录
            !empty($allMemberLogArray) && M('Purse')->addAll($allMemberLogArray);
            echo $date.'消费者增值完成...';
        }else {
            echo '还没有到增值日期...';
        }
    }

    /**
     * 3.自动处理增值积分
     */
    public function setO2OUserFanli() {
        //设置获取指定哪一天的数据
        $date = date('md');
        if($date>='0605' && $date<='0611') {//只有大于等于1月24日,小于等于2月4日时才返利
            $field = 'money'.$date;
            $isset = 'isset'.$date;
            //获取所有的待分数据
            $fanlilist = M('Fanliuo2o')
                ->field("id,userid,nickname,realname,$field,$isset,info")
                ->order('id ASC')
                ->select();
            $allMemberLogArray = [];
            $nowtime = time();//当前的时间点
            foreach ($fanlilist as $key=>$value) {
                //更新会员余额
                if(!$value[$isset]) {
                    $newepurse = $value[$field]*0.7;
                    $newgouwubi = $value[$field]*0.3;
                    //累计分红到余额
                    M('Total')->where("userid='{$value['userid']}'")->setField(array(
                        'epurse'=>array('exp', 'epurse+'.$newepurse),
                        'gouwubi'=>array('exp', 'gouwubi+'.$newgouwubi),
                        'o2osharebi'=>array('exp', 'o2osharebi+'.$value[$field])
                    ));
                    //设置以分红标记
                    M('Fanliuo2o')->where("id='{$value['id']}'")->setField(array(
                        $isset=>1
                    ));
                    $usertotal = M('Total')->field('o2osharebi,epurse,gouwubi')->where("userid='{$value['userid']}'")->find();
                    //分红记录数组
                    $allMemberLogArray[] = [
                        'jine'=>$value[$field],
                        'yue'=>numberFormat('+', $usertotal['o2osharebi'], $value[$field]),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'共享积分账户',
                        'info'=>$value['info'].$date,
                        'userid'=>$value['userid'],
                        'create'=>$nowtime
                    ];
                    $allMemberLogArray[] = [
                        'jine'=>$newepurse,
                        'yue'=>numberFormat('+', $usertotal['epurse'], $newepurse),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'钱包余额账户',
                        'info'=>$value['info'].$date,
                        'userid'=>$value['userid'],
                        'create'=>$nowtime
                    ];
                    $allMemberLogArray[] = [
                        'jine'=>$newgouwubi,
                        'yue'=>numberFormat('+', $usertotal['gouwubi'], $newgouwubi),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'消费积分账户',
                        'info'=>$value['info'].$date,
                        'userid'=>$value['userid'],
                        'create'=>$nowtime
                    ];
                }
            }
            //最终添加分红记录
            !empty($allMemberLogArray) && M('Purse')->addAll($allMemberLogArray);
            echo $date.'消费者增值完成...';
        }else {
            echo '还没有到增值日期...';
        }
    }

    /**
     * 3.自动处理增值积分 - 真实
     */
    public function setShopFanli() {
        //设置获取指定哪一天的数据
        $date = date('md');
        if($date>='0522' && $date<='0530') {//只有大于等于1月24日,小于等于2月4日时才返利
            $field = 'money'.$date;
            $isset = 'isset'.$date;
            //获取所有的待分数据
            $fanlilist = M('Fanlis')
                ->field("id,userid,nickname,realname,$field,$isset,info")
                ->order('id ASC')
                ->select();
            $allMemberLogArray = [];
            $nowtime = time();//当前的时间点
            foreach ($fanlilist as $key=>$value) {
                //更新会员余额
                if(!$value[$isset]) {
                    $newepurse = $value[$field]*0.7;
                    $newgouwubi = $value[$field]*0.3;
                    //累计分红到余额
                    M('Total')->where("userid='{$value['userid']}'")->setField(array(
                        'epurse'=>array('exp', 'epurse+'.$newepurse),
                        'gouwubi'=>array('exp', 'gouwubi+'.$newgouwubi),
                        'shopsharebi'=>array('exp', 'shopsharebi+'.$value[$field])
                    ));
                    //设置以分红标记
                    M('Fanlis')->where("id='{$value['id']}'")->setField(array(
                        $isset=>1
                    ));
                    $usertotal = M('Total')->field('shopsharebi,epurse,gouwubi')->where("userid='{$value['userid']}'")->find();
                    //分红记录数组
                    $allMemberLogArray[] = [
                        'jine'=>$value[$field],
                        'yue'=>numberFormat('+', $usertotal['shopsharebi'], $value[$field]),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'商家共享积分账户',
                        'info'=>$value['info'].$date,
                        'userid'=>$value['userid'],
                        'create'=>$nowtime
                    ];
                    $allMemberLogArray[] = [
                        'jine'=>$newepurse,
                        'yue'=>numberFormat('+', $usertotal['epurse'], $newepurse),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'钱包余额账户',
                        'info'=>$value['info'].$date,
                        'userid'=>$value['userid'],
                        'create'=>$nowtime
                    ];
                    $allMemberLogArray[] = [
                        'jine'=>$newgouwubi,
                        'yue'=>numberFormat('+', $usertotal['gouwubi'], $newgouwubi),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'消费积分账户',
                        'info'=>$value['info'].$date,
                        'userid'=>$value['userid'],
                        'create'=>$nowtime
                    ];
                }
            }
            //最终添加分红记录
            !empty($allMemberLogArray) && M('Purse')->addAll($allMemberLogArray);
            echo $date.'商家增值完成...';
        }else {
            echo '还没有到增值日期...';
        }
    }

    /**
     * 3.自动处理增值积分 - o2o
     */
    public function setO2OShopFanli() {
        //设置获取指定哪一天的数据
        $date = date('md');
        if($date>='0605' && $date<='0611') {//只有大于等于1月24日,小于等于2月4日时才返利
            $field = 'money'.$date;
            $isset = 'isset'.$date;
            //获取所有的待分数据
            $fanlilist = M('Fanliso2o')
                ->field("id,userid,nickname,realname,$field,$isset,info")
                ->order('id ASC')
                ->select();
            $allMemberLogArray = [];
            $nowtime = time();//当前的时间点
            foreach ($fanlilist as $key=>$value) {
                //更新会员余额
                if(!$value[$isset]) {
                    $newepurse = $value[$field]*0.7;
                    $newgouwubi = $value[$field]*0.3;
                    //累计分红到余额
                    M('Total')->where("userid='{$value['userid']}'")->setField(array(
                        'epurse'=>array('exp', 'epurse+'.$newepurse),
                        'gouwubi'=>array('exp', 'gouwubi+'.$newgouwubi),
                        'o2oshopsharebi'=>array('exp', 'o2oshopsharebi+'.$value[$field])
                    ));
                    //设置以分红标记
                    M('Fanliso2o')->where("id='{$value['id']}'")->setField(array(
                        $isset=>1
                    ));
                    $usertotal = M('Total')->field('o2oshopsharebi,epurse,gouwubi')->where("userid='{$value['userid']}'")->find();
                    //分红记录数组
                    $allMemberLogArray[] = [
                        'jine'=>$value[$field],
                        'yue'=>numberFormat('+', $usertotal['o2oshopsharebi'], $value[$field]),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'商家共享积分账户',
                        'info'=>$value['info'].$date,
                        'userid'=>$value['userid'],
                        'create'=>$nowtime
                    ];
                    $allMemberLogArray[] = [
                        'jine'=>$newepurse,
                        'yue'=>numberFormat('+', $usertotal['epurse'], $newepurse),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'钱包余额账户',
                        'info'=>$value['info'].$date,
                        'userid'=>$value['userid'],
                        'create'=>$nowtime
                    ];
                    $allMemberLogArray[] = [
                        'jine'=>$newgouwubi,
                        'yue'=>numberFormat('+', $usertotal['gouwubi'], $newgouwubi),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'消费积分账户',
                        'info'=>$value['info'].$date,
                        'userid'=>$value['userid'],
                        'create'=>$nowtime
                    ];
                }
            }
            //最终添加分红记录
            !empty($allMemberLogArray) && M('Purse')->addAll($allMemberLogArray);
            echo $date.'商家增值完成...';
        }else {
            echo '还没有到增值日期...';
        }
    }

    /**
     * 4.每天晚上的23点自动结算
     */
    public function setSettle() {
        echo D('Settle')->setDaySettle();
    }

    /**
     * 5.每5分钟执行一次,设置绑定手机号的用户为绑定手机状态
     */
    public function autobindPhone() {
        echo M('User')->where("phone<>'' AND realname<>'' AND isbindphone=0")->setField('isbindphone', 1);
    }

    /**
     * 订单7天自动确认收货,每10分钟执行一次
     */
    public function autoiscompleteorder() {
        echo D('Order')->autoiscompleteorder();
    }

    /**
     * 消费者、分销商、招商员等级自动升级
     */
    public function userAutoUpgrade() {
        echo D('User')->setUserAutoUpgrade();
    }

}