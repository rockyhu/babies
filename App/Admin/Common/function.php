<?php
/**
 * 将数组格式array('id'=> , 'referee'=> , 'username'=> )生成树形数组格式
 * array('id'=> , 'referee'=> , 'username'=> , childrens => array() )
 */
function returnArray($result){
    $newResult = array();
    if(!empty($result)){
        foreach ($result as $k=>$v) {
            $arrTep = $v;
            $arrTep['childrens'] = array();
            //父类ID是0时，代表没有父类ID，为根节点
            if(0 == $v['referee']){
                $newResult[] = $arrTep;
                continue;
            }
            if(0 != $v['referee']){
                //添加不入数组中的子节点，可能是没有父类节点，那么将其当成根节点
                if(false === updateArray($newResult, $arrTep)){
                    $arrTep = array('id'=> $arrTep['id'], 'referee'=>0, 'username'=>$arrTep['username'], 'childrens'=>array($arrTep));
                    $newResult[] = $arrTep;
                }
            }
        }
    }
    return $newResult;
    //echo json_encode($newResult);
}

/**
 * 将当前节点插入到新的树形数组中
 * @param $newResult 树形数组地址
 * @param $arrTep 当前节点
 */
function updateArray(&$newResult, $arrTep){
    if(!empty($newResult)){
        foreach ($newResult as $k=>$v) {
            //查询当前节点的id是否与新的树形数组的id一致，如果是，那么将当前节点存放在树形数组的childrens字段中
            if($v['id'] == $arrTep['referee']){
                $newResult[$k]['childrens'][] = $arrTep;
                return true;
            }elseif(!empty($v['childrens'])){
                //递归调用，查询树形数组的子节点与当前节点的关系
                if(true === updateArray($newResult[$k]['childrens'], $arrTep)){
                    return true;
                }
            }
        }
    }
    return false;
}

/**
 * 获取当前页面的查看地址
 * @return string
 */
function getCurrentUrl() {
    if(APP_DEBUG){//调试阶段
        $url = ucfirst(substr(__CONTROLLER__, strpos(__CONTROLLER__, 'Admin')+6)).'/index';
    }else{//部署阶段
        $url = substr(__CONTROLLER__, strpos(__CONTROLLER__, 'admin')+6);
        if(strpos($url,'_') !== false){
            $pos = strpos($url,'_');
            $url = ucfirst(substr($url, 0, $pos)).ucfirst(substr($url, $pos+1)).'/index';
        }else{
            $url = ucfirst($url).'/index';
        }
    }
    return $url;
}

/**
 * 导出excel数据
 * @param unknown $expTitle excel文件名称
 * @param unknown $expCellName excel表格标题
 * @param unknown $expTableData excel表格数据
 */
function exportExcel($expTitle, $expCellName, $expTableData){
    $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);
    $fileName = $expTitle.date('Y-m-d H-i-s');
    $cellNum = count($expCellName);
    $dataNum = count($expTableData);

    vendor("PHPExcel.PHPExcel");

    $objPHPExcel = new \PHPExcel();
    $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
    for($i=0; $i<$cellNum; $i++){
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
        $objPHPExcel->getActiveSheet(0)->getColumnDimension($cellName[$i])->setAutoSize(true);
        $objPHPExcel->getActiveSheet(0)->getStyle($cellName[$i].'1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet(0)->getStyle($cellName[$i].'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet(0)->getStyle($cellName[$i].'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    }
    for($i=0; $i<$dataNum; $i++){
        for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]]);
            $objPHPExcel->getActiveSheet(0)->getColumnDimension($cellName[$j])->setAutoSize(true);
            $objPHPExcel->getActiveSheet(0)->getStyle($cellName[$j].($i+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet(0)->getStyle($cellName[$j].($i+2))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }
    }
    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
    header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit();
}

/**
 * 将数组json转换成数组字符串
 * @param $arrayJson 数组json
 * @return string
 */
function arrayJsonToArray($arrayJson) {
    $newarray = [];
    foreach ($arrayJson as $key=>$json) {
        $newarray[] = json_decode($json);
    }
    return json_encode($newarray);
}

function imgurl($url) {
    if(empty($url)) return '';
    if(strpos($url, 'http://') !== false) {
        return str_replace('http://www.abc769.com', '.', $url);
    }else {
        return './attachment/'.$url;
    }
}