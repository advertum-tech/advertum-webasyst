<?php

class shopAdvertumOrdersModel extends waModel
{
    const ORDER_STATUS_CREATED = 'created';
    const ORDER_STATUS_PAID = 'paid';
    const ORDER_STATUS_CANCELED = 'canceled';

    //    const PAGE_SIZE = 50; // prod val
    const PAGE_SIZE = 5; // test val

    protected $table = 'shop_advertum_orders';

    public function createOrGet($uid, $order)
    {
        $advertumOrder = $this->getByField([
            'uid' => $uid,
            'order_id' => $order['id'],
        ]);
        if (!$advertumOrder) {
            $this->insert(array(
                'uid' => $uid,
                'order_id' => $order['id'],
                'status' => self::ORDER_STATUS_CREATED,
                'create_price' => $order['total'],
                'create_datetime' => date('Y-m-d H:i:s'),
                'currency' => $order['currency'],
                'shipping' => $order['shipping'],
                'discount' => $order['discount'],
                'contact_id' => wa()->getUser()->getId(),
            ));
            $advertumOrder = $this->getByField([
                'uid' => $uid,
                'order_id' => $order['id'],
            ]);
        }
        return $advertumOrder;
    }

//    public function add($contact_id)
//    {
//        do {
//            $code = rand(100, 1000000);
//        } while ($this->getById($code));
//
//        $this->insert(array(
//            'code' => $code,
//            'contact_id' => $contact_id,
//            'create_datetime' => date('Y-m-d H:i:s')
//        ));
//        return $code;
//    }
}