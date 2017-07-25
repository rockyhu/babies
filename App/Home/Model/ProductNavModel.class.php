<?php
namespace Home\Model;
use Think\Model;

/**
 * 商品分类模型
 * @author rockyhu
 *
 */
class ProductNavModel extends Model{

    /**
     * 商品分类筛选
     * @return array
     */
	public function getCategoryList() {
		$categorylist = $this
            ->field('id,text,thumb,pnid,ishome')
            ->where("isshow=1")
            ->order('sort ASC')
            ->select();

        //返回分类数组
        $resultCategoryArr = [];
        //推荐分类
        $resultCategoryArr[] = [
            'id'=>'rec',
            'class'=>'on',
            'text'=>'推荐分类',
            'child'=>[[
                    'id'=>'genlisid3',
                    'text'=>'三星产品',
                    'thumb'=>C('TMPL_PARSE_STRING')['__IMG__'].'/star3.jpg',
                    'url'=>U('product/lists', array('key'=>'genlis','item'=>3))
                ],
                [
                    'id'=>'genlisid2',
                    'text'=>'二星产品',
                    'thumb'=>C('TMPL_PARSE_STRING')['__IMG__'].'/star2.jpg',
                    'url'=>U('product/lists', array('key'=>'genlis','item'=>2))
                ],
                [
                    'id'=>'genlisid1',
                    'text'=>'一星产品',
                    'thumb'=>C('TMPL_PARSE_STRING')['__IMG__'].'/star1.jpg',
                    'url'=>U('product/lists', array('key'=>'genlis','item'=>1))
                ]]
        ];
        foreach ($categorylist as $key=>$value) {
            //筛选出主分类
            if(!$value['pnid']) $resultCategoryArr[] = [
                'id'=>$value['id'],
                'text'=>$value['text'],
                'child'=>[[
                        'id'=>'genlisid3',
                        'text'=>'三星产品',
                        'thumb'=>C('TMPL_PARSE_STRING')['__IMG__'].'/star3.jpg',
                        'url'=>U('product/lists', array('key'=>'genlis','item'=>3))
                    ],
                    [
                        'id'=>'genlisid2',
                        'text'=>'二星产品',
                        'thumb'=>C('TMPL_PARSE_STRING')['__IMG__'].'/star2.jpg',
                        'url'=>U('product/lists', array('key'=>'genlis','item'=>2))
                    ],
                    [
                        'id'=>'genlisid1',
                        'text'=>'一星产品',
                        'thumb'=>C('TMPL_PARSE_STRING')['__IMG__'].'/star1.jpg',
                        'url'=>U('product/lists', array('key'=>'genlis','item'=>1))
                    ]]
            ];
            //筛选出推荐分类的子分类
            if($value['ishome']) $resultCategoryArr[0]['child'][] = [
                'id'=>$value['id'],
                'text'=>$value['text'],
                'thumb'=>$value['thumb'] ? C('SITE_URL').substr(json_decode($value['thumb'])->source,1) : '',
                'url'=>U('product/lists', $value['pnid'] ? array('pnid'=>$value['pnid'],'nid'=>$value['id']) : array('pnid'=>$value['id']))
            ];
        }
        //保存推荐分类的子分类
        $recCategorylist = $resultCategoryArr[0]['child'];
        //筛选出主分类下的子分类
        foreach ($categorylist as $key=>$value) {
            foreach ($resultCategoryArr as $k=>$v) {
                if($value['pnid'] == $v['id']) {
                    $resultCategoryArr[$k]['child'][] = [
                        'id'=>$value['id'],
                        'text'=>$value['text'],
                        'thumb'=>$value['thumb'] ? C('SITE_URL').substr(json_decode($value['thumb'])->source,1) : '',
                        'url'=>U('product/lists', array('pnid'=>$value['pnid'],'nid'=>$value['id']))
                    ];
                }
            }
        }
        foreach ($resultCategoryArr as $k=>$v) {
            $resultCategoryArr[$k]['child'] = json_encode($v['child']);
        }
        return [
            'recCategorylist'=>$recCategorylist,
            'categorylist'=>$resultCategoryArr
        ];
	}

	public function getCategoryListForLists() {
        $categorylist = $this
            ->field('id,text,pnid,ishome')
            ->where("isshow=1")
            ->order('sort ASC')
            ->select();
        //返回分类数组
        $resultCategoryArr = [];
        foreach ($categorylist as $key=>$value) {
            //筛选出主分类
            if(!$value['pnid']) $resultCategoryArr[] = [
                'id'=>$value['id'],
                'text'=>$value['text'],
                'url'=>U('product/lists', array('pnid'=>$value['id'])),
                'child'=>[]
            ];
        }
        //筛选出主分类下的子分类
        foreach ($categorylist as $key=>$value) {
            foreach ($resultCategoryArr as $k=>$v) {
                if($value['pnid'] == $v['id']) {
                    $resultCategoryArr[$k]['child'][] = [
                        'id'=>$value['id'],
                        'text'=>$value['text'],
                        'url'=>U('product/lists', array('pnid'=>$value['pnid'],'nid'=>$value['id'])),
                    ];
                }
            }
        }
        return $resultCategoryArr;
    }
	
	
}