<?php
namespace Home\Model;
use Think\Model;

/**
 * 产品让利等级模型
 * @author rockyhu
 *
 */
class GenlisModel extends Model{

    /**
     * 获取产品让利等级{id:genlisfee}
     */
    public function getProductGenlislist() {
        $list = $this->field('id,genlisfee')->order('id ASC')->select();
        $newlist = [];
        foreach ($list as $key=>$value) {
            $newlist[$value['id']] = $value['genlisfee'];
        }
        return $newlist;
    }

    /**
     * 获取产品让利等级{id:genlisname}
     */
    public function getProductGenlisNamelist() {
        $list = $this->field('id,genlisname')->order('id ASC')->select();
        $newlist = [];
        foreach ($list as $key=>$value) {
            $newlist[$value['id']] = $value['genlisname'];
        }
        return $newlist;
    }

    /**
     * 根据商家的销售额获取商家的增值比例
     * @param $shopsales 商家累计销售额
     */
    public function getProductGenlisForShop() {
        $list = $this->field('id,shoptotalallreturnfee1,shoptotalallreturnfee2,shoptotalallreturnfee3')->order('id ASC')->select();
        $newlist = [];
        foreach ($list as $key=>$value) {
            $newlist[$value['id']] = [
                '1'=>$value['shoptotalallreturnfee1']/100,
                '2'=>$value['shoptotalallreturnfee2']/100,
                '3'=>$value['shoptotalallreturnfee3']/100
            ];
        }
        return $newlist;
    }

    /**
     * 获取产品让利等级的激励比例
     * @return array
     */
    public function getProductGenlisJili() {
        $list = $this->field('id,xfallreturnfee,shopallreturnfee')->order('id ASC')->select();
        $newlist = [];
        foreach ($list as $key=>$value) {
            $newlist['user'][$value['id']] = $value['xfallreturnfee']/100;
            $newlist['shop'][$value['id']] = $value['shopallreturnfee']/100;
        }
        return $newlist;
    }

    /**
     * 获取产品让利等级对应招商员等级商家销售分成比例
     */
    public function getProductMerGenlis() {
        $list = $this->field('id,merchantfee1,merchantfee2')->order('id ASC')->select();
        $newlist = [];
        foreach ($list as $key=>$value) {
            $newlist[$value['id']] = [
                'merchantfee1'=>$value['merchantfee1']/100,
                'merchantfee2'=>$value['merchantfee2']/100
            ];
        }
        return $newlist;
    }

}