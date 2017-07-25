<?php
namespace Home\Model;
use Think\Model;

/**
 * 会员等级模型
 * @author rockyhu
 *
 */
class AgentModel extends Model{

    /**
     * 获取会员等级增值比例
     */
	public function getUserAgentList($agentid) {
        $oneAgentinfo = $this->field('genlis1totalallreturnfee,genlis2totalallreturnfee,genlis3totalallreturnfee')->where("id='{$agentid}'")->find();
        return [
            '1'=>$oneAgentinfo['genlis1totalallreturnfee']/100,
            '2'=>$oneAgentinfo['genlis2totalallreturnfee']/100,
            '3'=>$oneAgentinfo['genlis3totalallreturnfee']/100
        ];
    }

    /**
     * 获取会员等级列表
     * @return array
     */
    public function getAgentLevelArr() {
        $list = $this->field('id,minpv,maxpv')->order('id ASC')->select();
        $newArr = [];
        foreach ($list as $key=>$value) {
            $newArr[$value['id']] = [
                'min'=>$value['minpv'],
                'max'=>$value['maxpv']
            ];
        }
        print_r($newArr);
        return $newArr;
    }
	
}