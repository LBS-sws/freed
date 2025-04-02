<?php

class ProjectManageModel extends CFormModel
{
    public $id;
    public $menu_id;
    public $menu_name;
    public $menu_code;

    public $project_code;
    public $project_name;
    public $project_type;
    public $project_text;
    public $project_status;
    public $assign_plan;
    public $current_user;
    public $assign_user=array();
    public $assign_str_user;
    public $start_date;
    public $end_date;
    public $plan_date;
    public $plan_start_date;
    public $lcu;
    public $luu;
    public $lcd;
    public $lud;
    public $urgency;
    public $old_status_type;//0：草稿  1：发布
    public $status_type=0;//0：草稿  1：发布

    public $emailList=array();
    protected $updateHistory=array();
    protected $preRow=array();

    protected $code_pre="01";

    public $no_of_attm = array(
        'prom'=>0,
        'proinfo'=>0
    );
    public $docType = 'PROM';
    public $docMasterId = array(
        'prom'=>0,
        'proinfo'=>0
    );
    public $files;
    public $removeFileId = array(
        'prom'=>0,
        'proinfo'=>0
    );
    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'id'=>Yii::t('freed','ID'),
            'project_code'=>Yii::t('freed','project code'),
            'project_name'=>Yii::t('freed','project name'),
            'project_type'=>Yii::t('freed','project type'),
            'project_text'=>Yii::t('freed','project description'),
            'project_status'=>Yii::t('freed','project status'),
            'assign_plan'=>Yii::t('freed','assign plan'),
            'status_type'=>Yii::t('freed','status type'),
            'urgency'=>Yii::t('freed','urgency'),
            'lcu'=>Yii::t('freed','File builder'),
            'lcd'=>Yii::t('freed','File date'),
            'assign_user'=>Yii::t('freed','assign user'),
            'emailList'=>Yii::t('freed','email hint'),
            'plan_date'=>Yii::t('freed','plan date'),
            'plan_start_date'=>Yii::t('freed','plan start date'),
        );
    }

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('id,menu_id,status_type,plan_date,plan_start_date,project_code,project_name,project_type,project_text,emailList,
            project_status,assign_plan,urgency,current_user,assign_user,start_date,end_date,lcu,luu,lcd,lud','safe'),
            array('menu_id,project_name,project_type,project_text,assign_user','required'),
            array('menu_id','validateMenuID'),
            array('id','validateID','on'=>array("delete","edit")),
            array('id','validateDelete','on'=>array("delete")),
            array('project_name','validateName','on'=>array("edit","new")),
            array('emailList','validateEmailList','on'=>array("edit","new")),

            array('files, removeFileId, docMasterId','safe'),
        );
    }

    public function validateID($attribute, $params){
        $row = Yii::app()->db->createCommand()->select("*")->from("fed_project")
            ->where('id=:id',array(':id'=>$this->id))->queryRow();
        if($row){
            $this->old_status_type = $row["status_type"];
            $this->start_date = $row["start_date"];
            $this->assign_plan = $row["assign_plan"];
            $this->current_user = $row["current_user"];
        }else{
            $message = "项目不存在，请重试";
            $this->addError($attribute,$message);
        }
    }

    public function validateDelete($attribute, $params){
        $row = Yii::app()->db->createCommand()->select("id")->from("fed_project_assign")
            ->where('project_id=:id',array(':id'=>$this->id))->queryRow();
        if($row){
            $message = "该项目已有跟进内容，无法删除";
            $this->addError($attribute,$message);
        }
        $row = Yii::app()->db->createCommand()->select("id")->from("fed_min")
            ->where('project_id=:id',array(':id'=>$this->id))->queryRow();
        if($row){
            $message = "该项目包含小项目，请先删除小项目";
            $this->addError($attribute,$message);
        }
    }

    public function validateMenuID($attribute, $params){
        $row = Yii::app()->db->createCommand()
            ->select("menu_code,menu_name")
            ->from("fed_setting")
            ->where('id=:id',array(':id'=>$this->menu_id))->queryRow();
        if(!$row){
            $message = "数据异常请刷新重试";
            $this->addError($attribute,$message);
        }else{
            $this->menu_name = $row["menu_name"];
            $this->menu_code = $row["menu_code"].$this->code_pre;
        }
    }

    public function validateName($attribute, $params){
        $id = -1;
        if(!empty($this->id)){
            $id = $this->id;
        }
        $row = Yii::app()->db->createCommand()->select("project_code")->from("fed_project")
            ->where('project_name=:name and id!=:id and menu_id=:menu_id',
                array(':name'=>$this->project_name,':id'=>$id,':menu_id'=>$this->menu_id))->queryRow();
        if($row){
            $message = "已存在相同的项目标题（{$row["project_code"]}），请重新命名";
            $this->addError($attribute,$message);
        }
    }

    public function validateEmailList($attribute, $params){
        $list = array();
        $rows = FunctionSearch::getMenuUserEmailList($this->menu_id);
        $emailList = key_exists("emailList",$_POST)?$_POST["emailList"]:array();
        foreach ($rows as $row){
            $username = $row["username"];
            if(key_exists($username,$emailList)&&$emailList[$username]["uflag"]=="Y"){
                $list[$username]=array(
                    "username"=>$username,
                    "emailType"=>$emailList[$username]["emailType"],
                    "uflag"=>$emailList[$username]["uflag"],
                    "id"=>$emailList[$username]["id"],
                );
            }
            if(Yii::app()->user->id==$username){
                $list[$username]=array(
                    "id"=>0,
                    "username"=>$username,
                    "emailType"=>1,
                    "uflag"=>"Y",
                );
            }
        }
        $this->emailList = $list;
    }

    public function retrieveMenuData($menu_id){ //新增
        $menu = Yii::app()->db->createCommand()->select("*")
            ->from("fed_setting")
            ->where("id =:id",array(":id"=>$menu_id))->queryRow();
        if($menu){
            $this->menu_id = $menu_id;
            $this->menu_name = $menu["menu_name"];
            $this->menu_code = $menu["menu_code"].$this->code_pre;
            return true;
        }
        return false;
    }

    public function retrieveData($index){ //修改
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("a.*,b.menu_name,b.menu_code,docman$suffix.countdoc('PROM',a.id) as promdoc ")
            ->from("fed_project a")
            ->leftJoin("fed_setting b","a.menu_id=b.id")
            ->where("a.id =:id",array(":id"=>$index))->queryRow();
        if($row){
            $this->id = $index;
            $this->menu_id = $row["menu_id"];
            $this->menu_name = $row["menu_name"];
            $this->menu_code = $row["menu_code"].$this->code_pre;

            $this->status_type = $row["status_type"];
            $this->project_code = $row["project_code"];
            $this->project_name = $row["project_name"];
            $this->project_type = $row["project_type"];
            $this->project_text = $row["project_text"];
            $this->project_status = $row["project_status"];
            $this->assign_plan = $row["assign_plan"];
            $this->urgency = $row["urgency"];
            $this->current_user = $row["current_user"];
            $this->assign_user = explode(",",$row["assign_user"]);
            $this->assign_str_user = $row["assign_str_user"];
            $this->start_date = $row["start_date"];
            $this->end_date = $row["end_date"];
            $this->plan_date = empty($row["plan_date"])?null:CGeneral::toDate($row["plan_date"]);
            $this->plan_start_date = empty($row["plan_start_date"])?null:CGeneral::toDate($row["plan_start_date"]);
            $this->lcu = $row["lcu"];
            $this->luu = $row["luu"];
            $this->lcd = $row["lcd"];
            $this->lud = $row["lud"];
            $this->no_of_attm["prom"] = $row['promdoc'];
            return true;
        }
        return false;
    }

    public static function getProjectHistoryRows($project_id){
        $rows = Yii::app()->db->createCommand()->select("*")->from("fed_project_history")
            ->where("project_id =:id",array(":id"=>$project_id))->order("id desc")->queryAll();
        return $rows;
    }

    public function getProjectEmailInfo(){
        $rows = Yii::app()->db->createCommand()->select("id,username,email_type")
            ->from("fed_project_email")
            ->where("project_id =:id",array(":id"=>$this->id))->queryAll();
        $this->emailList=array();
        if($rows){
            foreach ($rows as $row){
                $this->emailList[$row["username"]]["id"]=$row["id"];
                $this->emailList[$row["username"]]["emailType"]=$row["email_type"];
            }
        }
    }

    public static function emailTable($menu_id,$emailList=array()){
        $rows = FunctionSearch::getMenuUserEmailList($menu_id);
        $projectEmailList = FunctionList::getProjectEmailTypeList();
        $html = "<table class='table table-bordered table-striped' id='emailTable'>";
        $html.= "<thead>";
        $html.= "<tr>";
        $html.= "<th width='50%'>".Yii::t("freed","username")."</th>";
        $html.= "<th width='50%'>".Yii::t("freed","email on-off")."</th>";
        $html.= "</tr>";
        $html.= "</thead>";
        $html.="<tbody>";
        foreach ($rows as $row){
            $username = $row["username"];
            $disName = $row["disp_name"];
            $value = $row["email_type"];
            $id=0;
            $htmlOptions=array('class'=>'changeEmailType');
            if(key_exists($username,$emailList)){
                $id=$emailList[$username]["id"];
                $value=$emailList[$username]["emailType"];
            }
            if(Yii::app()->user->id==$username){
                $value=1;
                $htmlOptions["readonly"]=true;
            }
            $html.="<tr data-user='{$username}'>";
            $html.="<td>";
            $html.=TbHtml::hiddenField("emailList[{$username}][uflag]","N");
            $html.=TbHtml::hiddenField("emailList[{$username}][id]",$id);
            $html.=TbHtml::hiddenField("emailList[{$username}][username]",$username);
            $html.=TbHtml::textField("",$disName,array("disabled"=>true));
            $html.="</td>";
            $html.="<td>".TbHtml::dropDownList("emailList[{$username}][emailType]",$value,$projectEmailList,$htmlOptions)."</td>";
            $html.="</tr>";
        }
        $html.="</tbody>";
        $html.= "</table>";
        return $html;
    }

    public static function getProjectRow($project_id){
        $row = Yii::app()->db->createCommand()->select("*")->from("fed_project")
            ->where("id =:id",array(":id"=>$project_id))->queryRow();
        return $row;
    }

    public function getUpdateHistory(){
        return $this->updateHistory;
    }

    private function updateStrList(){
        return array("status_type","project_type","project_name","assign_user","plan_date","plan_start_date","urgency");
    }

    private function updateStrValue($itemStr,$value){
        switch ($itemStr){
            case "status_type":
                return empty($value)?Yii::t("freed","Draft"):Yii::t("freed","Publish");
            case "project_type":
                return FunctionList::getProjectTypeStr($value);
            case "assign_user":
                return FunctionSearch::getUserDisplayNameForArr($value);
            case "urgency":
                return FunctionList::getUrgencyStr($value);
            default:
                return $value;
        }
    }

    //多个员工跟进
    private function saveAssignUser(){
        if(!empty($this->assign_user)){
            $uid = Yii::app()->user->id;
            $userSql = implode("','",$this->assign_user);
            //删除多余的跟进人员
            Yii::app()->db->createCommand()->delete('fed_project_user', "project_id=:id and username not in('{$userSql}')", array(':id'=>$this->id));

            //添加新的跟进人员
            $rows = Yii::app()->db->createCommand()->select("id,username")->from("fed_project_user")
                ->where("project_id =:id",array(":id"=>$this->id))->queryAll();
            $rows = $rows?array_column($rows,"username"):array();
            foreach ($this->assign_user as $user){
                if(!in_array($user,$rows)){
                    Yii::app()->db->createCommand()->insert("fed_project_user", array(
                        'project_id'=>$this->id,
                        'username'=>$user,
                        'lcu'=>$uid,
                    ));
                }
            }
        }
    }

    private function saveHistory(){
        $uid = Yii::app()->user->id;
        switch ($this->getScenario()){
            case "new":
                Yii::app()->db->createCommand()->insert("fed_project_history", array(
                    'project_id'=>$this->id,
                    'update_type'=>2,
                    'update_html'=>"新增(".$this->updateStrValue("status_type",$this->status_type).")",
                    'lcu'=>$uid,
                ));
                break;
            case "edit":
                $preRow = $this->preRow;
                $nowRow = self::getProjectRow($this->id);
                foreach ($this->updateStrList() as $item){
                    if(key_exists($item,$preRow)&&$nowRow[$item]!==$preRow[$item]){
                        $labelName = $this->getAttributeLabel($item);
                        $this->updateHistory[]="<span>{$labelName}：".$this->updateStrValue($item,$preRow[$item])." 修改为 ".$this->updateStrValue($item,$nowRow[$item])."</span>";
                    }
                }
                if(!empty($this->updateHistory)){
                    Yii::app()->db->createCommand()->insert("fed_project_history", array(
                        'project_id'=>$this->id,
                        'update_type'=>1,
                        'update_html'=>implode("<br/>",$this->updateHistory),
                        'update_json'=>json_encode($preRow),
                        'lcu'=>$uid,
                    ));
                }
                break;
        }
    }

    public function saveData(){
        $this->updateHistory=array();
        $this->preRow = self::getProjectRow($this->id);
        $uid = Yii::app()->user->id;
        $this->assign_str_user = FunctionSearch::getUserDisplayNameForArr($this->assign_user);
        $nowDate = date_format(date_create(),"Y-m-d H:i:s");
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        switch ($this->getScenario()){
            case "new":
                $this->lcu = $uid;
                $this->lcd = $nowDate;
                Yii::app()->db->createCommand()->insert("fed_project", array(
                    'menu_id'=>$this->menu_id,
                    'status_type'=>$this->status_type,
                    'project_name'=>$this->project_name,
                    'project_type'=>$this->project_type,
                    'project_text'=>$this->project_text,
                    'assign_str_user'=>$this->assign_str_user,
                    'assign_user'=>implode(",",$this->assign_user),
                    'plan_date'=>empty($this->plan_date)?null:$this->plan_date,
                    'plan_start_date'=>empty($this->plan_start_date)?null:$this->plan_start_date,
                    'urgency'=>empty($this->urgency)?null:$this->urgency,
                    'start_date'=>$this->lcd,
                    'current_user'=>$this->lcu,
                    'lcu'=>$this->lcu,
                ));
                $this->id = Yii::app()->db->getLastInsertID();
                $this->lenStr();
                Yii::app()->db->createCommand()->update('fed_project', array(
                    'project_code'=>$this->project_code
                ), 'id=:id', array(':id'=>$this->id));
                break;
            case "edit":
                Yii::app()->db->createCommand()->update('fed_project', array(
                    'status_type'=>$this->status_type,
                    'project_name'=>$this->project_name,
                    'project_type'=>$this->project_type,
                    'project_text'=>$this->project_text,
                    'assign_str_user'=>$this->assign_str_user,
                    'assign_user'=>implode(",",$this->assign_user),
                    'plan_date'=>empty($this->plan_date)?null:$this->plan_date,
                    'plan_start_date'=>empty($this->plan_start_date)?null:$this->plan_start_date,
                    'urgency'=>empty($this->urgency)?null:$this->urgency,
                    'start_date'=>empty($this->old_status_type)?$nowDate:$this->start_date,
                    'luu'=>$uid,
                ), "id={$this->id}");
                break;
            case "delete":
                Yii::app()->db->createCommand()->delete('fed_project_email', 'project_id=:id', array(':id'=>$this->id));
                Yii::app()->db->createCommand()->delete('fed_project_history', 'project_id=:id', array(':id'=>$this->id));
                Yii::app()->db->createCommand()->delete('fed_project_user', 'project_id=:id', array(':id'=>$this->id));
                Yii::app()->db->createCommand()->delete('fed_project', 'id=:id', array(':id'=>$this->id));
                break;
            default:
                break;
        }
        $this->saveEmailInfo();//保存邮箱权限
        $this->saveHistory();//生成历史记录
        $this->saveAssignUser();//多个员工跟进
        $this->updateDocman('PROM');//保存附件

        if(!empty($this->status_type)){
            //$this->sendEmail();//发送邮件
            $this->sendFlow();//发送邮件
        }

        $transaction->commit();
        if($this->getScenario()=="new"){
            $this->setScenario("edit");
        }
    }

    protected function sendFlow(){
        $flowModel = new CNoticeFlowModel($this->menu_code,$this->id);
        $flowModel->setMB_PC_Url("projectManage/view",array("index"=>$this->id));
        switch ($this->getScenario()){
            case "new"://新增
                $subject = "[LBS-{$this->menu_name}] 新增项目《";
                $subject.=FunctionList::getProjectTypeStr($this->project_type);
                $subject.="》{$this->project_name}";
                $flowModel->setSubject($subject);
                $message=ProjectEmailHtml::projectEmailHtmlForNew($this);
                $flowModel->setMessage($message);
                $proType=array(1,2);
                break;
            case "edit"://修改
                if(empty($this->updateHistory)){
                    return false;//如果没有修改内容，不发送邮件
                }
                $subject = "[LBS-{$this->menu_name}] 项目修改《";
                $subject.=FunctionList::getProjectTypeStr($this->project_type);
                $subject.="》{$this->project_name}";
                $flowModel->setSubject($subject);
                $message=ProjectEmailHtml::projectEmailHtmlForUpdate($this);
                $flowModel->setMessage($message);
                $proType=array(1);
                break;
            default:
                return false;
        }
        $flowModel->addEmailToLcuList($this->assign_user);
        $sendList=array();
        if($this->assign_plan!=100){
            $flowModel->note_type=1;//审核流程
            $flowModel->saveFlowAll('',$this->menu_code);
            $sendList = array(
                "to_user"=>$flowModel->to_user,
                "to_addr"=>$flowModel->to_addr,
            );
        }
        $flowModel->note_type=2;//通知流程
        $flowModel->addEmailToLcu($this->lcu);
        $flowModel->addEmailToProjectAndType($this->id,$proType);
        $flowModel->notEmailToLcu(Yii::app()->user->id);
        $flowModel->notSendList($sendList);
        $flowModel->saveNoticeAll();
    }

    protected function sendEmail(){
        $emailModel = new Email();
        switch ($this->getScenario()){
            case "new"://新增
                $subject = "[LBS-{$this->menu_name}] 新增项目《";
                $subject.=FunctionList::getProjectTypeStr($this->project_type);
                $subject.="》{$this->project_name}";
                $emailModel->setSubject($subject);
                $message=ProjectEmailHtml::projectEmailHtmlForNew($this);
                $emailModel->setMessage($message);
                $emailModel->addEmailToProjectAndType($this->id,array(1,2));
                break;
            case "edit"://修改
                if(empty($this->updateHistory)){
                    return false;//如果没有修改内容，不发送邮件
                }
                $subject = "[LBS-{$this->menu_name}] 项目修改《";
                $subject.=FunctionList::getProjectTypeStr($this->project_type);
                $subject.="》{$this->project_name}";
                $emailModel->setSubject($subject);
                $message=ProjectEmailHtml::projectEmailHtmlForUpdate($this);
                $emailModel->setMessage($message);
                $emailModel->addEmailToProjectAndType($this->id,array(1));
                break;
            default:
                return false;
        }
        $emailModel->addEmailToLcu($this->lcu);
        $emailModel->addEmailToLcuList($this->assign_user);
        $emailModel->notEmailToLcu(Yii::app()->user->id);

        $emailModel->sent();
        return true;
    }

    protected function updateDocman($doctype) {
        if ($this->getScenario()=='new') {
            $connection = Yii::app()->db;
            $docidx = strtolower($doctype);
            if ($this->docMasterId[$docidx] > 0) {
                $docman = new DocMan($doctype,$this->id,get_class($this));
                $docman->masterId = $this->docMasterId[$docidx];
                $docman->updateDocId($connection, $this->docMasterId[$docidx]);
            }
        }
    }

    private function saveEmailInfo(){
        if(in_array($this->getScenario(),array("new","edit"))){
            $uid = Yii::app()->user->id;
            foreach ($this->emailList as $row){
                $emailInfo = Yii::app()->db->createCommand()->select("id,email_type")
                    ->from("fed_project_email")
                    ->where("project_id=:id and username=:username",
                        array(":id"=>$this->id,":username"=>$row["username"])
                    )->queryRow();
                if(!$emailInfo){ //如果不存在
                    Yii::app()->db->createCommand()->insert("fed_project_email", array(
                        'project_id'=>$this->id,
                        'username'=>$row["username"],
                        'email_type'=>$row["emailType"],
                        'lcu'=>$uid,
                    ));
                }elseif($row["emailType"]!=$emailInfo["email_type"]){
                    Yii::app()->db->createCommand()->update('fed_project_email', array(
                        'email_type'=>$row["emailType"],
                    ), 'id=:id', array(':id'=>$row["id"]));
                    $this->updateHistory[]="<span>{$row["username"]}：".FunctionList::getProjectEmailTypeStr($emailInfo["email_type"])." 修改为 ".FunctionList::getProjectEmailTypeStr($row["emailType"])."</span>";
                }
            }
        }
    }

    private function lenStr(){
        $code = strval($this->id);
        $this->project_code = FunctionList::getMenuCodeForMin($this->menu_code);
        for($i = 0;$i < 7-strlen($code);$i++){
            $this->project_code.="0";
        }
        $this->project_code .= $code;
    }


    public function getAjaxFileTable($id){
        $this->id = $id;
        $docman = new DocMan($this->docType,$id,get_class($this));
        $docman->masterId = $this->docMasterId[strtolower($docman->docType)];
        $html = $docman->ajaxGenTableFileList();//标的主附件
        $msg = Yii::t('dialog','No File Record');
        $rtn = "<tr><td>&nbsp;</td><td colspan=2>$msg</td></tr>";
        $html = $html==$rtn?"":$html;
        $rows = Yii::app()->db->createCommand()->select("id")->from("fed_project_assign")
            ->where("project_id=:id",array(":id"=>$id))->order("lcd asc")->queryAll();
        if($rows){
            $infoModel = new AssignPlanModel("new");
            foreach ($rows as $row){ //获取记录的附件
                $docman = new DocMan($infoModel->docType,$row["id"],get_class($infoModel));
                $docman->masterId = $infoModel->docMasterId[strtolower($docman->docType)];
                $infoHtml= $docman->ajaxGenTableFileList();
                $infoHtml = $infoHtml==$rtn?"":$infoHtml;
                $html.= $infoHtml;
            }
        }
        $html=empty($html)?$rtn:$html;
        return $html;
    }
}
