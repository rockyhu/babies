<?php
namespace Home\Controller;
use Think\Page;

class ProductController extends HomeController{

    public function index() {
        $this->redirect('product/lists');
    }

    /**
     * 搜索商品
     */
    public function searchProductWithKeyword() {
        if(IS_AJAX){
            $this->assign('searchResult', D('Product')->getSearchProductWithKeyword(I('get.keyword')));
            $this->assign('searchKey', '商品');
            $this->display('Category/searchProductResult');
        }else{
            $this->error('非法访问!');
        }
    }

    /**
     * 商品列表
     */
    public function lists() {
        //获取商品分类
        $this->assign('categorylist', D('ProductNav')->getCategoryListForLists());
        //获取产品列表
        $limit = 20;
        $productlist = D('Product')->getProductList(
            !empty(I('get.key')) ? I('get.key') : '',
            !empty(I('get.item')) ? I('get.item') : '',
            !empty(I('get.pnid')) ? I('get.pnid') : 0,
            !empty(I('get.nid')) ? I('get.nid') : 0,
            !empty(I('get.order')) ? I('get.order') : 'sales',
            !empty(I('get.order')) ? I('get.by') : 'desc',
            !empty(I('get.p')) ? I('get.p') : 1,
            $limit
        );
        $page = new Page($productlist['total'], $limit);
        $page->setConfig('theme', '%HEADER% <span class="total">共%TOTAL_PAGE%页</span> %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $page->show();
        $this->assign('page',$show);
        $this->assign('productlist',$productlist);
        $this->display();
    }

    /**
     * 商品详情
     */
    public function detail() {
        if($this->userLogined()) {
            $this->assign('oneProduct', D('Product')->getOneProduct(I('get.id'), session('user_auth.id')));
            $this->assign('cartNum', D('Cart')->getCartNum(session('user_auth.id')));
            $this->display();
        }
    }

    public function getOneProductContent() {
        if (IS_AJAX) {
            echo D('Product')->getOneProductContent(I('post.productid'));
        }
    }

    public function setProductIslike() {
        if (IS_AJAX) {
            echo D('Product')->setProductIslike(session('user_auth.id'), I('post.id'), I('post.islike'));
        } else {
            $this->error('非法操作！');
        }
    }

    /**
     * 分页获取商品
     */
    public function getPageProduct() {
        if (IS_AJAX) {
            echo D('Product')->getRecommendPageProductList(I('post.page'));
        } else {
            $this->error('非法操作！');
        }
    }

    public function removeUserProductLike() {
        if (IS_AJAX) {
            echo D('ProductLike')->removeUserProductLike(I('post.ids'), session('user_auth.id'));
        } else {
            $this->error('非法操作！');
        }
    }

}