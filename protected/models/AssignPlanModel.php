<?php

class AssignPlanModel extends CFormModel
{
    public $id;
    public $menu_id;
    public $menu_name;
    public $menu_code;

    public $project_id;
    public $project_type;
    public $project_name;
    public $project_status;
    public $start_date;
    public $username;
    public $prev_plan;
    public $assign_plan;
    public $assign_text;
    public $assign_day;
    public $assign_hour;
    public $assign_minute;
    public $prev_id;
    public $diff_timer;
    public $lcd;
    public $project_lcu;
    public $assign_user;
    public $current_user;

    protected $code_pre="01";

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
            'assign_text'=>Yii::t('freed','assign text'),
            'project_status'=>Yii::t('freed','project status'),
            'assign_plan'=>Yii::t('freed','assign plan'),
            'lcu'=>Yii::t('freed','File builder'),
            'lcd'=>Yii::t('freed','File date'),
            'assign_user'=>Yii::t('freed','assign user'),
        );
    }

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('id,menu_id,project_id,assign_text,assign_plan','safe'),
            array('project_id,assign_text,assign_plan','required'),
            array('project_id','validateProjectID'),
            array('assign_plan', 'numerical', 'integerOnly'=>true, 'min'=>0, 'max'=>100),
        );
    }

    public function validateProjectID($attribute, $params){
        $row = Yii::app()->db->createCommand()
            ->select("a.current_user,a.project_type,a.project_name,a.lcu,a.assign_user,a.start_date,a.id,a.menu_id,b.menu_code,b.menu_name")
            ->from("fed_project a")
            ->leftJoin("fed_setting b","a.menu_id=b.id")
            ->where('a.id=:id',array(':id'=>$this->project_id))->queryRow();
        if(!$row){
            $message = "数据异常请刷新重试";
            $this->addError($attribute,$message);
        }else{
            $this->project_lcu = $row["lcu"];
            $this->assign_user = explode(",",$row["assign_user"]);
            $this->start_date = $row["start_date"];
            $this->project_type = $row["project_type"];
            $this->project_name = $row["project_name"];
            $this->menu_id = $row["menu_id"];
            $this->menu_name = $row["menu_name"];
            $this->menu_code = $row["menu_code"].$this->code_pre;
            $this->current_user = $row["current_user"];
            $this->lcd = date("Y-m-d H:i:s");
        }
    }

    public function retrieveProjectID($project_id){
        $row = Yii::app()->db->createCommand()
            ->select("a.id,a.project_type,a.project_name,a.menu_id,b.menu_code,b.menu_name")
            ->from("fed_project a")
            ->leftJoin("fed_setting b","a.menu_id=b.id")
            ->where('a.id=:id',array(':id'=>$project_id))->queryRow();
        if($row){
            $this->project_id = $project_id;
            $this->project_type = $row["project_type"];
            $this->project_name = $row["project_name"];
            $this->menu_id = $row["menu_id"];
            $this->menu_name = $row["menu_name"];
            $this->menu_code = $row["menu_code"].$this->code_pre;
        }
    }

    public function retrieveData($index){ //修改
        $row = Yii::app()->db->createCommand()
            ->select("f.*,a.menu_id,b.menu_name,b.menu_code")
            ->from("fed_project_assign f")
            ->leftJoin("fed_project a","f.project_id=a.id")
            ->leftJoin("fed_setting b","a.menu_id=b.id")
            ->where("f.id =:id",array(":id"=>$index))->queryRow();
        if($row){
            $this->id = $index;
            $this->menu_id = $row["menu_id"];
            $this->menu_name = $row["menu_name"];
            $this->menu_code = $row["menu_code"].$this->code_pre;

            $this->project_id = $row["project_id"];
            $this->username = $row["username"];
            $this->prev_plan = $row["prev_plan"];
            $this->assign_plan = $row["assign_plan"];
            $this->assign_text = $row["assign_text"];
            $this->assign_day = $row["assign_day"];
            $this->assign_hour = $row["assign_hour"];
            $this->assign_minute = $row["assign_minute"];
            $this->prev_id = $row["prev_id"];
            $this->diff_timer = $row["diff_timer"];
            return true;
        }
        return false;
    }

    private function getAssignHistoryNotID($num=3){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.assign_plan,a.assign_text,a.lcd,b.disp_name")
            ->from("fed_project_assign a")
            ->leftJoin("security{$suffix}.sec_user b","a.username=b.username")
            ->where('a.project_id=:id and a.id!='.$this->id,array(':id'=>$this->project_id))->order("lcd desc")->limit($num)->queryAll();
        return $rows?$rows:array();
    }

    public static function printAssignHtml($project_id){
        $suffix = Yii::app()->params['envSuffix'];
        $html = "";
        $html.= "<div id='assignDiv'>";
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.assign_plan,a.assign_text,a.lcd,b.disp_name")
            ->from("fed_project_assign a")
            ->leftJoin("security{$suffix}.sec_user b","a.username=b.username")
            ->where('a.project_id=:id',array(':id'=>$project_id))->order("lcd asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $html.= self::getBlockQuoteHtml($row);
            }
        }
        $html.= "</div>";
        return $html;
    }

    public function getAjaxHtml(){
        $uid = Yii::app()->user->id;
        $row = array(
            "assign_plan"=>$this->assign_plan,
            "assign_text"=>$this->assign_text,
            "disp_name"=>FunctionSearch::getUserDisplayName($uid),
            "lcd"=>$this->lcd,
        );
        return self::getBlockQuoteHtml($row,true);
    }

    public static function getBlockQuoteHtml($row,$active=false){
        $assign_plan = FunctionList::getAssignPlanStr($row["assign_plan"]);
        if($active){
            $html= "<div class=\"form-group active\">";
        }else{
            $html= "<div class=\"form-group\">";
        }
        $html.= "<div class=\"col-lg-12 div-assign \" >";
        $html.= "<div class=''>";
        $html.= "<div class=\"col-lg-4\">";
        $html.= "<h4>";
        $html.= "<b>".$row["disp_name"]."</b>";
        $html.= "<small>".$assign_plan."</small>";
        $html.= "</h4>";
        $html.= "</div>";
        $html.= "<div class=\"col-lg-8  div-assign-time\">";
        $html.= "<small>".$row["lcd"]."</small>";
        $html.= "</div>";
        $html.= "<div class=\"col-lg-12\">";
        $html.= "<div>".$row["assign_text"]."</div>";
        $html.= "</div>";
        $html.= "</div>";
        $html.= "</div>";
        $html.= "</div>";
        return $html;
    }

    private function computePlan(){
        if($this->getScenario()=="new"){
            $startDate = $this->start_date;
            $this->prev_plan = 0;
            $this->prev_id = 0;
            $prevAssign = Yii::app()->db->createCommand()->select("a.id,a.lcd,a.assign_plan")
                ->from("fed_project_assign a")
                ->where('a.project_id=:id',array(':id'=>$this->project_id))
                ->order("a.lcd desc")->queryRow();
            if($prevAssign){
                //如果上一条进度为100，则时间跨度需要从0开始
                $startDate = $prevAssign["assign_plan"]==100?$this->lcd:$prevAssign["lcd"];
                $this->prev_plan = $prevAssign["assign_plan"];
                $this->prev_id = $prevAssign["id"];
            }
            $diffTimer = strtotime($this->lcd)-strtotime($startDate);
            $this->diff_timer = $diffTimer;
            $this->assign_day = floor($diffTimer/(60*60*24));
            $this->assign_hour = ($diffTimer/(60*60))%24;
            $this->assign_minute = ($diffTimer/60)%60;
        }
    }

    public function saveData(){
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        $this->computePlan();
        $uid = Yii::app()->user->id;
        switch ($this->getScenario()){
            case "new":
                $this->username = $uid;
                Yii::app()->db->createCommand()->insert("fed_project_assign", array(
                    'project_id'=>$this->project_id,
                    'username'=>$uid,
                    'assign_plan'=>$this->assign_plan,
                    'assign_text'=>$this->assign_text,
                    'prev_plan'=>$this->prev_plan,
                    'assign_day'=>$this->assign_day,
                    'assign_hour'=>$this->assign_hour,
                    'assign_minute'=>$this->assign_minute,
                    'prev_id'=>$this->prev_id,
                    'diff_timer'=>$this->diff_timer,
                    'lcu'=>$uid,
                    'lcd'=>$this->lcd,
                    'lud'=>$this->lcd,//由于数据库录入时间有误差
                ));
                $this->id = Yii::app()->db->getLastInsertID();
                break;
            case "edit":
                Yii::app()->db->createCommand()->update('fed_project_assign', array(
                    'assign_plan'=>$this->assign_plan,
                    'assign_text'=>$this->assign_text,
                    'luu'=>$uid,
                ), "id={$this->id}");
                break;
            case "delete":
                Yii::app()->db->createCommand()->delete('fed_project_assign', 'id=:id', array(':id'=>$this->id));
                break;
            default:
                break;
        }

        $this->computeProject();

        $this->sendFlow();//发送邮件
        //$this->sendEmail();//发送邮件
        $transaction->commit();
    }

    protected function sendFlow(){
        $flowModel = new CNoticeFlowModel($this->menu_code,$this->project_id);
        $flowModel->setMB_PC_Url("projectManage/view",array("index"=>$this->project_id));
        $uid = Yii::app()->user->id;
        switch ($this->getScenario()){
            case "new"://新增
                if($this->assign_plan==100){ //完成
                    $subject = "[LBS-{$this->menu_name}] 已完成《";
                    $status = array(1,2,3);
                }else{
                    $subject = "[LBS-{$this->menu_name}] 项目跟进《";
                    $status = array(1);
                }
                $subject.=FunctionList::getProjectTypeStr($this->project_type);
                $subject.="》{$this->project_name}";
                $flowModel->setSubject($subject);
                $rows = $this->getAssignHistoryNotID();
                $message=ProjectEmailHtml::projectEmailHtmlForAssign($this,$rows);
                $flowModel->setMessage($message);
                break;
            case "edit"://修改
                return false;
            default:
                return false;
        }
        $sendList = array();
        if($this->assign_plan==100){ //项目未完成
            $flowModel->setOwerNumForUsername($this->project_lcu);
            $flowModel->sendFinishFlow($this->menu_code);
        }else{
            if($this->project_lcu==$uid){ //录入人写了跟进事项
                $flowModel->note_type=1;//审核流程
                $flowModel->addEmailToLcuList($this->assign_user);
                $flowModel->saveFlowAll('',$this->menu_code);
            }elseif(in_array($uid,$this->assign_user)){ //跟进人写了跟进事项
                $flowModel->note_type=1;//审核流程
                $flowModel->addEmailToLcu($this->project_lcu);
                $flowModel->saveFlowAll('',$this->menu_code);
            }
            $sendList = array(
                "to_user"=>$flowModel->to_user,
                "to_addr"=>$flowModel->to_addr,
            );
        }
        $flowModel->note_type=2;//通知流程
        $flowModel->addEmailToProjectAndType($this->project_id,$status);
        $flowModel->addEmailToLcu($this->project_lcu);
        $flowModel->addEmailToLcuList($this->assign_user);
        $flowModel->notEmailToLcu(Yii::app()->user->id);
        $flowModel->notSendList($sendList);
        $flowModel->saveNoticeAll();
        return true;
    }

    protected function sendEmail(){
        $emailModel = new Email();
        switch ($this->getScenario()){
            case "new"://新增
                if($this->assign_plan==100){ //完成
                    $subject = "[LBS-{$this->menu_name}] 已完成《";
                    $status = array(1,2,3);
                }else{
                    $subject = "[LBS-{$this->menu_name}] 项目跟进《";
                    $status = array(1);
                }
                $subject.=FunctionList::getProjectTypeStr($this->project_type);
                $subject.="》{$this->project_name}";
                $emailModel->setSubject($subject);
                $rows = $this->getAssignHistoryNotID();
                $message=ProjectEmailHtml::projectEmailHtmlForAssign($this,$rows);
                $emailModel->setMessage($message);
                $emailModel->addEmailToProjectAndType($this->project_id,$status);
                break;
            case "edit"://修改
                return false;
            default:
                return false;
        }
        $emailModel->addEmailToLcu($this->project_lcu);
        $emailModel->addEmailToLcuList($this->assign_user);
        $emailModel->notEmailToLcu(Yii::app()->user->id);

        $emailModel->sent();
        return true;
    }

    private function computeProject(){
        $uid = Yii::app()->user->id;
        $this->project_status=FunctionList::getStatusNumForPlan($this->assign_plan);
        Yii::app()->db->createCommand()->update('fed_project', array(
            'assign_plan'=>$this->assign_plan,
            'project_status'=>$this->project_status,
            'current_user'=>$uid,
            'end_date'=>$this->lcd,
        ), "id={$this->project_id}");
    }

    public function getReadonly(){
        return Yii::app()->user->validRWFunction($this->menu_code);
    }
}
