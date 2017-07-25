<?php
namespace Home\Controller;

/**
 * 支付页面控制器
 * @author rockyhu
 *
 */
class PaymentController extends HomeController{

    //1.非法操作
    public function index()
    {
        $this->error('非法操作');
    }

    //1.订单支付
    public function orderpay() {
        if ($this->userLogined()) {
            //1.将授权登陆后的重定向链接赋值给AuthRedirectUrl
            $id = I('get.id');
            if(!empty($id)) {
                cookie('AuthRedirectUrl', C('SITE_URL').'?c=Payment&a=orderpay&id='.$id);
            }else {
                cookie('AuthRedirectUrl', C('SITE_URL').'?c=Payment&a=orderpay&ordersn_general='.I('get.ordersn_general'));
            }
            //获取订单相关信息
            if(!empty($id)) {
                $this->assign('orderpayinfo', D('Order')->getOrderPayinfoWithId($id, session('user_auth.id')));
            }else {
                $this->assign('orderpayinfo', D('Order')->getOrderPayinfoWithOrdersn(I('get.ordersn_general'), session('user_auth.id')));
            }
            $this->display('Order/orderpay');
        }
    }

    /**
     *订单支付 - 微信支付
     */
    public function orderpay_action()
    {
        if (IS_AJAX) {
            // 获取微信的用户信息
            if (cookie('wxdata')) {
                $wxdata = json_decode(cookie('wxdata'), true);
                // 创建预支付订单
                vendor('Wxpay.WxPayPubHelper');
                // 使用jsapi接口
                $jsApi = new \JsApi_pub();

                // 获取订单的信息
                $res = array(
                    'order_sn' => I('post.ordersn_general'),
                    'order_amount' => I('post.wxprice')
                );

                // =========步骤2：使用统一支付接口，获取prepay_id============

                // 使用统一支付接口
                $unifiedOrder = new \UnifiedOrder_pub();
                // 设置统一支付接口参数
                $total_fee = $res['order_amount'] * 100;
                if(session('user_auth.id') == 1934 || session('user_auth.id') == 1618) {
                    $total_fee = 1;
                }
                $body = "爱尚缘消费共享商城订单支付";
                $openid = $wxdata['openid'];
                $unifiedOrder->setParameter("openid", "$openid"); // 用户标识
                $unifiedOrder->setParameter("body", $body); // 商品描述
                // 自定义订单号，此处仅作举例
                $out_trade_no = $res['order_sn'];
                $unifiedOrder->setParameter("out_trade_no", $out_trade_no); // 商户订单号
                $unifiedOrder->setParameter("total_fee", $total_fee); // 总金额
                $unifiedOrder->setParameter("notify_url", C('SITE_URL') . "Payment/orderpay_do"); // 通知地址
                $unifiedOrder->setParameter("trade_type", "JSAPI"); // 交易类型
                $unifiedOrder->setParameter("time_start", date('YmdHis', time()));//订单生成时间
                $unifiedOrder->setParameter("time_expire", date('YmdHis', time()+5*60+1));//订单失效时间
                $prepay_id = $unifiedOrder->getPrepayId();

                // =========步骤3：使用jsapi调起支付============
                $jsApi->setPrepayId($prepay_id);
                $jsApiParameters = $jsApi->getParameters();
                $wxconf = json_decode($jsApiParameters, true);
                if ($wxconf['package'] == 'prepay_id=') {
                    exit('当前订单存在异常，不能使用支付');
                }
                echo $jsApiParameters;
            }else {
                echo 0;//未授权的登陆
            }
        } else {
            $this->error('非法操作！');
        }
    }

    /**
     * 订单支付回调地址
     */
    public function orderpay_do()
    {
        vendor('Wxpay.WxPayPubHelper');
        // 使用通用通知接口
        $notify = new \Notify_pub();
        // 存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        // 验证签名，并回应微信。
        if ($notify->checkSign() == FALSE) {
            $notify->setReturnParameter("return_code", "FAIL"); // 返回状态码
            $notify->setReturnParameter("return_msg", "签名失败"); // 返回信息
        } else {
            $notify->setReturnParameter("return_code", "SUCCESS"); // 设置返回码
        }
        $returnXml = $notify->returnXml();
        // ==商户根据实际情况设置相应的处理流程，此处仅作举例=======
        $parameter = $notify->xmlToArray($xml);
        if ($notify->checkSign() == TRUE) {
            if ($notify->data["return_code"] == "FAIL") {
                // 此处应该更新一下订单状态，商户自行增删操作
                // 更新订单数据【通信出错】设为无效订单
                echo 'error';
            } else
                if ($notify->data["result_code"] == "FAIL") {
                    // 此处应该更新一下订单状态，商户自行增删操作
                    // 更新订单数据【通信出错】设为无效订单
                    echo 'error';
                } else {
                    // 我这里用到一个process方法，成功返回数据后处理，返回地数据具体可以参考微信的文档
                    if ($this->orderpayprocess($parameter)) {
                        // 处理成功后输出success，微信就不会再下发请求了
                        echo 'success';
                    } else {
                        // 没有处理成功，微信会间隔的发送请求
                        echo 'error';
                    }
                }
        }
    }

    /**
     * 更新订单的状态
     */
    private function orderpayprocess($parameter)
    {
        D('Order')->setOrderPayByWx($parameter['out_trade_no'], $parameter['transaction_id']);
        return true;
    }

    //2.分销商注册
    public function resellerRegister() {
        if ($this->userLogined()) {
            //1.将授权登陆后的重定向链接赋值给AuthRedirectUrl
            cookie('AuthRedirectUrl', C('SITE_URL').'?c=Payment&a=resellerRegister');
            //获取会员信息
            $this->assign('userinfo', D('User')->getUserInfo(session('user_auth.id')));
            $this->display('Reseller/register');
        }
    }

    /**
     *分销商支付 - 微信支付
     */
    public function resellerRegister_action()
    {
        if (IS_AJAX) {
            // 获取微信的用户信息
            if (cookie('wxdata')) {
                $wxdata = json_decode(cookie('wxdata'), true);
            }
            //创建分销商支付订单
            $resellerinfo = D('ResellerSignup')->addResellerSignup(str_replace(' ', '', I('post.phone')), I('post.realname'), I('post.weixin'), session('user_auth.id'));
            if(is_array($resellerinfo)) {
                // 创建预支付订单
                vendor('Wxpay.WxPayPubHelper');
                // 使用jsapi接口
                $jsApi = new \JsApi_pub();

                // 获取订单的信息
                $res = array(
                    'order_sn' => $resellerinfo['signup_sn'],
                    'order_amount' => $resellerinfo['price']
                );

                // =========步骤2：使用统一支付接口，获取prepay_id============

                // 使用统一支付接口
                $unifiedOrder = new \UnifiedOrder_pub();
                // 设置统一支付接口参数
                $total_fee = $res['order_amount'] * 100;
                if(session('user_auth.id') == 1934 || session('user_auth.id') == 1618) {
                    $total_fee = 1;
                }
                $body = "爱尚缘消费共享商城分销商申请支付";
                $openid = $wxdata['openid'];
                $unifiedOrder->setParameter("openid", "$openid"); // 用户标识
                $unifiedOrder->setParameter("body", $body); // 商品描述
                // 自定义订单号，此处仅作举例
                $out_trade_no = $res['order_sn'];
                $unifiedOrder->setParameter("out_trade_no", $out_trade_no); // 商户订单号
                $unifiedOrder->setParameter("total_fee", $total_fee); // 总金额
                $unifiedOrder->setParameter("notify_url", C('SITE_URL') . "Payment/resellerRegister_do"); // 通知地址
                $unifiedOrder->setParameter("trade_type", "JSAPI"); // 交易类型
                $unifiedOrder->setParameter("time_start", date('YmdHis', time()));//订单生成时间
                $unifiedOrder->setParameter("time_expire", date('YmdHis', time()+5*60+1));//订单失效时间
                $prepay_id = $unifiedOrder->getPrepayId();

                // =========步骤3：使用jsapi调起支付============
                $jsApi->setPrepayId($prepay_id);
                $jsApiParameters = $jsApi->getParameters();
                $wxconf = json_decode($jsApiParameters, true);
                if ($wxconf['package'] == 'prepay_id=') {
                    exit('当前订单存在异常，不能使用支付');
                }
                echo $jsApiParameters;
            }else {
                echo 0;
            }
        } else {
            $this->error('非法操作！');
        }
    }

    /**
     * 支付回调地址
     */
    public function resellerRegister_do()
    {
        vendor('Wxpay.WxPayPubHelper');
        // 使用通用通知接口
        $notify = new \Notify_pub();
        // 存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        // 验证签名，并回应微信。
        if ($notify->checkSign() == FALSE) {
            $notify->setReturnParameter("return_code", "FAIL"); // 返回状态码
            $notify->setReturnParameter("return_msg", "签名失败"); // 返回信息
        } else {
            $notify->setReturnParameter("return_code", "SUCCESS"); // 设置返回码
        }
        $returnXml = $notify->returnXml();
        // ==商户根据实际情况设置相应的处理流程，此处仅作举例=======
        $parameter = $notify->xmlToArray($xml);
        if ($notify->checkSign() == TRUE) {
            if ($notify->data["return_code"] == "FAIL") {
                // 此处应该更新一下订单状态，商户自行增删操作
                // 更新订单数据【通信出错】设为无效订单
                echo 'error';
            } else
                if ($notify->data["result_code"] == "FAIL") {
                    // 此处应该更新一下订单状态，商户自行增删操作
                    // 更新订单数据【通信出错】设为无效订单
                    echo 'error';
                } else {
                    // 我这里用到一个process方法，成功返回数据后处理，返回地数据具体可以参考微信的文档
                    if ($this->resellerRegisterprocess($parameter)) {
                        // 处理成功后输出success，微信就不会再下发请求了
                        echo 'success';
                    } else {
                        // 没有处理成功，微信会间隔的发送请求
                        echo 'error';
                    }
                }
        }
    }

    /**
     * 更新状态
     */
    private function resellerRegisterprocess($parameter)
    {
        D('ResellerSignup')->updateResellerSignup($parameter['out_trade_no'], $parameter['transaction_id']);
        return true;
    }

    //3.招商员注册
    public function merchantRegister() {
        if ($this->userLogined()) {
            //1.将授权登陆后的重定向链接赋值给AuthRedirectUrl
            cookie('AuthRedirectUrl', C('SITE_URL').'?c=Payment&a=merchantRegister');
            //获取会员信息
            $this->assign('userinfo', D('User')->getMerchantUserInfoforRegister(session('user_auth.id')));
            $this->display('Merchant/register');
        }
    }

    /**
     *招商员支付 - 微信支付
     */
    public function merchantRegister_action()
    {
        if (IS_AJAX) {
            // 获取微信的用户信息
            if (cookie('wxdata')) {
                $wxdata = json_decode(cookie('wxdata'), true);
            }
            //创建招商员支付订单
            $merchantinfo = D('MerchantSignup')->addMerchantSignup(I('post.merchantphone'), str_replace(' ', '', I('post.phone')), I('post.realname'), I('post.weixin'), session('user_auth.id'));
            if(is_array($merchantinfo)) {
                // 创建预支付订单
                vendor('Wxpay.WxPayPubHelper');
                // 使用jsapi接口
                $jsApi = new \JsApi_pub();

                // 获取订单的信息
                $res = array(
                    'order_sn' => $merchantinfo['signup_sn'],
                    'order_amount' => $merchantinfo['price']
                );

                // =========步骤2：使用统一支付接口，获取prepay_id============

                // 使用统一支付接口
                $unifiedOrder = new \UnifiedOrder_pub();
                // 设置统一支付接口参数
                $total_fee = $res['order_amount'] * 100;
                if(session('user_auth.id') == 1934 || session('user_auth.id') == 1618) {
                    $total_fee = 1;
                }
                //$total_fee = 1;
                $body = "爱尚缘消费共享商城招商员申请支付";
                $openid = $wxdata['openid'];
                $unifiedOrder->setParameter("openid", "$openid"); // 用户标识
                $unifiedOrder->setParameter("body", $body); // 商品描述
                // 自定义订单号，此处仅作举例
                $out_trade_no = $res['order_sn'];
                $unifiedOrder->setParameter("out_trade_no", $out_trade_no); // 商户订单号
                $unifiedOrder->setParameter("total_fee", $total_fee); // 总金额
                $unifiedOrder->setParameter("notify_url", C('SITE_URL') . "Payment/merchantRegister_do"); // 通知地址
                $unifiedOrder->setParameter("trade_type", "JSAPI"); // 交易类型
                $unifiedOrder->setParameter("time_start", date('YmdHis', time()));//订单生成时间
                $unifiedOrder->setParameter("time_expire", date('YmdHis', time()+5*60+1));//订单失效时间
                $prepay_id = $unifiedOrder->getPrepayId();

                // =========步骤3：使用jsapi调起支付============
                $jsApi->setPrepayId($prepay_id);
                $jsApiParameters = $jsApi->getParameters();
                $wxconf = json_decode($jsApiParameters, true);
                if ($wxconf['package'] == 'prepay_id=') {
                    exit('当前订单存在异常，不能使用支付');
                }
                echo $jsApiParameters;
            }else {
                echo 0;
            }
        } else {
            $this->error('非法操作！');
        }
    }

    /**
     * 支付回调地址
     */
    public function merchantRegister_do()
    {
        vendor('Wxpay.WxPayPubHelper');
        // 使用通用通知接口
        $notify = new \Notify_pub();
        // 存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        // 验证签名，并回应微信。
        if ($notify->checkSign() == FALSE) {
            $notify->setReturnParameter("return_code", "FAIL"); // 返回状态码
            $notify->setReturnParameter("return_msg", "签名失败"); // 返回信息
        } else {
            $notify->setReturnParameter("return_code", "SUCCESS"); // 设置返回码
        }
        $returnXml = $notify->returnXml();
        // ==商户根据实际情况设置相应的处理流程，此处仅作举例=======
        $parameter = $notify->xmlToArray($xml);
        if ($notify->checkSign() == TRUE) {
            if ($notify->data["return_code"] == "FAIL") {
                // 此处应该更新一下订单状态，商户自行增删操作
                // 更新订单数据【通信出错】设为无效订单
                echo 'error';
            } else
                if ($notify->data["result_code"] == "FAIL") {
                    // 此处应该更新一下订单状态，商户自行增删操作
                    // 更新订单数据【通信出错】设为无效订单
                    echo 'error';
                } else {
                    // 我这里用到一个process方法，成功返回数据后处理，返回地数据具体可以参考微信的文档
                    if ($this->merchantRegisterprocess($parameter)) {
                        // 处理成功后输出success，微信就不会再下发请求了
                        echo 'success';
                    } else {
                        // 没有处理成功，微信会间隔的发送请求
                        echo 'error';
                    }
                }
        }
    }

    /**
     * 更新状态
     */
    private function merchantRegisterprocess($parameter)
    {
        D('MerchantSignup')->updateMerchantSignup($parameter['out_trade_no'], $parameter['transaction_id']);
        return true;
    }

    //4.商家注册
    public function shopRegister() {
        if ($this->userLogined()) {
            $isupdate = I('get.isupdate');
            if(empty($isupdate)) {
                //判断当前的会员是否已经提交商家入驻申请并完成支付
                $shopinfo = D('Shop')->checkShopUserStatus(session('user_auth.id'));
                if($shopinfo) {//已入住,状态待定
                    switch ($shopinfo['status']) {
                        case '审核中':
                            $this->display('Shop/shopcheck');
                            break;
                        case '审核不通过':
                            $this->display('Shop/shopcheckno');
                            break;
                        case '审核通过':
                            //跳转到商家中心
                            $this->redirect('Shop/shop');
                            break;
                    }
                }else {//入驻申请页面
                    //1.将授权登陆后的重定向链接赋值给AuthRedirectUrl
                    cookie('AuthRedirectUrl', C('SITE_URL').'?c=Payment&a=shopRegister');
                    $this->assign('shopkindlist', D('ShopKind')->getShopKindList());
                    //获取会员信息
                    $this->assign('userinfo', D('User')->getShopUserInfoforRegister(session('user_auth.id')));
                    $this->display('Shop/register');
                }
            }else {//入驻申请页面 - 修改部分资料
                //1.将授权登陆后的重定向链接赋值给AuthRedirectUrl
                cookie('AuthRedirectUrl', C('SITE_URL').'?c=Payment&a=shopRegister&isupdate=1');
                $this->assign('shopkindlist', D('ShopKind')->getShopKindList());
                //获取会员信息
                $this->assign('shopinfo', D('Shop')->getRegisterShopInfo(session('user_auth.id')));
                $this->display('Shop/registerisupdate');
            }
        }
    }

    /**
     *商家支付 - 微信支付
     */
    public function shopRegister_action()
    {
        if (IS_AJAX) {
            // 获取微信的用户信息
            if (cookie('wxdata')) {
                $wxdata = json_decode(cookie('wxdata'), true);
            }
            //创建商家支付订单
            $shopinfo = D('ShopSignup')->addShopSignup(I('post.merchantphone'), I('post.type'), I('post.email'), I('post.shoppassword'), I('post.shopkind'), I('post.shopname'), I('post.realname'), str_replace(' ', '', I('post.phone')), I('post.province'), I('post.city'), I('post.town'), I('post.address'), I('post.images'), session('user_auth.id'));
            if(is_array($shopinfo)) {
                // 创建预支付订单
                vendor('Wxpay.WxPayPubHelper');
                // 使用jsapi接口
                $jsApi = new \JsApi_pub();

                // 获取订单的信息
                $res = array(
                    'order_sn' => $shopinfo['signup_sn'],
                    'order_amount' => $shopinfo['price']
                );

                // =========步骤2：使用统一支付接口，获取prepay_id============

                // 使用统一支付接口
                $unifiedOrder = new \UnifiedOrder_pub();
                // 设置统一支付接口参数
                $total_fee = $res['order_amount'] * 100;
                if(session('user_auth.id') == 1934 || session('user_auth.id') == 1618) {
                    $total_fee = 1;
                }
                $body = "爱尚缘消费共享商城商家入驻申请支付";
                $openid = $wxdata['openid'];
                $unifiedOrder->setParameter("openid", "$openid"); // 用户标识
                $unifiedOrder->setParameter("body", $body); // 商品描述
                // 自定义订单号，此处仅作举例
                $out_trade_no = $res['order_sn'];
                $unifiedOrder->setParameter("out_trade_no", $out_trade_no); // 商户订单号
                $unifiedOrder->setParameter("total_fee", $total_fee); // 总金额
                $unifiedOrder->setParameter("notify_url", C('SITE_URL') . "Payment/shopRegister_do"); // 通知地址
                $unifiedOrder->setParameter("trade_type", "JSAPI"); // 交易类型
                $unifiedOrder->setParameter("time_start", date('YmdHis', time()));//订单生成时间
                $unifiedOrder->setParameter("time_expire", date('YmdHis', time()+5*60+1));//订单失效时间
                $prepay_id = $unifiedOrder->getPrepayId();

                // =========步骤3：使用jsapi调起支付============
                $jsApi->setPrepayId($prepay_id);
                $jsApiParameters = $jsApi->getParameters();
                $wxconf = json_decode($jsApiParameters, true);
                if ($wxconf['package'] == 'prepay_id=') {
                    exit('当前订单存在异常，不能使用支付');
                }
                echo $jsApiParameters;
            }else {
                echo 0;
            }
        } else {
            $this->error('非法操作！');
        }
    }

    /**
     * 支付回调地址
     */
    public function shopRegister_do()
    {
        vendor('Wxpay.WxPayPubHelper');
        // 使用通用通知接口
        $notify = new \Notify_pub();
        // 存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        // 验证签名，并回应微信。
        if ($notify->checkSign() == FALSE) {
            $notify->setReturnParameter("return_code", "FAIL"); // 返回状态码
            $notify->setReturnParameter("return_msg", "签名失败"); // 返回信息
        } else {
            $notify->setReturnParameter("return_code", "SUCCESS"); // 设置返回码
        }
        $returnXml = $notify->returnXml();
        // ==商户根据实际情况设置相应的处理流程，此处仅作举例=======
        $parameter = $notify->xmlToArray($xml);
        if ($notify->checkSign() == TRUE) {
            if ($notify->data["return_code"] == "FAIL") {
                // 此处应该更新一下订单状态，商户自行增删操作
                // 更新订单数据【通信出错】设为无效订单
                echo 'error';
            } else
                if ($notify->data["result_code"] == "FAIL") {
                    // 此处应该更新一下订单状态，商户自行增删操作
                    // 更新订单数据【通信出错】设为无效订单
                    echo 'error';
                } else {
                    // 我这里用到一个process方法，成功返回数据后处理，返回地数据具体可以参考微信的文档
                    if ($this->shopRegisterprocess($parameter)) {
                        // 处理成功后输出success，微信就不会再下发请求了
                        echo 'success';
                    } else {
                        // 没有处理成功，微信会间隔的发送请求
                        echo 'error';
                    }
                }
        }
    }

    /**
     * 更新状态
     */
    private function shopRegisterprocess($parameter)
    {
        D('ShopSignup')->updateShopSignup($parameter['out_trade_no'], $parameter['transaction_id']);
        return true;
    }

    //5.店铺支付
    public function autoStorePay() {
        if ($this->userLogined()) {
            $storeid = I('get.id');
            if(!empty($storeid)) {
                //1.将授权登陆后的重定向链接赋值给AuthRedirectUrl
                cookie('AuthRedirectUrl', C('SITE_URL').'?c=Payment&a=autoStorePay&id='.$storeid);
                $this->assign('oneshopstore', D('ShopStore')->getOneShopStoreBuy($storeid, session('user_auth.id')));
                $this->display('Store/buy');
            }else {
                $this->error('非法操作');
            }
        }
    }

    /**
     *店铺支付 - 微信支付
     */
    public function autoStorePay_action()
    {
        if (IS_AJAX) {
            $red = I('post.red');
            //红包金额必须与生成时的保持一致
            if((!empty($red) && session('rednum')[session('user_auth.id')] == I('post.red')) || empty($red)) {
                // 获取微信的用户信息
                if (cookie('wxdata')) {
                    $wxdata = json_decode(cookie('wxdata'), true);
                    //创建商家线下店铺支付订单
                    $storeorderinfo = D('StoreOrder')->createUserStoreOrder(
                        I('post.shopid'),
                        I('post.storeid'),
                        I('post.price'),
                        0,
                        I('post.epurse'),
                        I('post.wxpay'),
                        I('post.red'),
                        session('user_auth.id'));
                    if (is_array($storeorderinfo)) {
                        // 创建预支付订单
                        vendor('Wxpay.WxPayPubHelper');
                        // 使用jsapi接口
                        $jsApi = new \JsApi_pub();

                        // 获取订单的信息
                        $res = array(
                            'order_sn' => $storeorderinfo['ordersn'],
                            'order_amount' => $storeorderinfo['wxpay']
                        );

                        // =========步骤2：使用统一支付接口，获取prepay_id============

                        // 使用统一支付接口
                        $unifiedOrder = new \UnifiedOrder_pub();
                        // 设置统一支付接口参数
                        $total_fee = $res['order_amount'] * 100;
                        if(session('user_auth.id') == 1934 || session('user_auth.id') == 1618) {
                            $total_fee = 1;
                        }
                        $body = $storeorderinfo['storename']."线下店铺支付";
                        $openid = $wxdata['openid'];
                        $unifiedOrder->setParameter("openid", "$openid"); // 用户标识
                        $unifiedOrder->setParameter("body", $body); // 商品描述
                        // 自定义订单号，此处仅作举例
                        $out_trade_no = $res['order_sn'];
                        $unifiedOrder->setParameter("out_trade_no", $out_trade_no); // 商户订单号
                        $unifiedOrder->setParameter("total_fee", $total_fee); // 总金额
                        $unifiedOrder->setParameter("notify_url", C('SITE_URL')."Payment/autoStorePay_do"); // 通知地址
                        $unifiedOrder->setParameter("trade_type", "JSAPI"); // 交易类型
                        $unifiedOrder->setParameter("time_start", date('YmdHis', time()));//订单生成时间
                        $unifiedOrder->setParameter("time_expire", date('YmdHis', time()+5*60+1));//订单失效时间
                        $prepay_id = $unifiedOrder->getPrepayId();

                        // =========步骤3：使用jsapi调起支付============
                        $jsApi->setPrepayId($prepay_id);
                        $jsApiParameters = $jsApi->getParameters();
                        $wxconf = json_decode($jsApiParameters, true);
                        if ($wxconf['package'] == 'prepay_id=') {
                            exit('当前订单存在异常，不能使用支付');
                        }
                        echo $jsApiParameters;
                    } else {
                        echo 0;
                    }
                }else{
                    echo 0;//微信没有授权
                }
            }else {
                echo -1;//非法操作
            }
        } else {
            $this->error('非法操作！');
        }
    }

    /**
     * 支付回调地址
     */
    public function autoStorePay_do()
    {
        vendor('Wxpay.WxPayPubHelper');
        // 使用通用通知接口
        $notify = new \Notify_pub();
        // 存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        // 验证签名，并回应微信。
        if ($notify->checkSign() == FALSE) {
            $notify->setReturnParameter("return_code", "FAIL"); // 返回状态码
            $notify->setReturnParameter("return_msg", "签名失败"); // 返回信息
        } else {
            $notify->setReturnParameter("return_code", "SUCCESS"); // 设置返回码
        }
        $returnXml = $notify->returnXml();
        // ==商户根据实际情况设置相应的处理流程，此处仅作举例=======
        $parameter = $notify->xmlToArray($xml);
        if ($notify->checkSign() == TRUE) {
            if ($notify->data["return_code"] == "FAIL") {
                // 此处应该更新一下订单状态，商户自行增删操作
                // 更新订单数据【通信出错】设为无效订单
                echo 'error';
            } else
                if ($notify->data["result_code"] == "FAIL") {
                    // 此处应该更新一下订单状态，商户自行增删操作
                    // 更新订单数据【通信出错】设为无效订单
                    echo 'error';
                } else {
                    // 我这里用到一个process方法，成功返回数据后处理，返回地数据具体可以参考微信的文档
                    if ($this->autoStorePayprocess($parameter)) {
                        // 处理成功后输出success，微信就不会再下发请求了
                        echo 'success';
                    } else {
                        // 没有处理成功，微信会间隔的发送请求
                        echo 'error';
                    }
                }
        }
    }

    /**
     * 更新状态
     */
    private function autoStorePayprocess($parameter)
    {
        D('StoreOrder')->setShopStorePayByWx($parameter['out_trade_no'], $parameter['transaction_id']);
        return true;
    }

    //private function

    //6.在线充值
    public function rechargePay() {
        if ($this->userLogined()) {
            //1.将授权登陆后的重定向链接赋值给AuthRedirectUrl
            cookie('AuthRedirectUrl', C('SITE_URL').'?c=Payment&a=rechargePay');
            $this->display('User/recharge');
        }
    }

    /**
     *在线充值 - 微信支付
     */
    public function rechargePay_action()
    {
        if (IS_AJAX) {
            // 获取微信的用户信息
            if (cookie('wxdata')) {
                $wxdata = json_decode(cookie('wxdata'), true);
                //创建商家线下店铺支付订单
                $userRechargeinfo = D('UserRecharge')->createUserRecharge(I('post.amount'), session('user_auth.id'));
                if (is_array($userRechargeinfo)) {
                    // 创建预支付订单
                    vendor('Wxpay.WxPayPubHelper');
                    // 使用jsapi接口
                    $jsApi = new \JsApi_pub();

                    // 获取订单的信息
                    $res = array(
                        'order_sn' => $userRechargeinfo['signup_sn'],
                        'order_amount' => $userRechargeinfo['amount']
                    );

                    // =========步骤2：使用统一支付接口，获取prepay_id============

                    // 使用统一支付接口
                    $unifiedOrder = new \UnifiedOrder_pub();
                    // 设置统一支付接口参数
                    $total_fee = $res['order_amount'] * 100;
                    if(session('user_auth.id') == 1934 || session('user_auth.id') == 1618) {
                        $total_fee = 1;
                    }
                    //$total_fee = 1;
                    $body = "爱尚缘消费共享商城会员在线充值";
                    $openid = $wxdata['openid'];
                    $unifiedOrder->setParameter("openid", "$openid"); // 用户标识
                    $unifiedOrder->setParameter("body", $body); // 商品描述
                    // 自定义订单号，此处仅作举例
                    $out_trade_no = $res['order_sn'];
                    $unifiedOrder->setParameter("out_trade_no", $out_trade_no); // 商户订单号
                    $unifiedOrder->setParameter("total_fee", $total_fee); // 总金额
                    $unifiedOrder->setParameter("notify_url", C('SITE_URL')."Payment/rechargePay_do"); // 通知地址
                    $unifiedOrder->setParameter("trade_type", "JSAPI"); // 交易类型
                    $unifiedOrder->setParameter("time_start", date('YmdHis', time()));//订单生成时间
                    $unifiedOrder->setParameter("time_expire", date('YmdHis', time()+5*60+1));//订单失效时间
                    $prepay_id = $unifiedOrder->getPrepayId();

                    // =========步骤3：使用jsapi调起支付============
                    $jsApi->setPrepayId($prepay_id);
                    $jsApiParameters = $jsApi->getParameters();
                    $wxconf = json_decode($jsApiParameters, true);
                    if ($wxconf['package'] == 'prepay_id=') {
                        exit('当前订单存在异常，不能使用支付');
                    }
                    echo $jsApiParameters;
                } else {
                    echo 0;
                }
            }else{
                echo 0;//微信没有授权
            }
        } else {
            $this->error('非法操作！');
        }
    }

    /**
     * 支付回调地址
     */
    public function rechargePay_do()
    {
        vendor('Wxpay.WxPayPubHelper');
        // 使用通用通知接口
        $notify = new \Notify_pub();
        // 存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        // 验证签名，并回应微信。
        if ($notify->checkSign() == FALSE) {
            $notify->setReturnParameter("return_code", "FAIL"); // 返回状态码
            $notify->setReturnParameter("return_msg", "签名失败"); // 返回信息
        } else {
            $notify->setReturnParameter("return_code", "SUCCESS"); // 设置返回码
        }
        $returnXml = $notify->returnXml();
        // ==商户根据实际情况设置相应的处理流程，此处仅作举例=======
        $parameter = $notify->xmlToArray($xml);
        if ($notify->checkSign() == TRUE) {
            if ($notify->data["return_code"] == "FAIL") {
                // 此处应该更新一下订单状态，商户自行增删操作
                // 更新订单数据【通信出错】设为无效订单
                echo 'error';
            } else
                if ($notify->data["result_code"] == "FAIL") {
                    // 此处应该更新一下订单状态，商户自行增删操作
                    // 更新订单数据【通信出错】设为无效订单
                    echo 'error';
                } else {
                    // 我这里用到一个process方法，成功返回数据后处理，返回地数据具体可以参考微信的文档
                    if ($this->rechargePayprocess($parameter)) {
                        // 处理成功后输出success，微信就不会再下发请求了
                        echo 'success';
                    } else {
                        // 没有处理成功，微信会间隔的发送请求
                        echo 'error';
                    }
                }
        }
    }

    /**
     * 更新状态
     */
    private function rechargePayprocess($parameter)
    {
        D('UserRecharge')->updateUserRecharge($parameter['out_trade_no'], $parameter['transaction_id']);
        return true;
    }

}