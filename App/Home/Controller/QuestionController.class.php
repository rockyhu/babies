<?php
namespace Home\Controller;


class QuestionController extends HomeController {

    /**
     * 问题状态设置，解决或未解决
     */
    public function setQuestionSavedState() {
        if (IS_AJAX) {
            echo D('Question')->setQuestionSavedState(I('post.questionid'), I('post.state'), session('user_auth.id'));
        } else {
            $this->error('非法操作！');
        }
    }

}