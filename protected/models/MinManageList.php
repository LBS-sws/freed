<?php

class MinManageList extends ProjectManageList
{
	public function getMinInfoHtmlTr($min_id){
        $suffix = Yii::app()->params['envSuffix'];
        $html="";
        $rows = Yii::app()->db->createCommand()->select("a.*,b.disp_name")
            ->from("fed_min_assign a")
            ->leftJoin("security{$suffix}.sec_user b","a.username=b.username")
            ->where('a.min_id=:id',array(':id'=>$min_id))->order("a.lcd desc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $html.= "<tr class='detail_{$min_id}'>";
                $html.= "<td colspan=2>";
                $html.= "<strong>跟进账号：</strong>";
                $html.=$row["disp_name"];
                $html.= "<br><strong>跟进进度：</strong>";
                $html.=FunctionList::getAssignPlanStr($row["assign_plan"]);
                $html.="</td>";
                $html.= "<td colspan=2><strong>跟进时间：</strong>";
                $html.=$row["lcd"];
                $html.="</td>";
                $html.= "<td colspan=6><strong>跟进内容：</strong>";
                $html.=$row["assign_text"];
                $html.="</td>";
                $html.= "</tr>";
            }
        }
        return $html;
    }

    public static function printMinHtmlForTable($project_id,$menu_id,$ready=false){
        $suffix = Yii::app()->params['envSuffix'];
        $html="<table class='table table-bordered table-hover' style='margin: 0px;'>";
        $html.="<thead><tr>";
        $html.="<th width='1%'></th>";
        $html.="<th>".Yii::t("freed","min project code")."</th>";
        $html.="<th>".Yii::t("freed","min project type")."</th>";
        $html.="<th>".Yii::t("freed","min project name")."</th>";
        $html.="<th>".Yii::t("freed","File builder")."</th>";
        $html.="<th>".Yii::t("freed","File date")."</th>";
        $html.="<th>".Yii::t("freed","plan date")."</th>";
        $html.="<th>".Yii::t("freed","assign user")."</th>";
        $html.="<th>".Yii::t("freed","min assign plan")."</th>";
        $html.="<th width='1%'>";
        if($ready){//只读
            $html.="&nbsp;";
        }else{
            $link = Yii::app()->createUrl('minManage/add',array("index"=>$menu_id,"project_id"=>$project_id));
            $html.=TbHtml::link(Yii::t("freed","add min project"),$link,array('class'=>'add_min btn btn-default','target'=>'_blank'));
        }
        $html.="</th>";
        $html.="</tr></thead><tbody>";
        $rows = Yii::app()->db->createCommand()->select("a.*,b.disp_name")
            ->from("fed_min a")
            ->leftJoin("security{$suffix}.sec_user b","a.lcu=b.username")
            ->where('a.project_id=:id',array(':id'=>$project_id))->queryAll();

        if ($rows){
            foreach ($rows as $row){
                $link = Yii::app()->createUrl('minManage/view',array("index"=>$row["id"]));

                $detailCount = Yii::app()->db->createCommand()->select("count(id)")
                    ->from("fed_min_assign")
                    ->where('min_id=:id',array(':id'=>$row['id']))->queryScalar();
                $html.="<tr>";
                $html.="<td>".TbHtml::link("<span class='glyphicon glyphicon-eye-open'></span>",$link)."</td>";
                $html.="<td>".$row["project_code"]."</td>";
                $html.="<td>".FunctionList::getProjectTypeStr($row["project_type"])."</td>";
                $html.="<td>".$row["project_name"]."</td>";
                $html.="<td>".$row["disp_name"]."</td>";
                $html.="<td>".CGeneral::toDate($row["lcd"])."</td>";
                $html.="<td>".CGeneral::toDate($row["plan_date"])."</td>";
                $html.="<td>".$row["assign_str_user"]."</td>";
                $html.="<td>".FunctionList::getAssignPlanStr($row['assign_plan'])."</td>";
                if($detailCount>0){
                    $html.="<td class='show-tr'><span data-id='{$row['id']}' class='fa fa-plus-square'></span></td>";
                }else{
                    $html.="<td><span class='fa fa-square'></span></td>";
                }
                $html.="</tr>";
            }
        }else{
            $html.="<tr><td colspan='9'>没有小项目</td></tr>";
        }

        $html.="</tbody></table>";

        return $html;
    }
}
