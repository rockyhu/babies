<?php
namespace Admin\Model;
use Think\Model;

class AuthRuleModel extends Model {
	
	//获取所有规则
	public function getListAll() {
		return $this->field('id,name,title')->select();
	}
	
	//新增规则
	public function addRule($name, $title) {
		 $data = array(
		     'name'=>$name,
		     'title'=>$title
		 );
	     if($this->create($data)){
	        $rid = $this->add();
	        return $rid ? $rid : 0;
	    }else{
	        return $this->getError();
	    }
	}
	
	//编辑规则
	public function editRule($name, $title, $oriTitle) {
	    $map['title'] = $oriTitle;
	    $data = array(
	        'name'=>$name,
	        'title'=>$title
	    );
	    $state = $this->where($map)->setField($data);
        if(empty($state)) {
            $ruleid = $this->addRule($name, $title);
            if($ruleid>0) {
                return D('AuthGroup')->updateRole(session('admin.id'), $ruleid, true);
            }
        }else {
            return $state;
        }
	}
	
	//删除规则
	public function remove($title) {
	    $map['title'] = $title;
	    $ruleid = $this->where($map)->getField('id');
	    if($ruleid>0){
	        D('AuthGroup')->updateRole(session('admin.id'), $ruleid, false);
            return $this->delete($ruleid);
	    }
	}
}