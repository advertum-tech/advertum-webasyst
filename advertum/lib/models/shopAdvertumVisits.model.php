<?php

class shopAdvertumVisitsModel extends waModel
{
    protected $table = 'shop_advertum_visits';

    const PAGE_SIZE = 50; // prod val
//    const PAGE_SIZE = 5; // test val

    public function add($uid, $oldUid)
    {
        $instance = $this->getByField('uid', $uid);
        // todo: should track visits once per uid + day
        if (!$instance) {
            $this->insert(array(
                'uid' => $uid,
                'old_uid' => $oldUid,
                'create_datetime' => date('Y-m-d H:i:s'),
                'contact_id' => wa()->getUser()->getId(),
            ));
        }
    }

}
