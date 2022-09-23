<?php

class shopAdvertumPlugin extends shopPlugin
{
    public function frontendHead()
    {
//        waLog::log('ADVERTUM.frontendHead');
        if (!$this->getSettings('enabled')) {
            return;
        }

        $uid = waRequest::get('advertum_uid');
        // todo: log
        if ($uid) {
            if (waRequest::cookie('advertum_uid') != $uid) {
                $cookieLifetimeDays = $this->getSettings('cookie_expire') ?? 30;
                wa()->getResponse()->setCookie('advertum_uid', $uid, time() + $cookieLifetimeDays * 86400);
            }
            if ($this->getSettings('track_visits')) {
                $visitsModel = new shopAdvertumVisitsModel();
                $visitsModel->add($uid);
            }
            $url = wa()->getConfig()->getRequestUrl(false, true);
            $get_params = waRequest::get();
            unset($get_params['advertum_uid']);
            $url_params = http_build_query($get_params);
            wa()->getResponse()->redirect($url.($url_params ? '?'.$url_params : ''), 301);
        }
    }

    public function frontendMyOrders()
    {
        if (!$this->getSettings('enabled')) {
            return;
        }
        $userId = wa()->getUser()->getId();
        if (!$userId) {
            return;
        }
        if (!$this->getSettings('advertum_admin_user_id')) {
            // to set advertum admin user id in the Advertum plugin settings
            return 'uid: ' . $userId;
        }
        if (intval($this->getSettings('advertum_admin_user_id')) !== intval($userId)) {
            return;
        }

        waLog::log('ADVERTUM.frontendMyOrders.5');

        $view = wa()->getView();
        $view->assign('test_var', 'test_val');
        return $view->fetch($this->path . '/templates/frontendMyOrders.html');

    }

    public function createOrder($params)
    {
        if (!$this->getSettings('enabled') || wa()->getEnv() == 'backend') {
            return;
        }
        $uid = waRequest::cookie('advertum_uid');
        // todo: or currently logged user has associated advertum_uid in the past so webasyst pays fee to advertum
        if ($uid) {
            $leadModel = new shopAdvertumOrdersModel();
            $order_id = $params['order_id'];
            $leadModel->createOrGet($uid, $order_id, $params['price']);
            $params_model = new shopOrderParamsModel();
            $params_model->set($order_id, ['advertum_uid' => $uid], false);
            // todo: to push advertum api 'purchase detected'
        }
    }

    /**
     * @param array $params
     */
    public function applyCashback($params)
    {
        if (!$this->getSettings('enabled')) {
            return;
        }
        $order_id = $params['order_id'];

        $order_model = new shopOrderModel();
        $order = $order_model->getById($order_id);

        $order_params_model = new shopOrderParamsModel();
        $order_params = $order_params_model->get($order_id);

        if ($order['paid_date'] && !empty($params['update']['paid_date']) && !empty($order_params['advertum_uid'])) {
            $advertumOrdersModel = new shopAdvertumOrdersModel();
            $advertumOrder = $advertumOrdersModel->createOrGet($order_params['advertum_uid'], $order_id);
            if ($advertumOrder) {
                $advertumOrdersModel->updateByField([
                    'uid' => $order_params['advertum_uid'],
                    'order_id' => $order_id
                ], '', [
                    'paid_price' => $params['price'],
                    'paid_datetime' => date('Y-m-d H:i:s'),
                    'paid_contact_id' => wa()->getUser()->getId(),
                    'cashback_percentage' => 4.45,  // todo:
                    'cashback' => 123.456,          // todo:
                ]);
                // todo: to push the advertum api 'purchase completed'
            }
        }
    }

    /**
     * @param array $params
     */
    public function cancelCashback($params)
    {
        if (!$this->getSettings('enabled')) {
            return;
        }
        $order_id = $params['order_id'];

        $order_params_model = new shopOrderParamsModel();
        $order_params = $order_params_model->get($order_id);

        if (!empty($order_params['advertum_uid'])) {
            $advertumOrdersModel = new shopAdvertumOrdersModel();
            $advertumOrder = $advertumOrdersModel->createOrGet($order_params['advertum_uid'], $order_id);
            if ($advertumOrder) {
                $advertumOrdersModel->updateByField([
                    'uid' => $order_params['advertum_uid'],
                    'order_id' => $order_id,
                ], '', [
                    'canceled_price' => $params['price'],
                    'canceled_datetime' => date('Y-m-d H:i:s'),
                    'canceled_contact_id' => wa()->getUser()->getId(),
                    'cashback_percentage' => 1.23, // todo:
                    'cashback' => 888.999,         // todo:
                ]);
                // TODO: to push the advertum api 'purchase canceled'
            }
        }
    }
}

// https://developers.webasyst.ru/docs/cookbook/basics/routing/
// https://www.shop-script.com/help/99/shop-script-5-plugin-development/
// https://tjo.biz/sozdanie-plaginov-dlya-shop-script.html
//    public function frontendMyNav()
//    {
//        waLog::log('ADVERTUM.frontendMyNav');
////        $view_helper = new waViewHelper(wa()->getView());
////        return '<a href="'.$view_helper->myUrl().'advertum/">'._wp('Advertum').'</a>';
//        return '<a href="advertum/">'._wp('Advertum').'</a>';
//    }