<?php
return array(
    'shop_advertum_visits' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'uid' => array('varchar', 11, 'null' => 0),
        'create_datetime' => array('datetime', 'null' => 0),
        'contact_id' => array('int', 11, 'null' => 1),
        ':keys' => array(
            'PRIMARY' => 'id',
            'uid_create_datetime' => 'uid',
        ),
    ),
    'shop_advertum_orders' => array(
        'uid' => array('varchar', 11, 'null' => 0),
        'order_id' => array('int', 11, 'unsigned' => 1),
        'status' => array('varchar', 11, 'null' => 0),

        'create_price' => array('decimal', "15,4", 'null' => 1),
        'create_contact_id' => array('int', 11, 'null' => 1),
        'create_datetime' => array('datetime', 'null' => 0),

        'paid_price' => array('decimal', "15,4", 'null' => 1),
        'paid_datetime' => array('datetime', 'null' => 1),
        'paid_contact_id' => array('int', 11, 'null' => 1),

        'cashback_percentage' => array('decimal', "4,2", 'null' => 1),
        'cashback' => array('decimal', "15,4", 'null' => 1),

        'canceled_price' => array('decimal', "15,4", 'null' => 1),
        'canceled_datetime' => array('datetime', 'null' => 1),
        'canceled_contact_id' => array('int', 11, 'null' => 1),

        ':keys' => array(
            'PRIMARY' => 'uid',
            'uid_order_id' => array('uid', 'order_id'),
        ),
    ),
);