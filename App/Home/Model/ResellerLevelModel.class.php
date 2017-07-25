<?php
namespace Home\Model;
use Think\Model;

/**
 * 分销商等级模型
 * @author rockyhu
 *
 */
class ResellerLevelModel extends Model{

    /**
     * 获取分销商id 对应分销商等级的一星产品、二星产品、三星产品的分销参数
     * @param $userid 分销商id
     * @return array('星级产品id'=>array('第一级'=>'','第二级'=>''))
     */
    public function getAllResellerLevelArr($userid) {
        $resellerlevelinfo = $this
            ->join(array('a LEFT JOIN __RESELLER__ b ON a.id=b.level'))
            ->field('a.genlis1toonefee,a.genlis1totwofee,a.genlis2toonefee,a.genlis2totwofee,a.genlis3toonefee,a.genlis3totwofee')
            ->where("b.userid='{$userid}'")
            ->find();
        $newResellerlevelArr = [];
        if($resellerlevelinfo) {
            $newResellerlevelArr[1] = [
                '1'=>$resellerlevelinfo['genlis1toonefee'],
                '2'=>$resellerlevelinfo['genlis1totwofee']
            ];
            $newResellerlevelArr[2] = [
                '1'=>$resellerlevelinfo['genlis2toonefee'],
                '2'=>$resellerlevelinfo['genlis2totwofee']
            ];
            $newResellerlevelArr[3] = [
                '1'=>$resellerlevelinfo['genlis3toonefee'],
                '2'=>$resellerlevelinfo['genlis3totwofee']
            ];
        }
        return $newResellerlevelArr;
    }
	
	
}