<?php

return array(
    'name'          => 'Advertum',
    'description'   => 'Integration with Advertum platform',
    'img'           => 'img/favicon-16x16.png',
    'version'       => '1.0',
    'vendor'        => 'Advertum',
    'handlers'      => array(
        'frontend_head'              => 'frontendHead',
        'frontend_my_orders'         => 'frontendMyOrders',
        'order_action.create'        => 'createOrder',

        'order_action.complete'      => 'applyCashback',
        'order_action.pay'           => 'applyCashback',
        'order_action.restore'       => 'applyCashback',

        'order_action.delete'        => 'cancelCashback',
        'order_action.refund'        => 'cancelCashback',
    ),
);
