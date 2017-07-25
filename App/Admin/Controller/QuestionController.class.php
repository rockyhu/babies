<?php
namespace Admin\Controller;

class QuestionController extends AuthController {
    
    public function index() {
        $this->getAuthNavDos();
        $this->display();
    }
    
    public function AjaxQuestionList() {
        if(IS_AJAX){
            echo D('Question')->ajaxlistQuestion(I('get.draw'), I('get.search')['value'], I('get.start'), I('get.length'));
        }
    }
    
    public function add() {
        $this->getAuthNavDos('Question/add');
        $this->display();
    }
    
    public function edit() {
        if(!in_array('Question/edit', session('pageNavDos'))) $this->error('您没有权限访问该菜单~');
        $this->getAuthNavDos();
    	$id = I('get.id');
    	if(isset($id) && !empty($id)){
    		$this->assign('OneQuestion', D('Question')->getOneQuestion($id));
    		$this->display();
    	}
    }

    /**
     * 权限控制
     * @param string $currentUrl
     */
    private function getAuthNavDos($currentUrl = '') {
        $btn_html = '';
        if(in_array('Question/add', session('pageNavDos'))) {
            if($currentUrl == 'Question/add')
                $btn_html .= '<li class="active"><a href='.U('Question/add').'><i class="ion-ios-plus"></i> 添加常见问题</a></li>';
            else
                $btn_html .= '<li><a href='.U('Question/add').'><i class="ion-ios-plus"></i> 添加常见问题</a></li>';
        }else if($currentUrl == 'Question/add') {
            $this->error('您没有权限访问该菜单~');
        }
        $this->assign('btn_html', $btn_html);
    }
    
    public function addQuestion() {
        if (IS_AJAX) {
            echo D('Question')->addQuestion(I('post.title'), I('post.content'), I('post.tags'));
        } else {
            $this->error('非法操作！');
        }
    }

    public function addQuestionTags() {
        if (IS_AJAX) {
            echo D('QuestionTags')->addQuestionTags(I('post.name'));
        } else {
            $this->error('非法操作！');
        }
    }

    /**
     * 验证问题标签是否存在,存在返回'false',不存在返回'true'
     */
    public function checkQuestionTag() {
        if (IS_AJAX) {
            $questionTagId = D('QuestionTags')->checkQuestionTagsIsEx(I('post.name'));
            echo $questionTagId ? 'false' : 'true';
        } else {
            $this->error('非法操作！');
        }
    }

    /**
     * 获取所有的标签
     */
    public function getQuestionTags() {
        if (IS_AJAX) {
            echo $this->ajaxReturn(D('QuestionTags')->getAllQuestionTags());
        } else {
            $this->error('非法操作！');
        }
    }
    
    public function update() {
    	if (IS_AJAX) {
    		echo D('Question')->update(I('post.id'), I('post.title'), I('post.content'), I('post.tags'));
    	} else {
    		$this->error('非法操作！');
    	}
    }
    
    public function remove() {
        if(IS_AJAX){
            echo D('Question')->remove(I('post.id'));
        }else{
            $this->error('非法操作！');
        }
    }
    
}