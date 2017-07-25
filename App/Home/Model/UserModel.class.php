<?php
namespace Home\Model;
use Think\Model;

class UserModel extends Model{

    //自动验证
    protected $_validate = array();

    //自动完成
    protected $_auto = array(
        array('create','time',self::MODEL_INSERT,'function')
    );

    /**
     * 绑定手机号码
     * @param string $phone 手机号码
     * @param string $userid 用户id
     */
    public function setUserPhone($phone, $userid) {
        $this->where("id='{$userid}'")->setField('phone', str_replace(' ', '', $phone));
        return 1;
    }

    /**
     * 完善必要资料
     * @param $phone 手机号
     * @param $realname 真实姓名
     * @param $userid 用户id
     * @return int
     */
    public function setUserComplete($phone, $realname, $userid) {
        $state = $this->where("id='{$userid}'")->setField(array(
            'phone'=>$phone,
            'realname'=>$realname,
            'isbindphone'=>1
        ));
        if($state>0) {
            session('user_auth.phone', $phone);
            session('user_auth.realname', $realname);
            session('user_auth.isbindphone', 1);
        }
        return 1;
    }

    /**
     * 获取会员信息
     * @param $userid 会员id
     */
    public function getUserInfo($userid) {
        $userinfo = $this
            ->join(array('a LEFT JOIN __USER__ b ON a.referee=b.id'))
            ->field('a.id,a.realname,a.phone,a.referee,a.weixin,a.province,a.city,a.town,b.nickname')
            ->where("a.id='{$userid}'")
            ->find();
        if($userinfo) {
            $userinfo['nickname'] = !empty($userinfo['nickname']) ? $userinfo['nickname'] : '总店';
        }
        return $userinfo;
    }

    /**
     * 获取会员信息
     * @param $userid 会员id
     */
    public function getMerchantUserInfoforRegister($userid) {
        $userinfo = $this
            ->join(array('a LEFT JOIN __USER__ b ON a.merchantreferee=b.id'))
            ->field('a.id,a.realname,a.phone,a.merchantreferee,a.weixin,a.province,a.city,a.town,b.realname as merchantrefereerealname,b.phone as merchantrefereephone')
            ->where("a.id='{$userid}'")
            ->find();
        if($userinfo) {
            $userinfo['nickname'] = !empty($userinfo['nickname']) ? $userinfo['nickname'] : '总店';
        }
        return $userinfo;
    }

    /**
     * 获取会员信息
     * @param $userid 会员id
     */
    public function getShopUserInfoforRegister($userid) {
        $userinfo = $this
            ->join(array('a LEFT JOIN __USER__ b ON a.shopreferee=b.id'))
            ->field('a.id,a.realname,a.phone,a.shopreferee,a.weixin,a.province,a.city,a.town,b.realname as shoprefereerealname,b.phone as shoprefereephone')
            ->where("a.id='{$userid}'")
            ->find();
        if($userinfo) {
            $userinfo['nickname'] = !empty($userinfo['nickname']) ? $userinfo['nickname'] : '总店';
        }
        return $userinfo;
    }

    /**
     * 会员中心
     */
    public function getUserInfoIndex($userid) {
        $oneuserinfo =  $this
            ->join(array(
                'a LEFT JOIN __TOTAL__ b ON a.id=b.userid',
                'LEFT JOIN __AGENT__ c ON a.agentlevel=c.id'))
            ->field('a.id,a.realname,a.phone,a.avatar,a.weixin,a.nickname,a.agentlevel,b.total,b.epurse,b.o2osharebi,b.sharebi,b.gouwubi,b.o2ovpsbi,b.vpsbi,b.shopbi,b.fundbi,c.agentsign')
            ->where("a.id='{$userid}'")
            ->find();
        if($oneuserinfo) {
            $oneuserinfo['sharebi'] = bcadd($oneuserinfo['sharebi'], $oneuserinfo['o2osharebi'], 2);
            $oneuserinfo['vpsbi'] = bcadd($oneuserinfo['vpsbi'], $oneuserinfo['o2ovpsbi'], 2);
            $oneuserinfo['agentlevel'] = $oneuserinfo['agentlevel'] ? $oneuserinfo['agentlevel'] : '普通会员';
            //在线充值链接
            $oneuserinfo['rechargeurl'] = C('SITE_URL').'?c=Payment&a=rechargePay';
        }
        return $oneuserinfo;
    }

    /**
     * 设置我的资料
     * @param $realname 真实姓名
     * @param $phone 手机号码
     * @param $weixin 微信号
     * @param $province 省
     * @param $city 市
     * @param $town 区
     * @param $userid 会员id
     */
    public function setUserInfo($realname, $phone, $weixin, $province, $city, $town, $userid) {
        $this->where("id='{$userid}'")->setField(array(
            'realname'=>$realname,
            'phone'=>$phone,
            'weixin'=>$weixin,
            'province'=>$province,
            'city'=>$city,
            'town'=>$town,
            'isbindphone'=>(!empty($phone) && isPhone($phone)) ? 1 : 0
        ));
        return 1;
    }

    /**
     * 1.验证占用字段,验证码是否正确 - old
     * @param string $phone 手机号码
     * @return boolean
     */
    public function checkPhone($phone) {
        $userid = $this->where("phone='{$phone}'")->getField('id');
        return $userid>0 ? 1 : 0;
    }

    /**
     * 1.验证占用字段,验证码是否正确 new
     * @param string $phone 手机号码
     * @return boolean
     */
    public function checkMerchantPhone($phone) {
        $userid = $this->where("phone='{$phone}' AND ismerchant=1")->getField('id');
        return $userid>0 ? 1 : 0;
    }

    /**
     * 会员注册
     * @param $phone 手机号码
     * @param $realname 真实姓名
     * @param $password 登陆密码
     * @return int|mixed|string
     */
    public function register($phone, $realname, $password) {
        $data = array(
            'phone'=>$phone,
            'realname'=>$realname,
            'password'=>sha1($password),
            'isbindphone'=>1
        );
        if($this->create($data)) {
            $adduserid = $this->add();
            //创建会员钱包账户
            if($adduserid>0) {
                D('Total')->addUserTotal($adduserid);
            }
            return $adduserid>0 ? $adduserid : 0;
        }else{
            return $this->getError();
        }
    }

    /**
     * 通过微信关注授权后自动注册会员
     * @param $openid
     * @param $weixin 微信账号
     * @param $avatar 头像
     * @param $nickname 昵称
     * @param $sex 性别
     */
    public function autoRegister($openid, $avatar, $nickname, $sex, $referee = 0) {
        $userid = $this->where("openid='{$openid}'")->getField('id');
        if(empty($userid)) {
            $data = array(
                'openid'=>$openid,
                'avatar'=>$avatar,
                'nickname'=>$nickname,
                'gender'=>$sex,
                'referee'=>!empty($referee) ? $referee : 0,
                'followed'=>1//修改关注状态
            );
            if($this->create($data)) {
                $userid = $this->add();
                //创建会员钱包账户
                if($userid>0) {
                    D('Total')->addUserTotal($userid);
                }
            }
        }
    }

    /**
     * 自动登录
     * @param $openid
     */
    public function autoLogin($openid) {
        //验证密码
        $oneuser = $this
            ->field('id,phone,realname,nickname,isreseller,ismerchant,isshop,isbindphone')
            ->where("openid='{$openid}'")
            ->find();
        if(!empty($oneuser)){
            //将记录写入session
            $auth = array(
                'id'=>$oneuser['id'],
                'phone'=>$oneuser['phone'],
                'realname'=>$oneuser['realname'] ? $oneuser['realname'] : ($oneuser['nickname'] ? $oneuser['nickname'] : $oneuser['id']),
                'isreseller'=>$oneuser['isreseller'],
                'ismerchant'=>$oneuser['ismerchant'],
                'isshop'=>$oneuser['isshop'],
                'isbindphone'=>$oneuser['isbindphone']
            );
            //写入session
            session('user_auth',$auth);
        }else{
            return 0;
        }
    }

    /**
     * 3.用户登录
     * @param string $phone 手机号
     * @param string $password 密码
     * @param string $auto     是否自动登陆
     * @return number
     */
    public function login($phone, $password, $auto){
        $map = array(
            'phone'=>$phone,
            'password'=>sha1($password)
        );
        //验证密码
        $user = $this->field('id,phone,realname,nickname,isreseller,ismerchant,isshop,isbindphone')->where($map)->find();
        if($user){
            //登录成功后写入登录信息
            //D('UserLogin')->addUserLogin($user['id']);

            //将记录写入session
            $auth = array(
                'id'=>$user['id'],
                'phone'=>$user['phone'],
                'realname'=>$oneuser['realname'] ? $oneuser['realname'] : ($oneuser['nickname'] ? $oneuser['nickname'] : $oneuser['id']),
                'isreseller'=>$user['isreseller'],
                'ismerchant'=>$user['ismerchant'],
                'isshop'=>$user['isshop'],
                'isbindphone'=>$user['isbindphone']
            );
            //写入session
            session('user_auth',$auth);

            return $user['id'];
        }else{
            return 0;
        }
    }

    /**
     * 完善分销商信息
     * @param $phone 手机号
     * @param $realname 真实姓名
     * @param $weixin 微信号
     * @param $userid 会员id
     */
    public function updateResellerUserInfo($phone, $realname, $weixin, $userid) {
        $this->where("id='{$userid}'")->setField(array(
            'phone'=>$phone,
            'realname'=>$realname,
            'weixin'=>$weixin,
            //这里需要需要保存当前会员的推荐
        ));
        return true;
    }

    /**
     * 更新session数据
     * @param $userid 会员id
     */
    public function updateUserSession($userid) {
        $userinfo = $this->field('isreseller,ismerchant,isshop')->where("id='{$userid}'")->find();
        if($userinfo['isreseller']>0) {
            $_SESSION['user_auth']['isreseller'] = 1;
        }else {
            $_SESSION['user_auth']['isreseller'] = 0;
        }
        if($userinfo['ismerchant']>0) {
            $_SESSION['user_auth']['ismerchant'] = 1;
        }else {
            $_SESSION['user_auth']['ismerchant'] = 0;
        }
        if($userinfo['isshop']>0) {
            $_SESSION['user_auth']['isshop'] = 1;
        }else {
            $_SESSION['user_auth']['isshop'] = 0;
        }
    }

    /**
     * 获取分销商信息
     */
    public function getResellerUserInfo($userid) {
        /**
         * 1.获取会员普通信息
         * 2.获取分销佣金总额
         * 3.获取分销订单总个数
         * 4.获取订单总计
         * 5.获取我的团队人数
         * 6.获取我的客户人数
         * 7.推广二维码链接
         */
        $oneuserinfo =  $this
            ->join(array(
                'a LEFT JOIN __RESELLER__ b ON a.id=b.userid',
                'LEFT JOIN __TOTAL__ d ON a.id=d.userid',
                'LEFT JOIN __RESELLER_LEVEL__ c ON b.level=c.id'))
            ->field('a.id,a.realname,a.phone,a.avatar,a.weixin,a.nickname,b.create,c.resellerlevelname,d.reseller_zigebi,d.reseller_yongjin')
            ->where("a.id='{$userid}'")
            ->find();
        if($oneuserinfo) {
            $oneuserinfo['create'] = date('Y-m-d H:i', $oneuserinfo['create']);

            //我的分销商
            $reselleruseridArr = $this->getResellerTeam($userid);
            //我的团队
            $teamuseridArr = $this->getTeamUseridTeam($userid);

            //添加自定义字段
            $oneuserinfo['yongjin'] = $oneuserinfo['reseller_yongjin'];
            $oneuserinfo['ordernum'] = D('Reseller')->getResellerOrderCount($userid);
            $oneuserinfo['tuanduinum'] = count($reselleruseridArr);
            $oneuserinfo['tuanduiinnum'] = count($teamuseridArr);
            $oneuserinfo['zigeyongjin'] = $oneuserinfo['reseller_zigebi'];
        }
        return $oneuserinfo;
    }

    /**
     * 获取当前会员推荐的分销商,一级、二级
     */
    public function getResellerTeam($userid) {
        $reselleruseridArr = [];
        //获取我推荐的分销商:一级分销商、二级分销商
        $onereselleruser = $this->field('id')->where("referee='{$userid}' AND isreseller=1")->select();
        foreach($onereselleruser as $key=>$value) {
            $reselleruseridArr[] = $value['id'];
            $tworeselleruser = $this->field('id')->where("referee='{$value['id']}' AND isreseller=1")->select();
            foreach ($tworeselleruser as $k=>$v) {
                $reselleruseridArr[] = $v['id'];
            }
        }
        return $reselleruseridArr;
    }

    /**
     * 获取我的团队
     * @param $userid 会员id
     */
    public function getTeamUseridTeam($userid) {
        $reselleruseridArr = [];
        //获取我推荐的分销商:一级分销商、二级分销商
        $onereselleruser = $this->field('id,isreseller')->where("referee='{$userid}'")->select();
        foreach($onereselleruser as $key=>$value) {
            $reselleruseridArr[] = $value['id'];
            //如果是分销商的话,继续查找下一级
            if($value['isreseller'] == 1) {
                $tworeselleruser = $this->field('id')->where("referee='{$value['id']}'")->select();
                foreach ($tworeselleruser as $k=>$v) {
                    $reselleruseridArr[] = $v['id'];
                }
            }
        }
        return $reselleruseridArr;
    }

    /**
     * 获取我的团队列表
     * @param $userid 会员id
     * @param $t 一级还是二级
     * @return array
     */
    public function getUserTeamin($userid, $t) {
        //判断当前会员是否是分销商
        $userinfo = $this->field('id')->where("id='{$userid}'")->find();
        if($userinfo) {
            if($t == 2) {
                $firstUserlist = $this->field('id')->where("referee='{$userid}'")->select();
                $twoUserlistArr = [];
                $secondcount = 0;
                foreach ($firstUserlist as $key=>$value) {
                    $twoUserlist = $this
                        ->field('id,nickname,openid,avatar,create')
                        ->where("referee='{$value['id']}'")->select();
                    $secondcount += count($twoUserlist);
                    foreach ($twoUserlist as $k=>$v) {
                        $twoUserlist[$k]['create'] = date('Y-m-d H:i', $v['create']);
                        $twoUserlist[$k]['count'] = 0;
                        $twoUserlistArr[] = $twoUserlist[$k];
                    }
                }
                return [
                    'list'=>$twoUserlistArr,
                    'firstcount'=>count($firstUserlist),
                    'secondcount'=>$secondcount
                ];
            }else {
                $firstUserlist = $this
                    ->field('id,nickname,openid,avatar,isreseller,create')
                    ->where("referee='{$userid}'")
                    ->select();
                $secondcount = 0;
                foreach ($firstUserlist as $key=>$value) {
                    $firstUserlist[$key]['create'] = date('Y-m-d H:i', $value['create']);
                    $twoUserlist = $this->field('id')->where("referee='{$value['id']}'")->select();
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
     * 获取当前会员推荐的分销商,一级、二级
     * 输出包括分销商id和分销商层级,输出数组
     */
    public function getResellerArrTeam($userid) {
        $reselleruseridArr = [];
        //获取我推荐的分销商:一级分销商、二级分销商
        $onereselleruser = $this->field('id')->where("referee='{$userid}' AND isreseller=1")->select();
        foreach($onereselleruser as $key=>$value) {
            $reselleruseridArr[] = [
                'id'=>$value['id'],
                'floor'=>1
            ];
            $tworeselleruser = $this->field('id')->where("referee='{$value['id']}' AND isreseller=1")->select();
            foreach ($tworeselleruser as $k=>$v) {
                $reselleruseridArr[] = [
                    'id'=>$v['id'],
                    'floor'=>2
                ];
            }
        }
        return $reselleruseridArr;
    }

    /**
     * 根据消费者会员id向上查询两级分销商
     * @param $userid 消费者会员id
     * @return Array (
     * [0] => Array ( [userid] => 19 [floor] => 1 [level] => Array ( [1] => 0.1 [2] => 0.29 [3] => 0.5 ) )
     * [1] => Array ( [userid] => 11 [floor] => 2 [level] => Array ( [1] => 0.18 [2] => 0.43 [3] => 0.8 ) )
     * )
     */
    public function getResellerUseridArr($userid) {
        //第一级分销商
        $firstResellerUser = $this
            ->join(array(
                'a LEFT JOIN  __USER__ b ON a.referee=b.id',
                'LEFT JOIN __RESELLER__ c ON b.id=c.userid',
                'LEFT JOIN __RESELLER_LEVEL__ d ON c.level=d.id'))
            ->field('b.id,d.genlis1toonefee,d.genlis1totwofee,d.genlis2toonefee,d.genlis2totwofee,d.genlis3toonefee,d.genlis3totwofee')
            ->where("a.id='{$userid}' AND b.isreseller=1")
            ->find();
        $reselleruserArr = [];
        if($firstResellerUser) {
            //第一级分销商数据
            $reselleruserArr[] = [
                'userid'=>$firstResellerUser['id'],
                'floor'=>1,
                'level'=>[
                    '1'=>$firstResellerUser['genlis1toonefee'], '2'=>$firstResellerUser['genlis2toonefee'], '3'=>$firstResellerUser['genlis3toonefee']
                ]
            ];
            //第二级分销商
            $secondResellerUser = $this
                ->join(array(
                    'a LEFT JOIN  __USER__ b ON a.referee=b.id',
                    'LEFT JOIN __RESELLER__ c ON b.id=c.userid',
                    'LEFT JOIN __RESELLER_LEVEL__ d ON c.level=d.id'))
                ->field('b.id,d.genlis1toonefee,d.genlis1totwofee,d.genlis2toonefee,d.genlis2totwofee,d.genlis3toonefee,d.genlis3totwofee')
                ->where("a.id='{$firstResellerUser['id']}' AND b.isreseller=1")
                ->find();
            if($secondResellerUser) {
                //第二级分销商数据
                $reselleruserArr[] = [
                    'userid'=>$secondResellerUser['id'],
                    'floor'=>2,
                    'level'=>[
                        '1'=>$secondResellerUser['genlis1totwofee'], '2'=>$secondResellerUser['genlis2totwofee'], '3'=>$secondResellerUser['genlis3totwofee']
                    ]
                ];
            }
        }
        return $reselleruserArr;
    }

    /**
     * 获取招商员中心的信息
     * @param $userid 会员编号
     */
    public function getMerchantUserInfo($userid) {
        /**
         * 1.招商员的个人信息
         * 2.招商佣金
         * 3.资格佣金
         * 4.我的订单
         * 5.我的商家
         * 6.我的团队
         * 7.二维码
         */
        $oneuserinfo =  $this
            ->join(array(
                'a LEFT JOIN __MERCHANT__ b ON a.id=b.userid',
                'LEFT JOIN __TOTAL__ d ON a.id=d.userid',
                'LEFT JOIN __MERCHANT_LEVEL__ c ON b.level=c.id'))
            ->field('a.id,a.realname,a.phone,a.avatar,a.weixin,a.nickname,b.create,c.merchantlevelname,d.merchant_zigebi,d.merchant_yongjin')
            ->where("a.id='{$userid}'")
            ->find();
        if($oneuserinfo) {
            $oneuserinfo['create'] = date('Y-m-d H:i', $oneuserinfo['create']);
            $merchantuseridArr = $this->getMerchantTeam($userid);

            //添加自定义字段
            $oneuserinfo['yongjin'] = $oneuserinfo['merchant_yongjin'];
            $oneuserinfo['shopcount'] = M('Shop')->where("merchant_uid='{$userid}' AND status='审核通过'")->count();
            $oneuserinfo['tuanduinum'] = count($merchantuseridArr);
            $oneuserinfo['zigeyongjin'] = $oneuserinfo['merchant_zigebi'];
        }
        return $oneuserinfo;
    }

    /**
     * 获取当前会员推荐的招商员,一级、二级
     */
    public function getMerchantTeam($userid) {
        $reselleruseridArr = [];
        //获取我推荐的分销商:一级分销商、二级分销商
        $onereselleruser = $this->field('id')->where("merchantreferee='{$userid}' AND ismerchant=1")->select();
        foreach($onereselleruser as $key=>$value) {
            $reselleruseridArr[] = $value['id'];
            $tworeselleruser = $this->field('id')->where("merchantreferee='{$value['id']}' AND ismerchant=1")->select();
            foreach ($tworeselleruser as $k=>$v) {
                $reselleruseridArr[] = $v['id'];
            }
        }
        return $reselleruseridArr;
    }

    /**
     * 余额提现
     * @param $userid 会员id
     */
    public function getUserInfoForwithdraw($userid) {
        //余额金额
        $epurse = M('Total')->where("userid='{$userid}'")->getField('epurse');
        //提现手续费
        $cashoutfee = M('System')->where("id=1")->getField('cashoutfee');
        return [
            'epurse'=>$epurse,
            'cashoutfee'=>$cashoutfee
        ];
    }

    /**
     * 商家货款余额提现
     * @param $userid 会员id
     */
    public function getShopInfoForwithdraw($userid) {
        //验证当前会员是否是商家
        $usershopinfo = $this->join(array(
            'a LEFT JOIN __SHOP__ b ON a.id=b.userid',
            'LEFT JOIN __TOTAL__ c ON a.id=c.userid'
        ))->field('b.id as shopid,c.shopbi')->where("a.id='{$userid}' AND b.status='审核通过'")->find();
        if($usershopinfo) {
            return [
                'shopbi'=>$usershopinfo['shopbi'],
                'shopid'=>$usershopinfo['shopid'],
                'cashoutfee'=>0
            ];
        }
    }

    /**
     * 余额提现提交
     */
    public function withdrawdo($amount, $cashoutfee, $userid) {
        /**
         * 提现步骤：
         * 1.当前用户的电子钱包减去提现金额
         * 2.记录当前用户提现记录
         * 3.短信提示管理员有提现
         */
        $create = time();
        $Total = D('Total');
        $epurse = $Total->getUserTotal($userid, 'epurse');
        if($epurse >= $amount) {
            //1.减去电子钱包
            $Total->where("userid='{$userid}'")->setDec('epurse', $amount);
            //2.提现明细
            D('Purse')->addPurse(array(
                'jine'=>$amount,
                'yue'=>numberFormat('-', $epurse, $amount),
                'tradeType'=>'余额提现',
                'type'=>'支出',
                'kind'=>'钱包余额账户',
                'info'=>'余额提现',
                'userid'=>$userid,
                'create'=>$create
            ));

            //添加提现记录
            $state = D('Cashout')->withdrawdo($amount, $cashoutfee/100, $userid, $create);
            if($state>0) {
                //3.发送短信
                //eg:胡世金于11月12日申请提现5000元。手续费250元，实际到账为4750元；请及时进行处理。
                $date = date('m月d日 H:i', time());
                $fee = $amount*$cashoutfee/100;
                $shiji = $amount - $fee;
                $userinfo = $this->field('openid,nickname,realname,phone')->where("id='{$userid}'")->find();
                requestManageResponseCode(C('DEFAULT_PHONE'), autoGetTemplateId('cashout'), array(
                    'realname'=>($userinfo['nickname'] ? $userinfo['nickname'] : $userinfo['realname']).'(会员)',
                    'create'=>$date,
                    'amount'=>$amount,
                    'fee'=>$fee,
                    'shiji'=>$shiji
                ));

                // 引入微信模板插件
                vendor('WechatTemplate.WechatTemplate', '', '.class.php');
                $wechatTemplate = new \WechatTemplate();
                $wechatTemplate->sendTemplateMessage(urldecode(json_encode([
                    "touser"=>$userinfo['openid'],
                    "template_id"=>autoGetWXTemplateId('TX001'),
                    "url"=>C('SITE_URL').'User/cashoutdetail.html',
                    "topcolor"=>"#7B68EE",
                    "data"=>[
                        "first"=>[
                            "value"=>urlencode("余额提现申请已提交，资金预计". date('m月d日', time()) ."24:00前到账，请注意查收。"),
                            "color"=>"#743A3A"
                        ],
                        "money"=>[
                            "value"=>urlencode($amount."元"),
                            "color"=>"#C4C400"
                        ],
                        "timet"=>[
                            "value"=>urlencode(date('Y年m月d日 H:i:s', time())),
                            "color"=>"#0000FF"
                        ],
                        "remark"=>[
                            "value"=>urlencode("\\n若您有疑问,请联系官方客服,客服电话为:0755-21002719!"),
                            "color"=>"#008000"
                        ]
                    ]
                ])));
            }
            return $state;
        }else {
            return 0;
        }
    }

    /**
     * 货款余额提现提交
     */
    public function shopwithdrawdo($amount, $cashoutfee, $shopid, $userid) {
        /**
         * 提现步骤：
         * 1.当前用户的电子钱包减去提现金额
         * 2.记录当前用户提现记录
         * 3.短信提示管理员有提现
         */
        $create = time();
        $Total = D('Total');
        $shopbi = $Total->getUserTotal($userid, 'shopbi');
        if($shopbi >= $amount) {
            //1.减去电子钱包
            $Total->where("userid='{$userid}'")->setDec('shopbi', $amount);
            //2.提现明细
            D('Purse')->addPurse(array(
                'jine'=>$amount,
                'yue'=>numberFormat('-', $shopbi, $amount),
                'tradeType'=>'货款提现',
                'type'=>'支出',
                'kind'=>'商家货款账户',
                'info'=>'货款余额提现',
                'userid'=>$userid,
                'create'=>$create
            ));
            //3.添加提现记录
            $state = D('ShopCashout')->withdrawdo($amount, $cashoutfee/100, $shopid, $userid, $create);
            if($state>0) {
                //3.发送短信
                //eg:胡世金于11月12日申请提现5000元。手续费250元，实际到账为4750元；请及时进行处理。
                $date = date('m月d日 H:i', time());
                $fee = $amount*$cashoutfee/100;
                $shiji = $amount-$fee;
                $shopinfo = M('Shop')->field('shopname')->where("id='{$shopid}'")->find();
                requestManageResponseCode(C('DEFAULT_PHONE'), autoGetTemplateId('shopcashout'), array(
                    'realname'=>$shopinfo['shopname'].'(商家)',
                    'create'=>$date,
                    'amount'=>$amount,
                    'fee'=>$fee,
                    'shiji'=>$shiji
                ));

                //4. 引入微信模板插件
                vendor('WechatTemplate.WechatTemplate', '', '.class.php');
                $wechatTemplate = new \WechatTemplate();
                $userinfo = $this->field('openid')->where("id='{$userid}'")->find();
                $wechatTemplate->sendTemplateMessage(urldecode(json_encode([
                    "touser"=>$userinfo['openid'],
                    "template_id"=>autoGetWXTemplateId('TX001'),
                    "url"=>C('SITE_URL').'Shop/logg.html',
                    "topcolor"=>"#7B68EE",
                    "data"=>[
                        "first"=>[
                            "value"=>urlencode("货款提现申请已提交，资金预计". date('m月d日', time()) ."24:00前到账，请注意查收。"),
                            "color"=>"#743A3A"
                        ],
                        "money"=>[
                            "value"=>urlencode($amount."元"),
                            "color"=>"#C4C400"
                        ],
                        "timet"=>[
                            "value"=>urlencode(date('Y年m月d日 H:i:s', time())),
                            "color"=>"#0000FF"
                        ],
                        "remark"=>[
                            "value"=>urlencode("\\n若您有疑问,请联系官方客服,客服电话为:0755-21002719!"),
                            "color"=>"#008000"
                        ]
                    ]
                ])));
            }
            return $state;
        }else {
            return 0;
        }
    }

    /**
     * 获取我的账户类型的详情,包括当前的余额及当前账户的职能及当前账户的财务明细筛选
     * @param $type 账户类型
     * @param $userid 会员id
     */
    public function getUserAccountInfo($type, $userid) {
        switch ($type) {
            case 1://钱包余额账户
                $type = '钱包余额账户';
                $typeinfo = '可以用来消费、回购、转账';
                $yue = M('Total')->where("userid='{$userid}'")->getField('epurse');
                break;
            case 2://消费积分账户
                $type = '消费积分账户';
                $typeinfo = '只能用来消费';
                $yue = M('Total')->where("userid='{$userid}'")->getField('gouwubi');
                break;
            case 3://共享积分账户
                $type = '共享积分账户';
                $typeinfo = '已经增值了的积分';
                $yue = M('Total')->where("userid='{$userid}'")->getField('sharebi');
                break;
            case 4://增值积分账户
                $type = '增值积分账户';
                $typeinfo = '累计总增值积分';
                $yue = M('Total')->where("userid='{$userid}'")->getField('vpsbi');
                break;
            case 5://分销佣金账户
                $type = '分销佣金账户';
                $typeinfo = '累计总分销佣金';
                $yue = M('Total')->where("userid='{$userid}'")->getField('reseller_yongjin');
                break;
            case 6://分销资格佣金账户
                $type = '分销资格佣金账户';
                $typeinfo = '累计总资格佣金';
                $yue = M('Total')->where("userid='{$userid}'")->getField('reseller_zigebi');
                break;
            case 7://招商佣金账户
                $type = '招商佣金账户';
                $typeinfo = '累计总招商佣金';
                $yue = M('Total')->where("userid='{$userid}'")->getField('merchant_yongjin');
                break;
            case 8://招商资格佣金账户
                $type = '招商资格佣金账户';
                $typeinfo = '累计总资格佣金';
                $yue = M('Total')->where("userid='{$userid}'")->getField('merchant_zigebi');
                break;
            case 9://增值积分账户
                $type = '商家增值积分账户';
                $typeinfo = '累计总增值积分';
                $total = M('Total')->field('shopvpsbi,o2oshopvpsbi')->where("userid='{$userid}'")->find();
                $yue = bcadd($total['shopvpsbi'], $total['o2oshopvpsbi'], 3);
                break;
            case 10://共享积分账户
                $type = '商家共享积分账户';
                $typeinfo = '已经增值了的积分';
                $total = M('Total')->field('shopsharebi,o2oshopsharebi')->where("userid='{$userid}'")->find();
                $yue = bcadd($total['shopsharebi'], $total['o2oshopsharebi'], 3);
                break;
        }
        $list = D('Purse')->getUserTypePurseList($type, $userid);
        return [
            'type'=>$type,
            'yue'=>$yue,
            'typeinfo'=>$typeinfo,
            'list'=>$list
        ];
    }

    /**
     * 获取商家的团队
     * @param $userid 会员id
     */
    public function getStoreUserTeam($userid) {
        $teamlist = $this
            ->field('id,nickname,openid,avatar,isreseller,create')
            ->where("referee='{$userid}'")
            ->select();
        foreach ($teamlist as $key=>$value) {
            $teamlist[$key]['create'] = date('Y-m-d H:i', $value['create']);
            $teamlist[$key]['count'] = $this->where("referee='{$value['id']}'")->count();
        }
        return [
            'list'=>$teamlist
        ];
    }

    /*----------------------- 分割线 -------------------------*/

    /**
     * 7.判断会员的原密码输入是否正确 - old
     * @param string $userid 会员id
     * @param string $oldpassword 原密码
     * @return boolean
     */
    public function checkOldPassword($userid, $oldpassword) {
        $map = array(
            'id'=>$userid,
            'password'=>sha1($oldpassword)
        );
        $id = $this->where($map)->getField('id');
        return ($userid && $id == $userid) ? 1 : 0;
    }

    /**
     * 8.修改会员的手机号码 - old
     * @param string $userid 会员id
     * @param string $phone 手机号
     * @param string $activecode 短信验证码
     * @return boolean
     */
    public function updateMobile($userid, $phone, $activecode) {
        /**
         * 步骤：
         * 1.先判断$phone和$activecode是否存在，存在则继续下一步操作，否则返回；
         * 2.修改会员的手机号码
         */
        if(D('Sms')->checkSMSisCheckOk($phone, $activecode)){//验证通过
            return $this->where("id='{$userid}'")->setField('phone', $phone);
        }
    }

    /**
     * 15.设置用户银行卡 - old
     * @param string $cardno 银行卡卡号
     * @param string $bankname 开户银行
     * @param string $bankaddress 开户行地址
     * @param string $userid 用户id
     */
    public function setUserBank($cardno, $cardname, $bankname, $bankaddress, $userid) {
        //设置银行卡
        return D('Bank')->addBank($cardno, $bankname, $cardname, $bankaddress, $userid);
    }

    /*---- 改版函数 start ----------------------------------------------------------*/

    /**
     * 设置个人资料 - new
     * @param $sex 性别
     * @param $phone 手机号码
     * @param $idcard 身份证号
     * @param $email 电子邮件
     * @param $address 联系地址
     * @param $userid 用户id
     * @return int|string
     */
    public function setUserDetail($sex, $phone, $idcard, $email, $province, $city, $town, $address, $userid) {
        $data = array(
            'id'=>$userid,
            'sex'=>$sex,
            'phone'=>$phone,
            'idcard'=>$idcard,
            'email'=>$email,
            'province'=>$province,
            'city'=>$city,
            'town'=>$town,
            'address'=>$address
        );
        if($this->create($data)) {
            $this->save();
            return 1;
        }else {
            return $this->getError();
        }
    }

    /**
     * 消费者、分销商、招商员等级自动升级
     */
    public function setUserAutoUpgrade() {
        /**
         * 1.招商员等级自动升级
         */
        $merchantlist = M('Merchant')->field('level,userid')->order(array('id'=>'ASC'))->select();
        $merchantlevelArr = D('Merchant')->getMerchantLevelArr();
        foreach ($merchantlist as $key=>$value) {
            $value['merchantcount'] = M('User')->where("merchantreferee='{$value['userid']}' AND ismerchant=1")->count();
            //获取新的招商员等级
            $newmerchantlevel = $this->merchantIdSelect($value['merchantcount'], $merchantlevelArr);
            //招商员级别对比
            if($value['level'] < $newmerchantlevel) {
                M('Merchant')->where("userid='{$value['userid']}'")->setField('level', $newmerchantlevel);
            }
        }

        /**
         * 2.分销商等级自动升级
         */
        $resellerlist = M('Reseller')
            ->join(array('a LEFT JOIN __TOTAL__ b ON a.userid=b.userid'))
            ->field('a.level,a.userid,b.spending')
            ->order(array('a.id'=>'ASC'))
            ->select();
        $resellerlevelArr = D('Reseller')->getResellerLevelArr();
        foreach ($resellerlist as $key=>$value) {
            $value['resellercount'] = M('User')->where("referee='{$value['userid']}' AND isreseller=1")->count();
            //获取新的分销商等级
            $newresellerlevel = $this->resellerIdSelect($value['resellercount'], $value['spending'], $resellerlevelArr);
            if($value['level'] < $newresellerlevel) {
                M('Reseller')->where("userid='{$value['userid']}'")->setField('level', $newresellerlevel);
            }
        }

        /**
         * 3.消费者等级自动升级
         */
        $userlist = M('User')
            ->join(array('a LEFT JOIN __TOTAL__ b ON a.id=b.userid'))
            ->field('a.agentlevel,a.id,b.spending')
            ->order(array('a.id'=>'ASC'))
            ->select();
        $useragentlevelArr = D('Agent')->getAgentLevelArr();
        foreach ($userlist as $key=>$value) {
            $newAgentid = $this->userIdSelect($value['spending'], $useragentlevelArr);
            if($value['agentlevel'] < $newAgentid) {
                M('User')->where("id='{$value['id']}'")->setField('agentlevel', $newAgentid);
            }
        }
    }

    /**
     * 获取招商员的等级
     * @param $merchantcount 推荐的招商员数量
     * @param array $merchantlevelArr 招商员等级数组
     */
    private function merchantIdSelect($merchantcount, $merchantlevelArr = array()) {
        foreach ($merchantlevelArr as $level=>$value) {
            if($merchantcount >= $value['min'] && $merchantcount <= $value['max']) {
                return $level;
            }
        }
        return 1;
    }

    /**
     * 获取分销商的等级
     * @param $resellercount 推荐的分销商数量
     * @param $spending 消费额
     * @param array $resellerlevelArr 分销商等级数组
     * @return int|string
     */
    private function resellerIdSelect($resellercount, $spending, $resellerlevelArr = array()) {
        foreach ($resellerlevelArr as $level=>$value) {
            if(($resellercount >= $value['num']['min'] && $resellercount <= $value['num']['max']) && ($spending >= $value['pv']['min'] && $spending <= $value['pv']['max'])) {
                return $level;
            }
        }
        return 1;
    }

    /**
     * 获取会员的等级
     * @param $spending 会员消费额
     * @param array $userlevelArr 会员等级数组
     */
    private function userIdSelect($spending, $userlevelArr = array()) {
        foreach ($userlevelArr as $level=>$value) {
            if($spending >= $value['min'] && $spending <= $value['max']) {
                return $level;
            }
        }
        return 1;
    }

}