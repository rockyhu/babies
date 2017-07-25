<?php
namespace Admin\Controller;

class NavController extends AuthController {
    
	//显示菜单导航列表
	public function index(){
        $this->getAuthNavDos();
		$this->display();
	}

	public function ajaxlistNav() {
        if(IS_AJAX){
            echo D('Nav')->ajaxlistNav(I('get.draw'), I('get.search')['value'], I('get.start'), I('get.length'));
        }
    }
	
	//新增菜单导航
	public function add() {
        $this->getAuthNavDos('Nav/add');
	    $this->display();
	}
	
	//修改菜单导航
	public function edit() {
        if(!in_array('Nav/edit', session('pageNavDos'))) $this->error('您没有权限访问该菜单~');
	    $id = I('get.id');
	    if(isset($id) && !empty($id)){
            $this->getAuthNavDos();
    	    $this->assign('OneNav', D('Nav')->getOneNav($id));
    	    $this->display();
	    }
	}

    /**
     * 权限控制
     * @param string $currentUrl
     */
    private function getAuthNavDos($currentUrl = '') {
        $btn_html = '';
        if(in_array('Nav/add', session('pageNavDos'))) {
            if($currentUrl == 'Nav/add')
                $btn_html .= '<li class="active"><a href='.U('Nav/add').'><i class="ion-ios-plus"></i> 添加栏目</a></li>';
            else
                $btn_html .= '<li><a href='.U('Nav/add').'><i class="ion-ios-plus"></i> 添加栏目</a></li>';
        }else if($currentUrl == 'Nav/add') {
            $this->error('您没有权限访问该菜单~');
        }
        $this->assign('btn_html', $btn_html);
    }
	
	//获取菜单导航
	public function getNav() {
	    //获取url存在的导航栏目
	    $this->ajaxReturn(D('Nav')->getNav());
	}
	
	//获取主菜单
	public function getListMain() {
	    if (IS_AJAX) {
	        $this->ajaxReturn(D('Nav')->getListMain(I('get.id')));
	    } else {
	        $this->error('非法操作！');
	    } 
	}
	
	//新增菜单导航
	public function addNav() {
		if (IS_AJAX) {
			echo D('Nav')->addNav(I('post.nid'), I('post.text'), I('post.url'), I('post.iconCls'), I('post.ishide'));
		} else {
			$this->error('非法操作！');
		}
	}
	
	//编辑菜单
	public function update() {
	    if (IS_AJAX) {
	        echo D('Nav')->update(I('post.id'), I('post.nid'), I('post.text'), I('post.url'), I('post.iconCls'), I('post.ishide'), I('post.sort'));
	    } else {
	        $this->error('非法操作！');
	    }
	}
	
	//获取一个菜单
	public function getOneNav() {
	    if (IS_AJAX) {
	        $this->ajaxReturn(D('Nav')->getOneNav(I('post.id')));
	    } else {
	        $this->error('非法操作！');
	    }
	}
	
	//删除菜单
	public function remove() {
	    if(IS_AJAX){
	        echo D('Nav')->remove(I('post.id'), I('post.text'), I('post.nid'));
	    }else{
	        $this->error('非法操作！');
	    }
	}
}