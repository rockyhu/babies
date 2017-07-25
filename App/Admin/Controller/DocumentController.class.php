<?php
namespace Admin\Controller;

class DocumentController extends AuthController {
    
    public function index() {
        $this->getAuthNavDos();
        $this->assign('documentnavlist', D('DocumentNav')->getAllDocumentNavList());
        $this->display();
    }
    
    public function ajaxDocumentList() {
        if(IS_AJAX){
            echo D('Document')->ajaxlistDocument(I('get.draw'), I('get.search')['value'], I('get.start'), I('get.length'), I('get.status'), I('get.shuxing'), I('get.pnid'), I('get.nid'), I('get.marketprice'));
        }
    }
    
    public function add() {
        $this->getAuthNavDos('Document/add');
        $this->assign('documentnavlist', D('DocumentNav')->getAllDocumentNavListForedit());
        $this->display();
    }
    
    public function edit() {
        if(!in_array('Document/edit', session('pageNavDos'))) $this->error('您没有权限访问该菜单~');
    	$id = I('get.id');
    	if(isset($id) && !empty($id)){
            $this->getAuthNavDos();
            $OneDocument = D('Document')->getOneDocument($id);
            $this->assign('OneDocument', $OneDocument);
            $this->assign('documentnavlist', D('DocumentNav')->getAllDocumentNavListForedit($OneDocument['pnid'], $OneDocument['nid']));
    		$this->display();
    	}
    }

    /**
     * 权限控制
     * @param string $currentUrl
     */
    private function getAuthNavDos($currentUrl = '') {
        $btn_html = '';
        if(in_array('Document/add', session('pageNavDos'))) {
            if($currentUrl == 'Document/add')
                $btn_html .= '<li class="active"><a href='.U('Document/add').'><i class="ion-ios-plus"></i> 添加文档</a></li>';
            else
                $btn_html .= '<li><a href='.U('Document/add').'><i class="ion-ios-plus"></i> 添加文档</a></li>';
        }else if($currentUrl == 'Document/add') {
            $this->error('您没有权限访问该菜单~');
        }
        $this->assign('btn_html', $btn_html);
    }
    
    public function addDocument() {
        if (IS_AJAX) {
            echo D('Document')->addDocument(
                I('post.shopid'),
                I('post.genlisid'),
                I('post.sort'),
                I('post.name'),
                I('post.pnid'),
                I('post.nid'),
                I('post.type'),
                I('post.isrecommand'),
                I('post.isnew'),
                I('post.ishot'),
                I('post.isdiscount'),
                I('post.issendfree'),
                I('post.istime'),
                I('post.isnodiscount'),
                I('post.thumb', '', false),
                I('post.images', '', false),
                I('post.marketprice'),
                I('post.productprice'),
                I('post.costprice'),
                I('post.total'),
                I('post.maxbuy'),
                I('post.sales'),
                I('post.content'),
                I('post.nocommission'),
                I('post.hidecommission'),
                I('post.isreturn'),
                I('post.isreturntwo'),
                I('post.isreturnqueue'),
                I('post.return_appoint_amount'),
                I('post.dispatchprice'),
                I('post.status'));
        } else {
            $this->error('非法操作！');
        }
    }
    
    public function update() {
    	if (IS_AJAX) {
    		echo D('Document')->update(
    		    I('post.id'),
                I('post.shopid'),
                I('post.genlisid'),
                I('post.sort'),
                I('post.name'),
                I('post.pnid'),
                I('post.nid'),
                I('post.type'),
                I('post.isrecommand'),
                I('post.isnew'),
                I('post.ishot'),
                I('post.isdiscount'),
                I('post.issendfree'),
                I('post.istime'),
                I('post.isnodiscount'),
                I('post.thumb', '', false),
                I('post.images', '', false),
                I('post.marketprice'),
                I('post.productprice'),
                I('post.costprice'),
                I('post.total'),
                I('post.maxbuy'),
                I('post.sales'),
                I('post.content'),
                I('post.nocommission'),
                I('post.hidecommission'),
                I('post.isreturn'),
                I('post.isreturntwo'),
                I('post.isreturnqueue'),
                I('post.return_appoint_amount'),
                I('post.dispatchprice'),
                I('post.status'));
    	} else {
    		$this->error('非法操作！');
    	}
    }
    
    public function remove() {
        if(IS_AJAX){
            echo D('Document')->remove(I('post.id'));
        }else{
            $this->error('非法操作！');
        }
    }

    /**
     * 设置产品上架或下架
     */
    public function setDocumentStatus() {
        if(IS_AJAX){
            echo D('Document')->setDocumentStatus(I('post.id'), I('post.status'));
        }else{
            $this->error('非法操作！');
        }
    }

    /**
     * 设置产品的属性
     */
    public function setDocumentProperty() {
        if(IS_AJAX){
            echo D('Document')->setDocumentProperty(I('post.id'), I('post.property'), I('post.value'));
        }else{
            $this->error('非法操作！');
        }
    }
    
}