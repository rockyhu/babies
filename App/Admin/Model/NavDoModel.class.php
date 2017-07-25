<?php
namespace Admin\Model;
use Think\Model;

class NavDoModel extends Model{
	
    //自动验证
    protected $_validate = array();

    /**
     * @param $navid 菜单id
     * @return mixed 菜单操作数据
     */
    public function getSubNavDolist($navid) {
        return $this->field('id,text,url,navid,status')->where("navid='{$navid}'")->order('id ASC')->select();
    }

    /**
     * 添加菜单操作
     * @param $navid 菜单id
     * @param $text 菜单操作名称
     * @param $url 菜单操作链接
     * @return int|mixed|string
     */
	public function addNavDo($navid, $text, $url) {
	    $data = array(
	        'navid'=>$navid,
	        'text'=>$text,
	        'url'=>$url
	    );
	    if($this->create($data)){
	        $navdoid = $this->add();
	        return $navdoid ? $navdoid : 0;
	    }else{
	        return $this->getError();
	    }
	}

    /**
     * 编辑菜单操作
     * @param $id 菜单操作id
     * @param $text 菜单操作名称
     * @param $url 菜单操作链接
     * @return bool|int|string
     */
	public function editNavDo($id, $text, $url) {
	    $data = array(
	        'id'=>$id,
	        'text'=>$text,
	        'url'=>$url,
	    );
	    if($this->create($data)) {
	        $this->save();
	        return 1;
	    }else{
			return $this->getError();
		}
	}

    /**
     * 删除菜单操作
     * @param $id 菜单操作id
     * @return mixed
     */
	public function remove($id) {
        return $this->delete($id);
	}
	
}