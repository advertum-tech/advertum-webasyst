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
//        waLog::log('ADVERTUM.frontendHead.uid: ' . $uid);
        if ($uid) {
//            waLog::log('ADVERTUM.frontendHead.uid');
            $oldUid = null;
            if (waRequest::cookie('advertum_uid') != $uid) {
                $oldUid = waRequest::cookie('advertum_uid');
                $cookieLifetimeDays = $this->getSettings('cookie_expire');
                if (!$cookieLifetimeDays) {
                    $cookieLifetimeDays = 30;
                }
                wa()->getResponse()->setCookie('advertum_uid', $uid, time() + $cookieLifetimeDays * 86400);
            }
            if ($this->getSettings('track_visits')) {
                $visitsModel = new shopAdvertumVisitsModel();
                $visitsModel->add($uid, $oldUid);
//                waLog::log('ADVERTUM.frontendHead.track_visits');
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
//            waLog::log('ADVERTUM.frontendMyOrders.not_enabled');
            return;
        }
        $userId = wa()->getUser()->getId();
        if (!$userId) {
//            waLog::log('ADVERTUM.frontendMyOrders.no_current_user');
            return;
        }
        if (!$this->getSettings('advertum_admin_user_id')) {
            // to set advertum admin user id in the Advertum plugin settings
//            waLog::log('ADVERTUM.frontendMyOrders.no_advertum_admin_user_defined');
            return 'uid: ' . $userId;
        }
        if (intval($this->getSettings('advertum_admin_user_id')) !== intval($userId)) {
//            waLog::log('ADVERTUM.frontendMyOrders.advertum_admin_user_mismatch');
            return;
        }

        $status = waRequest::get('status', shopAdvertumOrdersModel::ORDER_STATUS_PAID);
        $uid = waRequest::get('uid');
        $order_id = waRequest::get('order_id');
        $orders_page = waRequest::get('orders_page', 1);
        $visits_page = waRequest::get('visits_page', 1);
        if ($orders_page < 1) {
            $orders_page = 1;
        }
        if ($visits_page < 1) {
            $visits_page = 1;
        }

        try {
            ///// ORDERS
            $ordersModel = new shopAdvertumOrdersModel();
            $limit = shopAdvertumOrdersModel::PAGE_SIZE;
            $where = '';
            if ($status) {
                $where = " WHERE status = '" . $status . "' ";
            }
            if ($uid) {
                $where .= $where ? " AND " : " WHERE ";
                $where .= " uid = '" . $uid . "' ";
            }
            if ($order_id) {
                $where .= $where ? " AND " : " WHERE ";
                $where .= " order_id = '" . $order_id . "' ";
            }
            $countSql = "SELECT COUNT(*) FROM shop_advertum_orders $where";
            $count = intval($ordersModel->query($countSql)->fetchField('COUNT(*)'));
            $orders_pages_count = ceil((float)$count / $limit);
            $offset = ($orders_page - 1) * $limit;
            $dataSql = "SELECT * FROM shop_advertum_orders $where ORDER BY id desc LIMIT $offset, $limit";
//            waLog::log('count__offset__limit__visits_pages_count__visits_page ' . $count . ' ' . $offset . ' ' . $limit . ' ' . $visits_pages_count . ' ' . $visits_page);
            $orders = $ordersModel->query($dataSql)->fetchAll();

            ///// VISITS
            $visitsModel = new shopAdvertumVisitsModel();
            $limit = shopAdvertumVisitsModel::PAGE_SIZE;
            $where = '';
            if ($uid) {
                $where = " WHERE uid = '" . $uid . "' ";
            }
            $countSql = "SELECT COUNT(*) FROM shop_advertum_visits $where";
            $count = intval($visitsModel->query($countSql)->fetchField('COUNT(*)'));
            $visits_pages_count = ceil((float)$count / $limit);
            $offset = ($visits_page - 1) * $limit;
            $dataSql = "SELECT * FROM shop_advertum_visits $where ORDER BY id desc LIMIT $offset, $limit";
    //        waLog::log('count__offset__limit__visits_pages_count__visits_page ' . $count . ' ' . $offset . ' ' . $limit . ' ' . $visits_pages_count . ' ' . $visits_page);
            $visits = $visitsModel->query($dataSql)->fetchAll();

            $view = wa()->getView();
            $view->assign('orders_pages_count', $orders_pages_count);
            $view->assign('orders', $orders);
            $view->assign('visits_pages_count', $visits_pages_count);
            $view->assign('visits', $visits);

            $view->assign('params', [
                'status' => $status,
                'uid' => $uid,
                'order_id' => $order_id,
                'orders_page' => $orders_page,
                'visits_page' => $visits_page,
            ]);
        } catch (waDbException $e) {
            waLog::log($e->getMessage());
            $view = wa()->getView();
            $view->assign('error', 'database error');
        }


        return $view->fetch($this->path . '/templates/frontendMyOrders.html');
    }

    public function createOrder($params)
    {
//        waLog::log('ADVERTUM.createOrder');
        if (!$this->getSettings('enabled') || wa()->getEnv() == 'backend') {
//            waLog::log('ADVERTUM.createOrder.not_enabled_or_backend');
            return;
        }
//        waLog::dump($params, 'error.log');
        $uid = waRequest::cookie('advertum_uid');
        // todo: or currently logged user has associated advertum_uid in the past so webasyst pays fee to advertum

        if ($uid) {
//            waLog::log('ADVERTUM.createOrder.cookie_exists');
            $order_id = $params['order_id'];
            $order_model = new shopOrderModel();
            $order = $order_model->getById($order_id);
//            waLog::dump($order, 'error.log');
            $leadModel = new shopAdvertumOrdersModel();
            $leadModel->createOrGet($uid, $order);
            $params_model = new shopOrderParamsModel();
            $params_model->set($order_id, ['advertum_uid' => $uid], false);
            // todo: to push advertum api 'purchase detected'
        } else {
//            waLog::log('ADVERTUM.createOrder.cookie_does_not_exist');
        }
    }

    /**
     * @param array $params
     */
    public function applyCashback($params)
    {
//        waLog::log('ADVERTUM.applyCashback');
        if (!$this->getSettings('enabled')) {
//            waLog::log('ADVERTUM.applyCashback.not_enabled');
            return;
        }
        $order_id = $params['order_id'];

        $order_model = new shopOrderModel();
        $order = $order_model->getById($order_id);
//        waLog::dump($order, 'error.log');

        $order_params_model = new shopOrderParamsModel();
        $order_params = $order_params_model->get($order_id);

        if ($order['paid_date'] && !empty($params['update']['paid_date']) && !empty($order_params['advertum_uid'])) {
//            waLog::log('ADVERTUM.applyCashback.paid_date_or_advertum_uid');
//            waLog::log('ADVERTUM.applyCashback.order_total_state_id: ' . $order['total'] . ' ' . $order['state_id']);
            $advertumOrdersModel = new shopAdvertumOrdersModel();
            $advertumOrder = $advertumOrdersModel->createOrGet($order_params['advertum_uid'], $order);
            if ($advertumOrder) {
//                waLog::log('ADVERTUM.applyCashback.$advertumOrder');
//                waLog::log('ADVERTUM.applyCashback.auid_oid: ' . $order_params['advertum_uid'] . ' ' . $order_id);
                $cashbackPercentage = $this->getSettings('cashback_percentage');
                if (!$cashbackPercentage) {
                    $cashbackPercentage = 5.0;
                }
                $cashback = round(floatval($order['total']) * (floatval($cashbackPercentage) / 100), 2);
//                waLog::log('ADVERTUM.applyCashback.$cashbackPercentage.$cashback: ' . $cashbackPercentage . ' ' . $cashback);

//                waLog::dump($order, 'error.log');
                $items_model = new shopOrderItemsModel();
                $items = $items_model->getItems($order_id);
                $itemsStr = '';
                foreach ($items as $item) {
                    $itemsStr .= strlen($itemsStr) ? '|' : '';
                    $name = mb_substr($item['name'],0,20);
                    $itemsStr .= $name . ',' . $item['sku_code'] . ',' . $item['price'] . ',' . $item['quantity'];
                }
//                waLog::dump($items, 'error.log');
//                waLog::log('ADVERTUM.applyCashback.$itemsStr: ' . $itemsStr);

                $advertumOrdersModel->updateByField([
                    'uid' => $order_params['advertum_uid'],
                    'order_id' => $order['id'],
                    'status' => shopAdvertumOrdersModel::ORDER_STATUS_CREATED,
                ], [
                    'status' => shopAdvertumOrdersModel::ORDER_STATUS_PAID,
                    'paid_price' => $order['total'],
                    'paid_datetime' => date('Y-m-d H:i:s'),
                    'cashback_percentage' => $cashbackPercentage,
                    'cashback' => $cashback,
                    'items' => $itemsStr,
                ]);
                // todo: to push the advertum api 'purchase completed'
            } else {
                waLog::log('ADVERTUM.applyCashback.NO_$advertumOrder');
            }
        } else {
            waLog::log('ADVERTUM.applyCashback.no_paid_date_or_no_advertum_uid');
        }
    }

    /**
     * @param array $params
     */
    public function cancelCashback($params)
    {
//        waLog::log('ADVERTUM.cancelCashback');
        if (!$this->getSettings('enabled')) {
//            waLog::log('ADVERTUM.cancelCashback.not_enabled');
            return;
        }
        $order_id = $params['order_id'];

        $order_params_model = new shopOrderParamsModel();
        $order_params = $order_params_model->get($order_id);

        if (!empty($order_params['advertum_uid'])) {
//            waLog::log('ADVERTUM.cancelCashback.advertum_uid');
            $order_model = new shopOrderModel();
            $order = $order_model->getById($order_id);
            $advertumOrdersModel = new shopAdvertumOrdersModel();
            $advertumOrder = $advertumOrdersModel->createOrGet($order_params['advertum_uid'], $order);
            if ($advertumOrder) {
//                waLog::log('ADVERTUM.cancelCashback.$advertumOrder');
                $advertumOrdersModel->updateByField([
                    'uid' => $order_params['advertum_uid'],
                    'order_id' => $order['id'],
                ], [
                    'status' => shopAdvertumOrdersModel::ORDER_STATUS_CANCELED,
                    'canceled_price' => $order['total'],
                    'canceled_datetime' => date('Y-m-d H:i:s'),
                ]);
                // TODO: to push the advertum api 'purchase canceled'
            } else {
//                waLog::log('ADVERTUM.cancelCashback.NO_$advertumOrder');
            }
        } else {
//            waLog::log('ADVERTUM.cancelCashback.no_advertum_uid');
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