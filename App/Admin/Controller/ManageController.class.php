<?php
namespace Admin\Controller;

class ManageController extends AuthController {
    
	//显示管理员列表
	public function index(){
        $this->getAuthNavDos();
		$this->display();
	}
    
	//管理员列表
	public function ajaxlistmanage() {
        if(IS_AJAX){
            echo D('Manage')->getAjaxManageList(I('get.draw'), I('get.search')['value'], I('get.start'), I('get.length'));
        }
    }
    
    //管理员登陆日志
    public function ajaxlistLoginlog() {
        if(IS_AJAX){
            echo D('ManageLogin')->getAjaxManageLoginlogList(I('get.draw'), I('get.search')['value'], I('get.start'), I('get.length'));
        }
    }
	
	//新增
	public function add() {
        $this->getAuthNavDos('Manage/add');
	    $this->display();
	}
	
	//修改
	public function edit() {
        if(!in_array('Manage/edit', session('pageNavDos'))) $this->error('您没有权限访问该菜单~');
        $this->getAuthNavDos();
	    $this->assign('OneManage', D('Manage')->getOneManage(I('get.id')));
	    $this->display();
	}
	
	//管理员登陆日志
	public function loginlog() {
        $this->getAuthNavDos('Manage/loginlog');
	    $this->display();
	}

    /**
     * 权限控制
     * @param string $currentUrl
     */
    private function getAuthNavDos($currentUrl = '') {
        $btn_html = '';
        if(in_array('Manage/add', session('pageNavDos'))) {
            if($currentUrl == 'Manage/add')
                $btn_html .= '<li class="active"><a href='.U('Manage/add').'><i class="ion-ios-plus"></i> 添加管理员</a></li>';
            else
                $btn_html .= '<li><a href='.U('Manage/add').'><i class="ion-ios-plus"></i> 添加管理员</a></li>';
        }
        if(in_array('Manage/loginlog', session('pageNavDos'))) {
            if($currentUrl == 'Manage/loginlog')
                $btn_html .= '<li class="active"><a href='.U('Manage/loginlog').'><i class="ion-ios-paper"></i> 管理员登录日志</a></li>';
            else
                $btn_html .= '<li><a href='.U('Manage/loginlog').'><i class="ion-ios-paper"></i> 管理员登录日志</a></li>';
        }
        $this->assign('btn_html', $btn_html);
    }
	
	//获取管理员列表
	public function getList() {
		if (IS_AJAX) {
			$this->ajaxReturn(D('Manage')->getList(I('post.page'),I('post.rows'),I('post.sort'),I('post.order')));
		} else {
			$this->error('非法操作！');
		}
	}
	
	//新增管理员
	public function addManage() {
		if (IS_AJAX) {
			echo D('Manage')->addManage(I('post.manager'), I('post.realname'), I('post.password'), I('post.role'));
		} else {
			$this->error('非法操作！');
		}
	}
	
	//修改管理员
	public function update() {
	    if (IS_AJAX) {
	        echo D('Manage')->update(I('post.id'), I('post.realname'), I('post.password'), I('post.role'));
	    } else {
	        $this->error('非法操作！');
	    }
	}
	
	//删除管理员
    public function remove() {
   		if(IS_AJAX){
			echo D('Manage')->remove(I('post.id'));
		}else{
			$this->error('非法操作！');
		}
	}
	
}