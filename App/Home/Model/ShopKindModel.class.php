<?php
namespace Home\Model;
use Think\Model;

/**
 * 商户类别模型
 * @author rockyhu
 *
 */
class ShopKindModel extends Model{

    /**
     * 获取系统商户类别
     * @return mixed
     */
    public function getShopKindList() {
        return $this->field('id,text,info')->select();
    }
	
	
}