<?php
namespace Admin\Model;
use Think\Auth;
use Think\Model;

class GroupRulesModel extends Model{
	
    //自动验证
    protected $_validate = array();

    /**
     * @param $group_id 管理员所在的用户组
     * @param $navid 当前所在栏目的id
     * @return array|void
     */
    public function getOneGroupRulesWithManageid($group_id, $navid) {
        if(empty($group_id)) return;
        $navdos = $this->where("groupid='{$group_id}'")->getField('navdos');
        $navdos = unserialize(stripcslashes($navdos));
        $urlArr = [];
        foreach ($navdos as $key=>$value) {
            if($key == $navid) {
                foreach ($value as $k=>$v) {
                    $urlArr[] = $v['url'];
                }
            }
        }
        return $urlArr;
    }

    /**
     * 获取角色权限配置
     * @param $groupid 角色id
     * @return array|mixed
     */
    public function getOneGroupRules($groupid) {
        $oneRules = $this->field('navdos')->where("groupid='{$groupid}'")->find();
        if($oneRules['navdos']) {
            $oneRules['navdos'] = unserialize(stripcslashes($oneRules['navdos']));
        }
        return $oneRules['navdos'] ? $oneRules['navdos'] : array();
    }

    /**
     * 添加角色权限分配
     * @param $groupid 角色id
     * @param $navdos 菜单操作集合
     * @return int|mixed|string
     */
	public function addGroupRules($groupid, $navdos = array()) {
        $id = $this->where("groupid='{$groupid}'")->getField('groupid');
        if($id) return $this->editGroupRules($groupid, $navdos);
	    $data = array(
	        'groupid'=>$groupid,
	        'navdos'=>serialize($navdos)
	    );
	    $this->add($data);
        return 1;
	}

    /**
     * 编辑角色权限分配
     * @param $groupid 角色id
     * @param $navdos 菜单操作集合
     * @return bool|int|string
     */
	public function editGroupRules($groupid, $navdos = array()) {
	    $data = array(
	        'navdos'=>serialize($navdos),
	    );
	    return $this->where("groupid='{$groupid}'")->setField($data);
	}

    /**
     * 删除角色权限分配
     * @param $groupid 角色id
     * @return mixed
     */
	public function remove($groupid) {
        return $this->where("groupid='{$groupid}'")->delete();
	}
	
}