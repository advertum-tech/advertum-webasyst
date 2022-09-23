<?php

$_locale = wa()->getLocale();

return array(
    'enabled' => array(
        'title'        => /*_wp*/('Integration enabled'),
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 1,
    ),
    'cookie_expire' => array(
        'title'        => /*_wp*/('Cookie lifetime, days'),
        'control_type'=> waHtmlControl::INPUT,
        'value' => 30
    ),
    'cookie_main_domain' => array(
        'title'        => /*_wp*/('Set cookie to the root domain'),
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0,
    ),
    'advertum_admin_user_id' => array(
        'title'        => /*_wp*/('Advertum user id'),
        'control_type'=> waHtmlControl::INPUT,
    ),
    'track_visits' => array(
        'title'        => /*_wp*/('To track visits'),
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 1,
    ),
    'cashback_percentage' => array(
        'title'        => /*_wp*/('Cashback percentage'),
        'control_type' => waHtmlControl::INPUT,
    ),
);
