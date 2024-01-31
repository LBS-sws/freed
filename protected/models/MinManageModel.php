<?php

class MinManageModel extends ProjectManageModel
{
    public $project_id;

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'id'=>Yii::t('freed','ID'),
            'project_code'=>Yii::t('freed','min project code'),
            'project_name'=>Yii::t('freed','min project name'),
            'project_type'=>Yii::t('freed','min project type'),
            'project_text'=>Yii::t('freed','project description'),
            'project_status'=>Yii::t('freed','project status'),
            'assign_plan'=>Yii::t('freed','assign plan'),
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
            array('id,menu_id,project_id,plan_date,plan_start_date,project_code,project_name,project_type,project_text,emailList,
            project_status,assign_plan,urgency,current_user,assign_user,start_date,end_date,lcu,luu,lcd,lud','safe'),
            array('menu_id,project_name,project_type,project_text,assign_user','required'),
            array('menu_id','validateMenuID'),
            array('id','validateDelete','on'=>array("delete")),
            array('project_id','validateProjectId','on'=>array("edit","new","delete")),
            array('project_name','validateName','on'=>array("edit","new")),
            array('emailList','validateEmailList','on'=>array("edit","new")),

            array('files, removeFileId, docMasterId','safe'),
        );
    }

    public function validateDelete($attribute, $params){
        $row = Yii::app()->db->createCommand()->select("id")->from("fed_min_assign")
            ->where('min_id=:id',array(':id'=>$this->id))->queryRow();
        if($row){
            $message = "该项目已有跟进内容，无法删除";
            $this->addError($attribute,$message);
        }
    }

    public function validateProjectId($attribute, $params){
        $row = Yii::app()->db->createCommand()->select("id")->from("fed_project")
            ->where('id=:id',array(':id'=>$this->project_id))->queryRow();
        if(!$row){
            $message = "大项目不存在，请刷新页面重试";
            $this->addError($attribute,$message);
        }
    }

    public function validateName($attribute, $params){
        $id = -1;
        if(!empty($this->id)){
            $id = $this->id;
        }
        $row = Yii::app()->db->createCommand()->select("project_code")->from("fed_min")
            ->where('project_name=:name and id!=:id and project_id=:project_id',
                array(':name'=>$this->project_name,':id'=>$id,':project_id'=>$this->project_id))->queryRow();
        if($row){
            $message = "已存在相同的项目标题（{$row["project_code"]}），请重新命名";
            $this->addError($attribute,$message);
        }
    }

    public function resetDataForAdd($project_id){ //新增
        $row = Yii::app()->db->createCommand()->select("a.*")
            ->from("fed_project a")
            ->where("a.id =:id",array(":id"=>$project_id))->queryRow();
        if($row){
            $this->project_id = $project_id;
            $this->assign_user = explode(",",$row["assign_user"]);
            $this->assign_str_user = $row["assign_str_user"];
            $this->id = $project_id;//强制调用主项目的邮件通知
            $this->getProjectEmailInfo();
            $this->id = null;//还原
            return true;
        }
        return false;
    }

    public function retrieveData($index){ //修改
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("a.*,b.menu_name,b.menu_code")
            ->from("fed_min a")
            ->leftJoin("fed_setting b","a.menu_id=b.id")
            ->where("a.id =:id",array(":id"=>$index))->queryRow();
        if($row){
            $this->id = $index;
            $this->menu_id = $row["menu_id"];
            $this->menu_name = $row["menu_name"];
            $this->menu_code = $row["menu_code"].$this->code_pre;

            $this->project_id = $row["project_id"];
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
            return true;
        }
        return false;
    }

    public static function getMinHistoryRows($min_id){
        $rows = Yii::app()->db->createCommand()->select("*")->from("fed_min_history")
            ->where("min_id =:id",array(":id"=>$min_id))->order("id desc")->queryAll();
        return $rows;
    }

    public function getMinEmailInfo(){
        $rows = Yii::app()->db->createCommand()->select("id,username,email_type")
            ->from("fed_min_email")
            ->where("min_id =:id",array(":id"=>$this->id))->queryAll();
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

    public static function getMinRow($min_id){
        $row = Yii::app()->db->createCommand()->select("*")->from("fed_min")
            ->where("id =:id",array(":id"=>$min_id))->queryRow();
        return $row;
    }

    public function getUpdateHistory(){
        return $this->updateHistory;
    }

    private function updateStrList(){
        return array("project_type","project_name","assign_user","plan_date","plan_start_date","urgency");
    }

    private function updateStrValue($itemStr,$value){
        switch ($itemStr){
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
            Yii::app()->db->createCommand()->delete('fed_min_user', "min_id=:id and username not in('{$userSql}')", array(':id'=>$this->id));

            //添加新的跟进人员
            $rows = Yii::app()->db->createCommand()->select("id,username")->from("fed_min_user")
                ->where("min_id =:id",array(":id"=>$this->id))->queryAll();
            $rows = $rows?array_column($rows,"username"):array();
            foreach ($this->assign_user as $user){
                if(!in_array($user,$rows)){
                    Yii::app()->db->createCommand()->insert("fed_min_user", array(
                        'min_id'=>$this->id,
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
                Yii::app()->db->createCommand()->insert("fed_min_history", array(
                    'min_id'=>$this->id,
                    'update_type'=>2,
                    'update_html'=>"新增",
                    'lcu'=>$uid,
                ));
                break;
            case "edit":
                $preRow = $this->preRow;
                $nowRow = self::getMinRow($this->id);
                foreach ($this->updateStrList() as $item){
                    if(key_exists($item,$preRow)&&$nowRow[$item]!==$preRow[$item]){
                        $labelName = $this->getAttributeLabel($item);
                        $this->updateHistory[]="<span>{$labelName}：".$this->updateStrValue($item,$preRow[$item])." 修改为 ".$this->updateStrValue($item,$nowRow[$item])."</span>";
                    }
                }
                if(!empty($this->updateHistory)){
                    Yii::app()->db->createCommand()->insert("fed_min_history", array(
                        'min_id'=>$this->id,
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
        $this->preRow = self::getMinRow($this->id);
        $uid = Yii::app()->user->id;
        $this->assign_str_user = FunctionSearch::getUserDisplayNameForArr($this->assign_user);
        switch ($this->getScenario()){
            case "new":
                $this->lcu = $uid;
                $this->lcd = date("Y-m-d H:i:s");
                Yii::app()->db->createCommand()->insert("fed_min", array(
                    'menu_id'=>$this->menu_id,
                    'project_id'=>$this->project_id,
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
                Yii::app()->db->createCommand()->update('fed_min', array(
                    'project_code'=>$this->project_code
                ), 'id=:id', array(':id'=>$this->id));
                break;
            case "edit":
                Yii::app()->db->createCommand()->update('fed_min', array(
                    'project_name'=>$this->project_name,
                    'project_type'=>$this->project_type,
                    'project_text'=>$this->project_text,
                    'assign_str_user'=>$this->assign_str_user,
                    'assign_user'=>implode(",",$this->assign_user),
                    'plan_date'=>empty($this->plan_date)?null:$this->plan_date,
                    'plan_start_date'=>empty($this->plan_start_date)?null:$this->plan_start_date,
                    'urgency'=>empty($this->urgency)?null:$this->urgency,
                    'luu'=>$uid,
                ), "id={$this->id}");
                break;
            case "delete":
                Yii::app()->db->createCommand()->delete('fed_min', 'id=:id', array(':id'=>$this->id));
                Yii::app()->db->createCommand()->delete('fed_min_email', 'project_id=:id', array(':id'=>$this->id));
                Yii::app()->db->createCommand()->delete('fed_min_history', 'project_id=:id', array(':id'=>$this->id));
                Yii::app()->db->createCommand()->delete('fed_min_user', 'project_id=:id', array(':id'=>$this->id));
                break;
            default:
                break;
        }
        $this->saveEmailInfo();//保存邮箱权限
        $this->saveHistory();//生成历史记录
        $this->saveAssignUser();//多个员工跟进
        //$this->updateDocman('PROM');//保存附件

        //$this->sendEmail();//发送邮件

        if($this->getScenario()=="new"){
            $this->setScenario("edit");
        }
    }

    protected function sendEmail(){
        $emailModel = new Email();
        switch ($this->getScenario()){
            case "new"://新增
                $subject = "[LBS-{$this->menu_name}] 新增小项目《";
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
                $subject = "[LBS-{$this->menu_name}] 小项目修改《";
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
                    ->from("fed_min_email")
                    ->where("min_id=:id and username=:username",
                        array(":id"=>$this->id,":username"=>$row["username"])
                    )->queryRow();
                if(!$emailInfo){ //如果不存在
                    Yii::app()->db->createCommand()->insert("fed_min_email", array(
                        'min_id'=>$this->id,
                        'username'=>$row["username"],
                        'email_type'=>$row["emailType"],
                        'lcu'=>$uid,
                    ));
                }elseif($row["emailType"]!=$emailInfo["email_type"]){
                    Yii::app()->db->createCommand()->update('fed_min_email', array(
                        'email_type'=>$row["emailType"],
                    ), 'id=:id', array(':id'=>$row["id"]));
                    $this->updateHistory[]="<span>{$row["username"]}：".FunctionList::getProjectEmailTypeStr($emailInfo["email_type"])." 修改为 ".FunctionList::getProjectEmailTypeStr($row["emailType"])."</span>";
                }
            }
        }
    }

    private function lenStr(){
        $code = strval($this->id);
        $this->project_code = "MIN";
        for($i = 0;$i < 7-strlen($code);$i++){
            $this->project_code.="0";
        }
        $this->project_code .= $code;
    }
}
