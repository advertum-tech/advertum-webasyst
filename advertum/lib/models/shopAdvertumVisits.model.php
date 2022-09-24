<?php

class shopAdvertumVisitsModel extends waModel
{
    protected $table = 'shop_advertum_visits';

//    const PAGE_SIZE = 50; // prod val
    const PAGE_SIZE = 5; // test val

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

//    public function getStat($contact_id, $group_by, $start_date)
//    {
//        if ($group_by == 'date') {
//            $group_by = 'DATE(create_datetime)';
//        } elseif (!$this->fieldExists($group_by)) {
//            return array();
//        }
//        $sql = "SELECT ".$group_by." f, count(*) FROM ".$this->table."
//                WHERE contact_id = i:0 AND create_datetime >= s:1
//                GROUP BY ".$group_by;
//        return $this->query($sql, $contact_id, $start_date)->fetchAll('f', true);
//    }
//
//    public function getAllStat($group_by, $start_date)
//    {
//        if ($group_by == 'date') {
//            $group_by = 'DATE(create_datetime)';
//        } elseif (!$this->fieldExists($group_by)) {
//            return array();
//        }
//        $sql = "SELECT ".$group_by." f, count(*) FROM ".$this->table."
//                WHERE create_datetime >= s:0
//                GROUP BY ".$group_by;
//        return $this->query($sql, $start_date)->fetchAll('f', true);
//    }
}
