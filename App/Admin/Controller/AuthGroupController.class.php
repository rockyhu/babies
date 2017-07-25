<?php
namespace Admin\Controller;

class AuthGroupController extends AuthController {
	//显示角色列表
	public function index(){
        $this->getAuthNavDos();
		$this->display();
	}

	public function ajaxlistauthgroup() {
        if(IS_AJAX){
            echo D('AuthGroup')->getAjaxAuthGroupList(I('get.draw'), I('get.search')['value'], I('get.start'), I('get.length'));
        }
    }
	
	//新增
	public function add() {
        $this->getAuthNavDos('AuthGroup/add');
        $this->assign('allNavlist', D('Nav')->getAllNavlistforAuthgroup());
        $this->display();
	}
	
	//修改
	public function edit() {
        if(!in_array('AuthGroup/edit', session('pageNavDos'))) $this->error('您没有权限访问该菜单~');
        $this->getAuthNavDos();
        $this->assign('allNavlist', D('Nav')->getAllNavlistforAuthgroup());
	    $this->assign('oneAuthGroup', D('AuthGroup')->getOneAuthGroup(I('get.id')));
	    $this->display();
	}

    /**
     * 权限控制
     * @param string $currentUrl
     */
    private function getAuthNavDos($currentUrl = '') {
        $btn_html = '';
        if(in_array('AuthGroup/add', session('pageNavDos'))) {
            if($currentUrl == 'AuthGroup/add')
                $btn_html .= '<li class="active"><a href='.U('AuthGroup/add').'><i class="ion-ios-plus"></i> 添加角色</a></li>';
            else
                $btn_html .= '<li><a href='.U('AuthGroup/add').'><i class="ion-ios-plus"></i> 添加角色</a></li>';
        }else if($currentUrl == 'AuthGroup/add') {
            $this->error('您没有权限访问该菜单~');
        }
        $this->assign('btn_html', $btn_html);
    }

	//获取所有角色
	public function getListAll() {
		if (IS_AJAX) {
			$this->ajaxReturn(D('AuthGroup')->getListAll());
		} else {
			$this->error('非法操作！');
		}
	}
	
	//获取一个角色
	public function getOneAuthGroup() {
		if (IS_AJAX) {
			$this->ajaxReturn(D('AuthGroup')->getOneAuthGroup(I('post.id')));
		} else {
			$this->error('非法操作！');
		}
	}
	
	//添加角色
	public function addRole() {
		if (IS_AJAX) {
			echo D('AuthGroup')->addRole(I('post.title'), I('post.rules'));
		} else {
			$this->error('非法操作！');
		}
	}
	
	//编辑角色
	public function editRole() {
		if (IS_AJAX) {
			echo D('AuthGroup')->editRole(I('post.id'), I('post.title'), I('post.rules'));
		} else {
			$this->error('非法操作！');
		}
	}
	
	//删除角色
	public function remove() {
	    if(IS_AJAX){
	        echo D('AuthGroup')->remove(I('post.id'));
	    }else{
	        $this->error('非法操作！');
	    }
	}

    //添加菜单操作
    public function addNavDo() {
        if (IS_AJAX) {
            echo D('NavDo')->addNavDo(I('post.navid'), I('post.text'), I('post.url'));
        } else {
            $this->error('非法操作！');
        }
    }

    public function editNavDo() {
        if (IS_AJAX) {
            echo D('NavDo')->editNavDo(I('post.id'), I('post.text'), I('post.url'));
        } else {
            $this->error('非法操作！');
        }
    }

    //删除菜单操作
    public function removeNavDo() {
        if(IS_AJAX){
            echo D('NavDo')->remove(I('post.id'));
        }else{
            $this->error('非法操作！');
        }
    }
    
}