<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Auth;

class AuthController extends Controller {
	
	//构造方法(thinkphp的构造方法_initialize,自动执行)
	protected function _initialize() {
		if(!session('admin')){
			$this->redirect('Login/index');
		}
        $Auth = new Auth();
		if(CONTROLLER_NAME != 'Index'){//首页都有权限
		    if(!$Auth->check(MODULE_NAME.'/'.CONTROLLER_NAME.'/', session('admin.id'))){
		        header("Content-type: text/html; charset=utf-8");
		        echo '<p style="margin:10px;text-align:center;">对不起，您没有权限操作此模块！</p>';
		        exit();
		    }  
		}
        
		//运行公用导航
		$this->getNav($Auth);
		
	}
	
	//根据权限获取菜单栏目
	private function getNav($Auth) {
	    //公用导航
        if(is_null(session('admininit.mainNav'))) {
            $rules = $Auth->getGroups(session('admin.id'));
            $mainNav = D('Nav')->getRoleNav($rules[0]['rules'], $rules[0]['group_id']);
            $_SESSION['admininit']['group_id'] = $rules[0]['group_id'];//缓存当前管理员所在的Auth组
        }else {
            $mainNav = session('admininit.mainNav');
            //获取当前的url
            $c_url = getCurrentUrl();
            //设置当前选中的url及当前页面的菜单操作对当前管理员
            $c_id = 0;//当前选中的栏目id
            foreach ($mainNav as $key=>$value){
                $mainNav[$key]['class'] = '';
                if(empty($value['url'])){
                    foreach ($value['subNav'] as $k=>$v) {
                        $mainNav[$key]['subNav'][$k]['class'] = '';
                        if($value['id'] == $v['nid']) {
                            if($v['o_url'] == $c_url) {
                                $mainNav[$key]['subNav'][$k]['class'] = 'active';//子栏目选中
                                $mainNav[$key]['class'] = 'active';//父栏目选中
                                $c_id = $v['id'];//
                            }
                        }
                    }
                }
            }
            //缓存当前页面的菜单操作对当前管理员
            if(!empty($c_id)) {
                $urlDosArr = D('GroupRules')->getOneGroupRulesWithManageid(session('admininit.group_id'), $c_id);
                session('pageNavDos', $urlDosArr);
            }
        }
        $this->assign('allNavMenu', $mainNav);
	}
	
	
}