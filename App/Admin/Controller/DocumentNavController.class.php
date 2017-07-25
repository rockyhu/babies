<?php
namespace Admin\Controller;

class DocumentNavController extends AuthController {
    
	//显示产品分类列表
	public function index(){
        $this->getAuthNavDos();
		$this->display();
	}
	
	public function ajaxDocumentNavList() {
	    if(IS_AJAX){
	        echo D('DocumentNav')->ajaxlistDocumentNav(I('get.draw'), I('get.search')['value'], I('get.start'), I('get.length'));
	    }
	}
	
	//新增产品分类
	public function add() {
        $this->getAuthNavDos('DocumentNav/add');
        $id = I('get.id');
        if(isset($id) && !empty($id)) {
            $this->assign('OneDocumentNav', D('DocumentNav')->getOneDocumentNav($id));
        }
	    $this->display();
	}
	
	//修改产品分类
	public function edit() {
        if(!in_array('DocumentNav/edit', session('pageNavDos'))) $this->error('您没有权限访问该菜单~');
	    $id = I('get.id');
	    if(isset($id) && !empty($id)){
            $this->getAuthNavDos();
    	    $this->assign('OneDocumentNav', D('DocumentNav')->getOneDocumentNav($id));
    	    $this->display();
	    }
	}

    /**
     * 权限控制
     * @param string $currentUrl
     */
    private function getAuthNavDos($currentUrl = '') {
        $btn_html = '';
        if(in_array('DocumentNav/add', session('pageNavDos'))) {
            if($currentUrl == 'DocumentNav/add')
                $btn_html .= '<li class="active"><a href='.U('DocumentNav/add').'><i class="ion-ios-plus"></i> 添加分类</a></li>';
            else
                $btn_html .= '<li><a href='.U('DocumentNav/add').'><i class="ion-ios-plus"></i> 添加分类</a></li>';
        }else if($currentUrl == 'DocumentNav/add') {
            $this->error('您没有权限访问该菜单~');
        }
        $this->assign('btn_html', $btn_html);
    }
	
	//新增产品分类
	public function addDocumentNav() {
		if (IS_AJAX) {
			echo D('DocumentNav')->addDocumentNav(I('post.nnid'), I('post.text'), I('post.content'), I('post.kind'), I('post.isshow'));
		} else {
			$this->error('非法操作！');
		}
	}
	
	//编辑产品分类
	public function update() {
	    if (IS_AJAX) {
	        echo D('DocumentNav')->update(I('post.id'), I('post.nnid'), I('post.text'), I('post.sort'), I('post.content'), I('post.kind'), I('post.isshow'));
	    } else {
	        $this->error('非法操作！');
	    }
	}
	
	//获取一个产品分类
	public function getOneNav() {
	    if (IS_AJAX) {
	        $this->ajaxReturn(D('DocumentNav')->getOneNav(I('post.id')));
	    } else {
	        $this->error('非法操作！');
	    }
	}

	public function getNav() {
	    if(IS_AJAX) {
            $this->ajaxReturn(D('DocumentNav')->getNav());
        }else {
            $this->error('非法操作!');
        }
    }

    public function getMainNav() {
        if(IS_AJAX) {
            $this->ajaxReturn(D('DocumentNav')->getMainNav());
        }else {
            $this->error('非法操作!');
        }
    }
	
	//删除产品分类
	public function remove() {
	    if(IS_AJAX){
	        echo D('DocumentNav')->remove(I('post.id'));
	    }else{
	        $this->error('非法操作！');
	    }
	}
}