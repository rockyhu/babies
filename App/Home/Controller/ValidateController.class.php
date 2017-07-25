<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 验证类控制器
 * @author rockyhu
 */
class ValidateController extends Controller {
    
	/**
     * 1.Ajax验证会员手机号是否被占用，已占用返回'false',否则返回'true'
     */
    public function checkPhone() {
        if(IS_AJAX){
            $userid = D('User')->checkPhone(strTrim(I('post.phone')));
            echo $userid ? 'false' : 'true';
        }else{
            $this->error('非法访问~');
        }
    }

    /**
     * 1.Ajax验证会员手机号是否存在,存在返回'true',不存在返回'false'
     */
    public function checkMerchantPhone() {
        if(IS_AJAX){
            $userid = D('User')->checkMerchantPhone(strTrim(I('post.merchantphone')));
            echo $userid ? 'true' : 'false';
        }else{
            $this->error('非法访问~');
        }
    }
    
    /**
     * 3.Ajax验证会员原密码是否正确，正确返回'true',否则返回'false'
     */
    public function checkOldPassword() {
    	if(IS_AJAX){
    		$actionFlag = D('User')->checkOldPassword(session('user_auth.id'), I('post.oldpassword'));
    		echo $actionFlag ? 'true' : 'false';
    	}else{
    		$this->error('非法访问~');
    	}
    }

    /**
     * 6.Ajax验证当前用户的支付密码是否正确，正确返回'true',否则返回'false'
     */
    public function checkPaycode() {
        if(IS_AJAX){
            $userid = D('User')->checkPaycode(session('user_auth.id'), I('post.paycode'));
            echo $userid ? 'true' : 'false';
        }else{
            $this->error('非法访问~');
        }
    }
    
    /**
     * 7.二级密码验证
     */
    public function twoValidate() {
        if(IS_AJAX){
            $userid = D('User')->checkPaycode(session('user_auth.id'), I('post.paycode'));
            if($userid) {
                //操作类型
                $type = I('post.type');
                switch ($type) {
                    case 'myagent':
                    case 'useradd':
                        session('twoAuthYeji', 'pass');
                        break;
                    case 'myrehousenet':
                        session('twoAuthRehousenet', 'pass');
                        break;
                }
            }
            echo $userid ? 'true' : 'false';
        }else{
            session('twoAuthYeji', null);
            session('twoAuthRehousenet', null);
            session('isSuper', null);
            $this->error('非法访问~');
        }
    }

    /*---- 改版函数 start ----------------------------------------------------------*/

    /**
     * 1.Ajax验证会员用户名是否被占用，已占用返回'false',否则返回'true'
     */
    public function checkUsername() {
        if(IS_AJAX){
            $userid = D('User')->checkUsername(strTrim(I('post.username')));
            echo $userid ? 'false' : 'true';
        }else{
            $this->error('非法访问~');
        }
    }

    /**
     * 2.Ajax验证会员用户名是否存在并且不能是当前用户的用户名，满足条件返回'true',否则返回'false' - 钱包转账
     */
    public function checkTransferUsername() {
        if(IS_AJAX){
            $userid = D('User')->checkTransferUsername(I('post.username'), session('user_auth.username'));
            echo $userid ? 'true' : 'false';
        }else{
            $this->error('非法访问~');
        }
    }

    /**
     * 3.Ajax验证会员真实姓名是否正确，正确返回'true',否则返回'false'
     */
    public function checkRealname() {
        if(IS_AJAX){
            $userid = D('User')->checkRealname(I('post.username'), I('post.realname'));
            echo $userid ? 'true' : 'false';
        }else{
            $this->error('非法访问~');
        }
    }

    /**
     * 7.Ajax验证推荐人用户名是否存在并不能是当前用户的用户名，正确返回'true',否则返回'false'
     */
    public function checkRefereeUsername() {
        if(IS_AJAX){
            $userid = D('User')->checkRefereeUsername(I('post.refereeusername'));
            echo $userid ? 'true' : 'false';
        }else{
            $this->error('非法访问~');
        }
    }

    /**
     * 8.Ajax验证安置人用户名是否存在，正确返回'true',否则返回'false'
     */
    public function checkRehouseUsername() {
        if(IS_AJAX){
            $userid = D('User')->checkRehouseUsername(I('post.rehouseusername'));
            echo is_numeric($userid) ? 'true' : 'false';
        }else{
            $this->error('非法访问~');
        }
    }

    /**
     * 8.Ajax验证报单中心用户名是否存在，正确返回'true',否则返回'false'
     */
    public function checkUsernameisShop() {
        if(IS_AJAX){
            $userid = D('User')->checkUsernameisShop(I('post.shopusername'));
            echo $userid ? 'true' : 'false';
        }else{
            $this->error('非法访问~');
        }
    }
    
    /**
     * 9.Ajax验证会员用户名是否存在,满足条件返回'true',否则返回'false' - 找回密码
     */
    public function checkForgotUsername() {
        if(IS_AJAX){
            $userid = D('User')->checkForgotUsername(I('post.username'));
            echo $userid ? 'true' : 'false';
        }else{
            $this->error('非法访问~');
        }
    }
    
    /**
     * 10.Ajax验证会员手机号是否与输入的手机号一致，一致返回'true',否则返回'false'
     */
    public function checkForgotPhone() {
        if(IS_AJAX){
            $userid = D('User')->checkForgotPhone(strTrim(I('post.phone')), I('post.username'));
            echo $userid ? 'true' : 'false';
        }else{
            $this->error('非法访问~');
        }
    }

    /**
     * 11.验证身份证号的合法性
     */
    public function checkUserIdcard() {
        if(IS_AJAX){
            $userid = D('User')->checkUserIdcard(I('post.idcard'), I('post.type'));
            echo $userid ? 'false' : 'true';
        }else{
            $this->error('非法访问~');
        }
    }

    //Ajax验证数据，邮箱返回给Ajax
    public function checkVerify(){
        if(IS_AJAX){
            $uid = check_verify(I('post.verify'));
            echo $uid > 0 ? 'true' : 'false';
        }else{
            $this->error('非法访问!');
        }
    }
    
}