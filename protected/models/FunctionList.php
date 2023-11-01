<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/10/25 0025
 * Time: 12:39
 */
class FunctionList
{

    public static function getEmailTypeList(){
        $list = array(
            0=>Yii::t("freed","not all email"),//不接受任何邮件
            1=>Yii::t("freed","join email"),//接受参加的项目
            2=>Yii::t("freed","all email"),//全部的邮件
        );
        return $list;
    }

    public static function getEmailTypeStr($type=""){
        $list = self::getEmailTypeList();
        if(key_exists($type,$list)){
            return $list[$type];
        }else{
            return $type;
        }
    }

    public static function getProjectEmailTypeList(){
        $list = array(
            0=>Yii::t("freed","not all email"),//不接受任何邮件
            1=>Yii::t("freed","project all change"),//项目所有变动
            2=>Yii::t("freed","new and compute"),//新增及完成邮件
            3=>Yii::t("freed","only compute"),//仅完成邮件
        );
        return $list;
    }

    public static function getProjectEmailTypeStr($type=""){
        $list = self::getProjectEmailTypeList();
        if(key_exists($type,$list)){
            return $list[$type];
        }else{
            return $type;
        }
    }

    public static function getProjectTypeList($emptyBool=false)
    {
        $list=array();
        if($emptyBool){
            $list[""]=Yii::t("freed", "-- All --");
        }
        $list[1]=Yii::t("freed", "New function");//新功能
        $list[2]=Yii::t("freed", "Problem fixing");//问题修复
        $list[3]=Yii::t("freed", "Functional extension");//功能延伸
        $list[4]=Yii::t("freed", "Optimize");//优化
        $list[7]=Yii::t("freed", "Other");//其它
        return $list;
    }

    public static function getProjectTypeStr($type="",$emptyBool=false){
        $list = self::getProjectTypeList($emptyBool);
        if(key_exists($type,$list)){
            return $list[$type];
        }else{
            return $type;
        }
    }

    public static function getEQList()
    {
        $list=array(
            "<"=>"<",
            "<="=>"<=",
            "="=>"=",
            ">"=>">",
            ">="=>">=",
        );
        return $list;
    }

    public static function getEQStr($type=""){
        $list = self::getEQList();
        if(key_exists($type,$list)){
            return $list[$type];
        }else{
            return $list[">="];
        }
    }

    public static function getProjectStatusList($emptyBool=false){
        $list=array();
        if($emptyBool){
            $list[""]=Yii::t("freed", "-- All --");
        }
        $list[0]=Yii::t("freed","None");//未进展
        $list[1]=Yii::t("freed","In Progress");//进展中
        $list[9]=Yii::t("freed","Completed");//已完成
        return $list;
    }

    public static function getProjectStatusStr($type="",$emptyBool=false){
        $list = self::getProjectStatusList($emptyBool);
        if(key_exists($type,$list)){
            return $list[$type];
        }else{
            return $type;
        }
    }

    public static function getAssignPlanStr($assign_plan){
        if(!empty($assign_plan)){
            return $assign_plan."%";
        }else{
            return "";
        }
    }

    public static function getQuickList(){
        return array(
            "-1"=>"全部清空",//
            "40"=>"已测试，需要继续优化 (40%)",//
            "-2"=>"维持当前进度",//
            "80"=>"已更新到测试版，请测试 (80%)",//
            "85"=>"已测试,请更新到正式版 (85%)",//
            "90"=>"已更新到正式版，请检查 (90%)",//
            "100"=>"已完成 (100%)",//
        );
    }

    public static function getQuickOptionList(){
        return array(
            "-1"=>array('data-text'=>"-1"),//
            "-2"=>array('data-text'=>"-2"),//
            "40"=>array('data-text'=>"已测试，需要继续优化"),//
            "80"=>array('data-text'=>"已更新到测试版，请测试"),//
            "85"=>array('data-text'=>"已测试,请更新到正式版"),//
            "90"=>array('data-text'=>"已更新到正式版，请检查"),//
            "100"=>array('data-text'=>"已完成"),//
        );
    }

    public static function getStatusNumForPlan($plan_num){
        $plan_num = is_numeric($plan_num)?intval($plan_num):0;
        switch ($plan_num){
            case 0://无
                $statusNum = 0;
                break;
            case 100://已完成
                $statusNum = 9;
                break;
            default://进行中
                $statusNum = 1;
        }
        return $statusNum;
    }

    public static function getTableColor($row){
        //bg-yellow
        $trClass = "";
        if($row["project_status"]==9){//已完成
            $trClass.=" text-muted";
        }else{
            $uid = Yii::app()->user->id;
            if($row["current_user"]!=$uid){
                if($row["assign_user"]==$uid||$row["lcu"]==$uid){
                    $trClass.=" bg-yellow";
                }
            }
            $trClass.=" text-primary";
        }
        return $trClass;
    }

    public static function getMenuCodeForMin($menu_code){
        if(strlen($menu_code)>=2){
            return substr($menu_code,0,2);
        }
        return $menu_code;
    }


    public static function getSelectYear(){
        $arr = array();
        $year = date("Y");
        for($i=$year-3;$i<$year+3;$i++){
            if($i>=2022){
                $arr[$i] = $i.Yii::t("report","Year");
            }
        }
        return $arr;
    }

    public static function getSelectMonth(){
        $arr = array();
        for($i=1;$i<=12;$i++){
            $arr[$i] = $i.Yii::t("report","Month");
        }
        return $arr;
    }

    public static function getSelectType(){
        $arr = array();
        $arr[1]=Yii::t("freed","search quarter");//季度
        $arr[2]=Yii::t("freed","search month");//月度
        $arr[3]=Yii::t("freed","search day");//日期
        return $arr;
    }

    public static function getQuMonthList(){
        $arr = array(
            1=>Yii::t("freed","1 month - 3 month"),
            4=>Yii::t("freed","4 month - 6 month"),
            7=>Yii::t("freed","7 month - 9 month"),
            10=>Yii::t("freed","10 month - 12 month"),
        );
        return $arr;
    }

    public static function getQuMonthStr($key){
        $arr = self::getQuMonthList();
        if(key_exists($key,$arr)){
            return $arr[$key];
        }else{
            return $key;
        }
    }

}