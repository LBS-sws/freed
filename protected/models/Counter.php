<?php

class Counter {
    public static function countAuditMutual() {
        $arr = array();
        $uid = Yii::app()->user->id;
        $menuRows = Yii::app()->db->createCommand()->select("id,menu_code")
            ->from("fed_setting")->where("display=1")->queryAll();
        if($menuRows){
            $projectSql = "and ((a.lcu = '{$uid}' and a.current_user!='{$uid}')";
            $projectSql.= " or (a.assign_user = '{$uid}' and a.current_user!='{$uid}'))";
            foreach ($menuRows as $menu){
                $count = Yii::app()->db->createCommand()->select("count(a.id)")
                    ->from("fed_project a")
                    ->where("a.menu_id=:menu_id and a.assign_plan!=100 ".$projectSql,array(":menu_id"=>$menu["id"]))
                    ->queryScalar();
                $arr[]=array('code'=>$menu["menu_code"]."01",'count'=>$count,'color'=>"bg-yellow");
            }
        }
        return $arr;
    }

    public static function countSign() {
        $rtn = 0;

        $wf = new WorkflowPayment;
        $wf->connection = Yii::app()->db;
        $list = $wf->getPendingRequestIdList('PAYMENT', 'PS', Yii::app()->user->id);
        $items = empty($list) ? array() : explode(',',$list);
        $rtn = count($items);

        return $rtn;
    }
}

?>