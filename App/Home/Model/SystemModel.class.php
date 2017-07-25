<?php
namespace Home\Model;
use Think\Model;

/**
 * 系统参数模型
 * @author rockyhu
 *
 */
class SystemModel extends Model{

	/**
	 * 获取系统参数
	 */
    public function getSystem() {
        $system = $this->field('id,merchantpay,resellerpay,minicashout,cashoutfee,minitransfer,epursefee,gouwubifee,shutdownstate,shutdowntitle,shutdowncontent')->where("id=1")->find();
        $systemconfig = [];//系统配置信息
        if($system) {
            $systemconfig = array(
                'cashout'=>array(
                    'minicashout'=>$system['minicashout'],
                    'cashoutfee'=>$system['cashoutfee']
                ),
                'merchantpay'=>$system['merchantpay'],
                'resellerpay'=>$system['resellerpay'],
                'epursefee'=>$system['epursefee']/100,
                'gouwubifee'=>$system['gouwubifee']/100,
                'minitransfer'=>$system['minitransfer'],
                'shutdown'=>array(
                    'shutdownstate'=>$system['shutdownstate'],
                    'shutdowntitle'=>$system['shutdowntitle'],
                    'shutdowncontent'=>$system['shutdowncontent']
                )
            );
        }
        return $systemconfig;
    }
	
}