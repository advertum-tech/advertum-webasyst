<?php

class shopAdvertumOrdersModel extends waModel
{
    const ORDER_STATUS_CREATED = 'created';
    const ORDER_STATUS_PAID = 'paid';
    const ORDER_STATUS_CANCELED = 'canceled';

    protected $table = 'shop_advertum_orders';
    protected $id = 'uid';

    public function createOrGet($uid, $orderId, $price = null)
    {
        $advertumOrder = $this->getByField([
            'uid' => $uid,
            'order_id' => $orderId,
        ]);
        if (!$advertumOrder) {
            $this->insert(array(
                'uid' => $uid,
                'order_id' => $orderId,
                'status' => self::ORDER_STATUS_CREATED,
                'create_price' => $price,
                'create_contact_id' => wa()->getUser()->getId(),
                'create_datetime' => date('Y-m-d H:i:s'),
            ));
            $advertumOrder = $this->getByField([
                'uid' => $uid,
                'order_id' => $orderId,
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