<?php

class MinPlanModel extends AssignPlanModel
{
    public $min_id;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('id,menu_id,min_id,assign_text,assign_plan','safe'),
            array('min_id,assign_text,assign_plan','required'),
            array('min_id','validateMinID'),
            array('assign_plan', 'numerical', 'integerOnly'=>true, 'min'=>0, 'max'=>100),
        );
    }

    public function validateMinID($attribute, $params){
        $row = Yii::app()->db->createCommand()
            ->select("a.project_id,a.project_type,a.project_name,a.lcu,a.assign_user,a.start_date,a.id,a.menu_id,b.menu_code,b.menu_name")
            ->from("fed_min a")
            ->leftJoin("fed_setting b","a.menu_id=b.id")
            ->where('a.id=:id',array(':id'=>$this->min_id))->queryRow();
        if(!$row){
            $message = "数据异常请刷新重试";
            $this->addError($attribute,$message);
        }else{
            $this->project_lcu = $row["lcu"];
            //$this->assign_user = $row["assign_user"];
            $this->assign_user = explode(",",$row["assign_user"]);
            $this->start_date = $row["start_date"];
            $this->project_id = $row["project_id"];
            $this->project_type = $row["project_type"];
            $this->project_name = $row["project_name"];
            $this->menu_id = $row["menu_id"];
            $this->menu_name = $row["menu_name"];
            $this->menu_code = $row["menu_code"].$this->code_pre;
            $this->lcd = date("Y-m-d H:i:s");
        }
    }

    public function retrieveMinID($min_id){
        $row = Yii::app()->db->createCommand()
            ->select("a.id,a.project_id,a.project_type,a.project_name,a.menu_id,b.menu_code,b.menu_name")
            ->from("fed_min a")
            ->leftJoin("fed_setting b","a.menu_id=b.id")
            ->where('a.id=:id',array(':id'=>$min_id))->queryRow();
        if($row){
            $this->min_id = $min_id;
            $this->project_id = $row["project_id"];
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
            ->from("fed_min_assign f")
            ->leftJoin("fed_min a","f.min_id=a.id")
            ->leftJoin("fed_setting b","a.menu_id=b.id")
            ->where("f.id =:id",array(":id"=>$index))->queryRow();
        if($row){
            $this->id = $index;
            $this->menu_id = $row["menu_id"];
            $this->menu_name = $row["menu_name"];
            $this->menu_code = $row["menu_code"].$this->code_pre;

            $this->min_id = $row["min_id"];
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
            ->from("fed_min_assign a")
            ->leftJoin("security{$suffix}.sec_user b","a.username=b.username")
            ->where('a.min_id=:id and a.id!='.$this->id,array(':id'=>$this->min_id))->order("lcd desc")->limit($num)->queryAll();
        return $rows?$rows:array();
    }

    public static function printAssignHtml($min_id){
        $suffix = Yii::app()->params['envSuffix'];
        $html = "";
        $html.= "<div id='assignDiv'>";
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.assign_plan,a.assign_text,a.lcd,b.disp_name")
            ->from("fed_min_assign a")
            ->leftJoin("security{$suffix}.sec_user b","a.username=b.username")
            ->where('a.min_id=:id',array(':id'=>$min_id))->order("lcd asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $html.= self::getBlockQuoteHtml($row);
            }
        }
        $html.= "</div>";
        return $html;
    }

    private function computePlan(){
        if($this->getScenario()=="new"){
            $startDate = $this->start_date;
            $this->prev_plan = 0;
            $this->prev_id = 0;
            $prevAssign = Yii::app()->db->createCommand()->select("a.id,a.lcd,a.assign_plan")
                ->from("fed_min_assign a")
                ->where('a.min_id=:id',array(':id'=>$this->min_id))
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
        $this->computePlan();
        $uid = Yii::app()->user->id;
        switch ($this->getScenario()){
            case "new":
                $this->username = $uid;
                Yii::app()->db->createCommand()->insert("fed_min_assign", array(
                    'min_id'=>$this->min_id,
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
                Yii::app()->db->createCommand()->update('fed_min_assign', array(
                    'assign_plan'=>$this->assign_plan,
                    'assign_text'=>$this->assign_text,
                    'luu'=>$uid,
                ), "id={$this->id}");
                break;
            case "delete":
                Yii::app()->db->createCommand()->delete('fed_min_assign', 'id=:id', array(':id'=>$this->id));
                break;
            default:
                break;
        }

        $this->computeMin();

        //$this->sendEmail();//发送邮件
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

    private function computeMin(){
        $uid = Yii::app()->user->id;
        $this->project_status=FunctionList::getStatusNumForPlan($this->assign_plan);
        Yii::app()->db->createCommand()->update('fed_min', array(
            'assign_plan'=>$this->assign_plan,
            'project_status'=>$this->project_status,
            'current_user'=>$uid,
            'end_date'=>$this->lcd,
        ), "id={$this->min_id}");
    }

    public function getReadonly(){
        return Yii::app()->user->validRWFunction($this->menu_code);
    }
}
