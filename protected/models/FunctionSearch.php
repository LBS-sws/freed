<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/10/25 0025
 * Time: 12:39
 */
class FunctionSearch{
    //获取所有参加允许加入跟进系统的员工
    public static function getMenuUserList(){
        $suffix = Yii::app()->params['envSuffix'];
        $systemId = Yii::app()->params['systemId'];
        $list=array(""=>"");
        $rows = Yii::app()->db->createCommand()->select("b.username,b.disp_name")
            ->from("security{$suffix}.sec_user_access a")
            ->leftJoin("security{$suffix}.sec_user b","a.username=b.username")
            ->where("a.system_id='{$systemId}' and a.a_control like '%CN01%'")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["username"]] = $row["disp_name"];
            }
        }
        return $list;
    }

    //获取含有菜单id的账号
    public static function getAssignUserList($menu_id,$empty=true){
        $suffix = Yii::app()->params['envSuffix'];
        $systemId = Yii::app()->params['systemId'];
        if($empty){
            $list=array(""=>"");
        }else{
            $list=array();
        }
        $menu_code = self::getMenuCodeForID($menu_id);
        $rows = Yii::app()->db->createCommand()->select("b.username,b.disp_name")
            ->from("security{$suffix}.sec_user_access a")
            ->leftJoin("security{$suffix}.sec_user b","a.username=b.username")
            ->where("a.system_id='{$systemId}' and a.a_read_write like '%{$menu_code}01%'")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["username"]] = $row["disp_name"];
            }
        }
        return $list;
    }

    //获取含有菜单id的账号及菜单邮箱类型
    public static function getMenuUserEmailList($menu_id){
        $list=array();
        $rows = self::getAssignUserList($menu_id,false);
        if($rows){
            foreach ($rows as $username=>$disName){
                $row=array();
                $row["username"] = $username;
                $row["disp_name"] = $disName;
                $row["email_type"] = self::getEmailTypeForUM($username,$menu_id);
                if($row["email_type"]==2){
                    $row["email_type"]=2;//初始及完成邮件
                }else{
                    $row["email_type"]=0;
                }
                $list[] = $row;
            }
        }
        return $list;
    }

    //获取含有菜单id的账号及菜单邮箱类型
    public static function getEmailTypeForUM($username,$menu_id){
        $emailType=0;//
        $row = Yii::app()->db->createCommand()->select("email_type")->from("fed_setting_info")
            ->where("set_id=:id and username=:username",array(":id"=>$menu_id,":username"=>$username))
            ->queryRow();
        if($row){
            $emailType=$row["email_type"];//
        }
        return $emailType;
    }

    //获取菜单编号
    public static function getMenuCodeForID($menu_id){
        $menu_code="none";//
        $row = Yii::app()->db->createCommand()->select("menu_code")->from("fed_setting")
            ->where("id=:id",array(":id"=>$menu_id))->queryRow();
        if($row){
            $menu_code=$row["menu_code"];
        }
        return $menu_code;
    }

    //获取账号昵称
    public static function getUserDisplayName($username){
        $suffix = Yii::app()->params['envSuffix'];
        $disp_name=$username;
        $row = Yii::app()->db->createCommand()->select("disp_name")
            ->from("security{$suffix}.sec_user")
            ->where("username=:username",array(":username"=>$username))->queryRow();
        if($row){
            $disp_name=$row["disp_name"];
        }
        return $disp_name;
    }

    //获取账号昵称
    public static function getUserDisplayNameForArr($arr){
        $nameStr = "";
        if(is_array($arr)){
            foreach ($arr as $username){
                $nameStr.= empty($nameStr)?"":",";
                $nameStr.=self::getUserDisplayName($username);
            }
        }else{
            $nameStr.=self::getUserDisplayName($arr);
        }
        return $nameStr;
    }
}