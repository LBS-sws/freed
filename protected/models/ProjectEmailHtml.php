<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/10/31 0031
 * Time: 9:44
 */
class ProjectEmailHtml
{
    protected function metaHtml(){
        $metaHtml = '<meta charset="utf-8">';
        $metaHtml.= '<meta name="viewport" content="width=device-width">';
        $metaHtml.= '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
        $metaHtml.= '<meta name="x-apple-disable-message-reformatting">';
        return $metaHtml;
    }
    protected function styleTwoHtml()
    {
        $metaHtml = '<style>';
        $metaHtml.= 'small {font-size: 65%;color: #777;}';
        $metaHtml.= '.email-container { min-width: 375px !important; padding:10px 0px 20px 0px;}';
        $metaHtml.= '.ml-10{ margin-left:10px;}';
        $metaHtml.= '.div-box{
	background:#fff;
	color:#333;
    border-radius: 8px;
    box-shadow: none;
    box-shadow: 0 14px 28px rgba(92, 116, 153, 0.15), 0 10px 10px rgba(92, 116, 153, 0.1);
	padding:20px;
}';
        $metaHtml.= '.div-media{ display:table;width:100%;}';
        $metaHtml.= '.div-media-left{ display:table-cell;width:50%;}';
        $metaHtml.= '.border-bottom{border-bottom:1px solid #ccc;margin-top:-10px;margin-bottom:7px;}';
        $metaHtml.= '.project_text{padding:10px 0px;}';
        $metaHtml.= '.project_text img{ max-width: 100%;height: auto !important;}';
        $metaHtml.= '</style>';
        return $metaHtml;
    }
    protected function styleHtml(){
        $metaHtml = '<style>';
        $metaHtml.= 'html { font-family: sans-serif;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;}';
        $metaHtml.= 'body { font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;font-size: 14px;line-height: 1.42857143;color: #333;background-color: #fff;}';
        $metaHtml.= 'html,body{height:100%;background:#e7eaf0;}';
        $metaHtml.= '.text-center{ text-align:center;}';
        $metaHtml.= '.text-right{ text-align:right;}';
        $metaHtml.= 'h3,h4 { font-family: inherit;font-weight: 500;line-height: 1.1;color: inherit;orphans: 3;widows: 3;page-break-after: avoid;}';
        $metaHtml.= 'h3 { margin-top: 20px;margin-bottom: 10px;font-size: 24px;}';
        $metaHtml.= 'h4 {margin-top: 10px;margin-bottom: 10px;font-size: 18px;}';
        $metaHtml.= '.btn {display: inline-block;margin-bottom: 0;font-weight: normal;text-align: center;
  white-space: nowrap;vertical-align: middle;-ms-touch-action: manipulation;touch-action: manipulation;
  cursor: pointer;background-image: none;border: 1px solid transparent;padding: 6px 12px;
  font-size: 14px;line-height: 1.42857143;border-radius: 4px;-webkit-user-select: none;-moz-user-select: none;
  -ms-user-select: none;user-select: none;text-decoration: none;
}';
        $metaHtml.= '.btn:focus{
  outline: 5px auto -webkit-focus-ring-color;
  outline-offset: -2px;
}';
        $metaHtml.= '.btn:hover{color: #333;text-decoration: none;}';
        $metaHtml.= '.btn-primary {color: #fff;background-color: #337ab7;border-color: #2e6da4;}';
        $metaHtml.= '.btn-primary:focus{color: #fff;background-color: #286090;border-color: #122b40;}';
        $metaHtml.= '.btn-primary:hover {color: #fff;background-color: #286090;border-color: #204d74;}';

        $metaHtml.= '</style>';
        return $metaHtml;
    }

    public static function projectEmailHtmlForNew($model){
        $emailHtml = "";
        $emailHtml.= '<!DOCTYPE html> <html lang="en"> <head>';
        $emailHtml.= self::metaHtml();
        $emailHtml.= '<title></title>';
        $emailHtml.= self::styleHtml();
        $emailHtml.= '</head>';
        $emailHtml.= '<body>';
        $emailHtml.= self::styleTwoHtml();

        $emailHtml.= '<div style="width: 100%; background: #e7eaf0; text-align: left;">';
        $emailHtml.= '<div class="email-container" style="max-width: 630px; margin: auto;">';

        $emailHtml.= '<h3 class="text-center">项目跟进系统</h3>';
        $emailHtml.= '<div class="div-box">';
        $emailHtml.= '<div style="color: #444; font-size: 15px; line-height: 25px;">';
        $emailHtml.= "<span>新项目</span>";
        $emailHtml.= '</div>';
        $emailHtml.= "<h4><b>{$model->project_name}</b></h4>";
        $emailHtml.= '<div class="div-media"><div class="div-media-left"><strong>项目类别：</strong>';
        $emailHtml.= "<span>".FunctionList::getProjectTypeStr($model->project_type)."</span>";
        $emailHtml.= '</div><div class="div-media-left"><strong>跟进人：</strong>';
        $emailHtml.= "<span>".FunctionSearch::getUserDisplayName($model->assign_user)."</span>";
        $emailHtml.= '</div></div>';//end media
        $emailHtml.= '<div class="div-media"><div class="div-media-left"><strong>建档时间：</strong>';
        $emailHtml.= "<span>".$model->lcd."</span>";
        $emailHtml.= '</div><div class="div-media-left"><strong>建档人：</strong>';
        $emailHtml.= "<span>".FunctionSearch::getUserDisplayName($model->lcu)."</span>";
        $emailHtml.= '</div></div>';//end div-media
        $emailHtml.= '<div class="border-bottom">&nbsp;</div>';
        $emailHtml.= '<div class="project_text">';
        $emailHtml.=$model->project_text;
        $emailHtml.= '</div>';//end project_text
        $emailHtml.= '<div class="text-center">';
        $url = "index.php/projectManage/view?index=".$model->id."&menu_code=";
        $url.= FunctionList::getMenuCodeForMin($model->menu_code);
        $url = Yii::app()->getBaseUrl(true)."/".$url;
        $emailHtml.= "<a class=\"btn btn-primary\" href=\"{$url}\" target='_blank'>查看项目</a>";
        $emailHtml.= '</div>';//end text-center

        $emailHtml.= '</div>';//end box

        $emailHtml.= '</div>';//end email-container
        $emailHtml.= '</div>';

        $emailHtml.= '</body>';
        $emailHtml.= '</html>';
        return $emailHtml;
    }

    public static function projectEmailHtmlForUpdate($model){
        $emailHtml = "";
        $emailHtml.= '<!DOCTYPE html> <html lang="en"> <head>';
        $emailHtml.= self::metaHtml();
        $emailHtml.= '<title></title>';
        $emailHtml.= self::styleHtml();
        $emailHtml.= '</head>';
        $emailHtml.= '<body>';
        $emailHtml.= self::styleTwoHtml();

        $emailHtml.= '<div style="width: 100%; background: #e7eaf0; text-align: left;">';
        $emailHtml.= '<div class="email-container" style="max-width: 630px; margin: auto;">';

        $emailHtml.= '<h3 class="text-center">项目跟进系统</h3>';
        $emailHtml.= '<div class="div-box">';
        $emailHtml.= '<div style="color: #444; font-size: 15px; line-height: 25px;">';
        $emailHtml.= "<span>修改项目</span>";
        $emailHtml.= '</div>';
        $emailHtml.= "<h4><b>{$model->project_name}</b></h4>";
        $emailHtml.= '<div class="div-media"><div class="div-media-left"><strong>项目类别：</strong>';
        $emailHtml.= "<span>".FunctionList::getProjectTypeStr($model->project_type)."</span>";
        $emailHtml.= '</div><div class="div-media-left"><strong>跟进人：</strong>';
        $emailHtml.= "<span>".FunctionSearch::getUserDisplayName($model->assign_user)."</span>";
        $emailHtml.= '</div></div>';//end media
        $emailHtml.= '<div class="div-media"><div class="div-media-left"><strong>建档时间：</strong>';
        $emailHtml.= "<span>".$model->lcd."</span>";
        $emailHtml.= '</div><div class="div-media-left"><strong>建档人：</strong>';
        $emailHtml.= "<span>".FunctionSearch::getUserDisplayName($model->lcu)."</span>";
        $emailHtml.= '</div></div>';//end div-media
        $emailHtml.= '<div class="div-media"><div class="div-media-left"><strong>项目进展：</strong>';
        $emailHtml.= "<span>".FunctionList::getProjectStatusStr($model->project_status)."</span>";
        $emailHtml.= '</div></div>';//end div-media
        $emailHtml.= '<div class="border-bottom">&nbsp;</div>';
        $emailHtml.= '<div class="project_text">';
        $emailHtml.=implode("<br/>",$model->getUpdateHistory());
        $emailHtml.= '</div>';//end project_text
        $emailHtml.= '<div class="text-center">';
        $url = "index.php/projectManage/view?index=".$model->id."&menu_code=";
        $url.= FunctionList::getMenuCodeForMin($model->menu_code);
        $url = Yii::app()->getBaseUrl(true)."/".$url;
        $emailHtml.= "<a class=\"btn btn-primary\" href=\"{$url}\" target='_blank'>查看项目</a>";
        $emailHtml.= '</div>';//end text-center

        $emailHtml.= '</div>';//end box

        $emailHtml.= '</div>';//end email-container
        $emailHtml.= '</div>';

        $emailHtml.= '</body>';
        $emailHtml.= '</html>';
        return $emailHtml;
    }

    public static function projectEmailHtmlForAssign($model,$oldRows){
        $emailHtml = "";
        $emailHtml.= '<!DOCTYPE html> <html lang="en"> <head>';
        $emailHtml.= self::metaHtml();
        $emailHtml.= '<title></title>';
        $emailHtml.= self::styleHtml();
        $emailHtml.= '</head>';
        $emailHtml.= '<body>';
        $emailHtml.= self::styleTwoHtml();

        $emailHtml.= '<div style="width: 100%; background: #e7eaf0; text-align: left;">';
        $emailHtml.= '<div class="email-container" style="max-width: 630px; margin: auto;">';

        $emailHtml.= '<h3 class="text-center">项目跟进系统</h3>';
        $emailHtml.= '<div class="div-box">';
        $emailHtml.= '<div style="color: #444; font-size: 15px; line-height: 25px;">';
        $emailHtml.= "<span>项目跟进</span>";
        $emailHtml.= '</div>';
        $emailHtml.= "<h4><b>";
        $emailHtml.= "《";
        $emailHtml.= FunctionList::getProjectTypeStr($model->project_type);
        $emailHtml.= "》";
        $emailHtml.= $model->project_name;
        $emailHtml.= "</b></h4>";
        $emailHtml.= '<div class="border-bottom">&nbsp;</div>';
        $emailHtml.= '<div class="div-media"><div class="div-media-left"><strong>跟进人：</strong>';
        $emailHtml.= "<span>".FunctionSearch::getUserDisplayName($model->username)."</span>";
        $emailHtml.= "<small class='ml-10'>".FunctionList::getAssignPlanStr($model->assign_plan)."</small>";
        $emailHtml.= '</div><div class="div-media-left text-right"><strong>跟进时间：</strong>';
        $emailHtml.= "<span>".$model->lcd."</span>";
        $emailHtml.= '</div></div>';//end div-media
        $emailHtml.= '<div class="project_text">';
        $emailHtml.=$model->assign_text;
        $emailHtml.= '</div>';//end project_text
        if(!empty($oldRows)){
            $emailHtml.= "<h4><b>该项目的先前跟进：</b></h4>";
            foreach ($oldRows as $row){
                $emailHtml.= '<div class="border-bottom">&nbsp;</div>';
                $emailHtml.= '<div class="div-media"><div class="div-media-left"><strong>跟进人：</strong>';
                $emailHtml.= "<span>".$row["disp_name"]."</span>";
                $emailHtml.= "<small class='ml-10'>".FunctionList::getAssignPlanStr($row["assign_plan"])."</small>";
                $emailHtml.= '</div><div class="div-media-left text-right"><strong>跟进时间：</strong>';
                $emailHtml.= "<span>".$row["lcd"]."</span>";
                $emailHtml.= '</div></div>';//end div-media
                $emailHtml.= '<div class="project_text">';
                $emailHtml.=$row["assign_text"];
                $emailHtml.= '</div>';//end project_text
            }
        }
        $emailHtml.= '<div class="text-center">';
        $url = "index.php/projectManage/view?index=".$model->project_id."&menu_code=";
        $url.= FunctionList::getMenuCodeForMin($model->menu_code);
        $url = Yii::app()->getBaseUrl(true)."/".$url;
        $emailHtml.= "<a class=\"btn btn-primary\" href=\"{$url}\" target='_blank'>查看项目</a>";
        $emailHtml.= '</div>';//end text-center

        $emailHtml.= '</div>';//end box

        $emailHtml.= '</div>';//end email-container
        $emailHtml.= '</div>';

        $emailHtml.= '</body>';
        $emailHtml.= '</html>';
        return $emailHtml;
    }
}