<?php
namespace Admin\Controller;

class MenuController extends AuthController
{

    public function index()
    {
        $Wechat = A('Wechat');
        $menu = $Wechat->getMenu();
        if(!is_array($menu)) {
            $menu['menu']['button'] = [];
        }
        // 数组格式
        $this->assign('menusArr', $menu['menu']['button']);
        // json格式
        $this->assign('menus', json_encode($menu['menu']['button']));
        $this->display();
    }

    public function getmedia()
    {
        $Wechat = A('Wechat');
        print_r($Wechat->getForeverList());
    }

    public function saveMenu()
    {
        if (IS_AJAX) {
            $menu = htmlspecialchars_decode(I('post.menu'));
            $button = [
                'button' => json_decode($menu, true)
            ];
            $Wechat = A('Wechat');
            echo $Wechat->createMenu($button) ? 1 : 0;
        } else {
            $this->error('非法操作！');
        }
    }
}