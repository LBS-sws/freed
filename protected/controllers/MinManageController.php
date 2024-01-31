<?php

/**
 * Created by PhpStorm.
 * User: 沈超
 * Date: 2017/6/7 0008
 * Time: 上午 11:30
 */
class MinManageController extends Controller
{
    public $function_id='TE19';

    public function init(){
        $session = Yii::app()->session;
        if(key_exists("menu_code",$_GET)){
            $code = $_GET["menu_code"];
            $session["menu_code"]=$code;
        }elseif (isset($session['menu_code']) && !empty($session['menu_code'])) {
            $code = $session['menu_code'];
        }else{
            $code = "无";
        }
        $this->function_id = "{$code}01";
        parent::init();
    }

    public function filters()
    {
        return array(
            'enforceSessionExpiration',
            'enforceNoConcurrentLogin',
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('add','save','saveAssign','delete'),
                'expression'=>array('MinManageController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('view','edit','ajaxDetail'),
                'expression'=>array('MinManageController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    private function validateMenuCode($menuCode,$type="RW"){
        if($menuCode!=$this->function_id){
            $session = Yii::app()->session;
            if($type=="RW"){
                if(Yii::app()->user->validRWFunction($menuCode)){
                    $session["menu_code"]=FunctionList::getMenuCodeForMin($menuCode);
                    $session["active_func"]=$menuCode;
                    $this->function_id = $menuCode;
                    return true;
                }
            }else{
                if(Yii::app()->user->validFunction($menuCode)){
                    $session["menu_code"]=FunctionList::getMenuCodeForMin($menuCode);
                    $session["active_func"]=$menuCode;
                    $this->function_id = $menuCode;
                    return true;
                }
            }
            return false;
        }else{
            return true;
        }
    }

    public static function allowReadWrite() {
        $session = Yii::app()->session;
        $code = isset($session['menu_code'])?$session['menu_code']:"dd";
        return Yii::app()->user->validRWFunction("{$code}01");
    }

    public static function allowReadOnly() {
        $session = Yii::app()->session;
        $code = isset($session['menu_code'])?$session['menu_code']:"dd";
        return Yii::app()->user->validFunction("{$code}01");
    }

    public static function allowRead() {
        return true;
    }

    //详情列表的異步請求
    public function actionAjaxDetail($index){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $min_id = key_exists("id",$_GET)?$_GET["id"]:0;
            $model = new MinManageList();
            if($model->retrieveMenuId($index)&&$this->validateMenuCode($model->menu_code)){//验证菜单栏
                $html =$model->getMinInfoHtmlTr($min_id);
            }else{
                $html ="";
            }
            echo CJSON::encode(array('status'=>1,'html'=>$html));//Yii 的方法将数组处理成json数据
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

    public function actionSave()
    {
        if (isset($_POST['MinManageModel'])) {
            $model = new MinManageModel($_POST['MinManageModel']['scenario']);
            $model->attributes = $_POST['MinManageModel'];
            if ($model->validate()&&$this->validateMenuCode($model->menu_code)) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                $this->redirect(Yii::app()->createUrl('MinManage/edit',array('index'=>$model->id)));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model,));
            }
        }
    }

    public function actionAdd($index,$project_id)
    {
        $model = new MinManageModel('new');
        $bool = $model->resetDataForAdd($project_id);
        if ($bool&&$model->retrieveMenuData($index)&&$this->validateMenuCode($model->menu_code)) {
            $this->render('form',array('model'=>$model,));
        } else {
            throw new CHttpException(404,'The requested page does not exist.');
        }
    }

    public function actionEdit($index)
    {
        $model = new MinManageModel('edit');
        if ($model->retrieveData($index)&&$this->validateMenuCode($model->menu_code)) {
            $model->getMinEmailInfo();
            $this->render('form',array('model'=>$model,));
        } else {
            throw new CHttpException(404,'The requested page does not exist.');
        }
    }

    public function actionView($index)
    {
        $model = new MinManageModel('edit');
        $assignModel = new AssignPlanModel('edit');
        if ($model->retrieveData($index)&&$this->validateMenuCode($model->menu_code)) {
            $this->render('view',array('model'=>$model,'assignModel'=>$assignModel,));
        } else {
            throw new CHttpException(404,'The requested page does not exist.');
        }
    }

    public function actionDelete()
    {
        $model = new MinManageModel('delete');
        if (isset($_POST['MinManageModel'])) {
            $model->attributes = $_POST['MinManageModel'];
            if ($model->validate()&&$this->validateMenuCode($model->menu_code)) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('MinManage/index',array("index"=>$model->menu_id)));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model));
            }
        }
    }

    public function actionSaveAssign(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $scenario = key_exists("scenario",$_POST)?$_POST['scenario']:"";
            $model = new MinPlanModel($scenario);
            $model->attributes = $_POST;
            if($model->validate()&&$this->validateMenuCode($model->menu_code)){
                $model->saveData();
                $hmtl = $model->getAjaxHtml();
                $project_plan = FunctionList::getAssignPlanStr($model->assign_plan);
                $project_status = FunctionList::getProjectStatusStr($model->project_status);
                echo CJSON::encode(array('status'=>1,'html'=>$hmtl,'project_plan'=>$project_plan,'project_status'=>$project_status));
            }else{
                $message = CHtml::errorSummary($model);
                echo CJSON::encode(array('status'=>0,'message'=>$message));
            }
            die();
        }else{
            $this->redirect(Yii::app()->createUrl(''));
        }
    }
}