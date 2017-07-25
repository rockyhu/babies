<?php
namespace Home\Controller;
use Think\Controller;

//公共的方法
class HomeController extends Controller {
	
	//构造方法(thinkphp的构造方法_initialize)
	protected function _initialize() {}
	
	//检测学员登录状态
	protected function userLogined($shopuserid = 0) {
	    //超级管理员登陆
	    $this->isshutdown();

        //在微信内置浏览器下
        if(isWechat()) {
            // 处理自动登录，当cookie存在，session不存在的情况下
            if(!is_null(cookie('wxdata'))) {
                $wxuser = json_decode(cookie('wxdata'), true);
                $userid = M('User')->where("openid='{$wxuser['openid']}'")->getField('id');
                if (!session('?user_auth')) {
                    if($userid>0) {
                        $map['id'] = $userid;
                        $userObj = M('User')->field('id,phone,realname,nickname,isreseller,ismerchant,isshop,isbindphone')->where($map)->find();
                        if (!empty($userObj)) {
                            // 将记录写入session中去
                            $auth = array(
                                'id'=>$userObj['id'],
                                'phone'=>$userObj['phone'],
                                'realname'=>$userObj['realname'] ? $userObj['realname'] : ($userObj['nickname'] ? $userObj['nickname'] : $userObj['id']),
                                'isreseller'=>$userObj['isreseller'],
                                'ismerchant'=>$userObj['ismerchant'],
                                'isshop'=>$userObj['isshop'],
                                'isbindphone'=>$userObj['isbindphone']
                            );
                            // 写入session
                            session('user_auth', $auth);
                        }
                    }else {
                        //自动注册微信会员
                        D('User')->autoRegister($wxuser['openid'], $wxuser['avatar'], $wxuser['nickname'], $wxuser['sex'], $shopuserid);
                        D('User')->autoLogin($wxuser['openid']);
                    }
                }else {
                    //当前登陆的用户id和获取到的用户id不一致时,重新登陆即可
                    if($userid != session('user_auth.id')) {
                        session('user_auth', null);
                        D('User')->autoLogin($wxuser['openid']);
                    }
                }
            }else {
                //当cookie不存在的时候,那么微信授权自动注册登录即可
                cookie('AuthRedirectUrl', getWebUrl());
                //微信授权 - cookie('AuthRedirectUrl')：必须赋值
                $url = A('Wechat')->checkAuthInfo();
                header("Location: $url");
            }
        }

		//检测session是否存在
		if(session('?user_auth')){//判断session是否存在
            $this->isfreeze(session('user_auth.id'));
			return 1;
		}else{
		    if(isWechat()) {
                return 0;
            }else {
                $this->redirect('User/login');
            }
		}
	}
	
	/**
	 * 检测系统是否关闭
	 */
	protected function isshutdown() {
        $shutdowninfo = M('System')->field('shutdownstate,shutdowntitle,shutdowncontent')->where("id=1")->find();
	    if($shutdowninfo['shutdownstate']) {
	        $this->assign('shutdowninfo', $shutdowninfo);
	        $this->display('Public/doing');
	        exit();
	    }
	    return true;
	}

    /**
     * 检测当前账户是否被冻结
     */
	public function isfreeze($userid) {
        $isfreeze = M('User')->where("id='{$userid}'")->getField('isfreeze');
        if($isfreeze) {
            $this->display('Public/error');
            exit();
        }
    }
	
	
}