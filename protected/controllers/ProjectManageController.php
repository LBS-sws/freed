<?php

/**
 * Created by PhpStorm.
 * User: 沈超
 * Date: 2017/6/7 0008
 * Time: 上午 11:30
 */
class ProjectManageController extends Controller
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
                'actions'=>array('add','publish','draft','saveAssign','delete',
                    'uploadImgArea','fileRemove','fileupload'),
                'expression'=>array('ProjectManageController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view','edit','ajaxDetail','fileDownload','ajaxFileTable'),
                'expression'=>array('ProjectManageController','allowRead'),
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
        return !Yii::app()->user->isGuest;
    }

    //详情列表的異步請求
    public function actionAjaxDetail($index){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $project_id = key_exists("id",$_GET)?$_GET["id"]:0;
            $model = new ProjectManageList();
            if($model->retrieveMenuId($index)&&$this->validateMenuCode($model->menu_code)){//验证菜单栏
                $html =$model->getProjectInfoHtmlTr($project_id);
            }else{
                $html ="";
            }
            echo CJSON::encode(array('status'=>1,'html'=>$html));//Yii 的方法将数组处理成json数据
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

    public function actionIndex($index,$pageNum=0){
        $model = new ProjectManageList;
        if (isset($_POST['ProjectManageList'])) {
            $model->attributes = $_POST['ProjectManageList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['projectManage_'.$this->function_id]) && !empty($session['projectManage_'.$this->function_id])) {
                $criteria = $session['projectManage_'.$this->function_id];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveAll($index,$model->pageNum);
        if($this->validateMenuCode($model->menu_code)){
            $this->render('index',array('model'=>$model));
        }else{
            throw new CHttpException(404,'The requested page does not exist.');
        }
    }


    public function actionPublish()
    {
        if (isset($_POST['ProjectManageModel'])) {
            $model = new ProjectManageModel($_POST['ProjectManageModel']['scenario']);
            $model->attributes = $_POST['ProjectManageModel'];
            if ($model->validate()&&$this->validateMenuCode($model->menu_code)) {
                $model->status_type=1;
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('freed','Publish Done'));
                $this->redirect(Yii::app()->createUrl('ProjectManage/edit',array('index'=>$model->id)));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model,));
            }
        }
    }

    public function actionDraft()
    {
        if (isset($_POST['ProjectManageModel'])) {
            $model = new ProjectManageModel($_POST['ProjectManageModel']['scenario']);
            $model->attributes = $_POST['ProjectManageModel'];
            if ($model->validate()&&$this->validateMenuCode($model->menu_code)) {
                $model->status_type=0;
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('freed','Draft Done'));
                $this->redirect(Yii::app()->createUrl('ProjectManage/edit',array('index'=>$model->id)));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model,));
            }
        }
    }

    public function actionAdd($index)
    {
        $model = new ProjectManageModel('new');
        if ($model->retrieveMenuData($index)&&$this->validateMenuCode($model->menu_code)) {
            $this->render('form',array('model'=>$model,));
        } else {
            throw new CHttpException(404,'The requested page does not exist.');
        }
    }

    public function actionEdit($index)
    {
        $model = new ProjectManageModel('edit');
        if ($model->retrieveData($index)&&$this->validateMenuCode($model->menu_code)) {
            $model->getProjectEmailInfo();
            $this->render('form',array('model'=>$model,));
        } else {
            throw new CHttpException(404,'The requested page does not exist.');
        }
    }

    public function actionView($index)
    {
        $model = new ProjectManageModel('edit');
        $assignModel = new AssignPlanModel('edit');
        if ($model->retrieveData($index)&&$this->validateMenuCode($model->menu_code)) {
            $this->render('view',array('model'=>$model,'assignModel'=>$assignModel,));
        } else {
            throw new CHttpException(404,'The requested page does not exist.');
        }
    }

    public function actionDelete()
    {
        $model = new ProjectManageModel('delete');
        if (isset($_POST['ProjectManageModel'])) {
            $model->attributes = $_POST['ProjectManageModel'];
            if ($model->validate()&&$this->validateMenuCode($model->menu_code)) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('ProjectManage/index',array("index"=>$model->menu_id)));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model));
            }
        }
    }

    //上傳圖片(富文本)
    public function actionUploadImgArea(){
        $img = CUploadedFile::getInstanceByName("upload");
        $path =Yii::app()->basePath."/../images/uploadArea/";
        $city = Yii::app()->user->city();
        if (!file_exists($path)){
            mkdir($path);
            $myfile = fopen($path."index.php", "w");
            fclose($myfile);
        }
        $url = "images/uploadArea/{$city}_".date("YmdHis").".".$img->getExtensionName();
        if ($img->getError()==0) {
            $img->saveAs($url);
            $url = Yii::app()->getBaseUrl(true)."/".$url;
            echo CJSON::encode(array('uploaded'=>1,'url'=>$url,'fileName'=>$img->getName()));
        }else{
            echo CJSON::encode(array('uploaded'=>1,'url'=>"",'fileName'=>$img->getName(),'error'=>array("message"=>"图片大小不能超过2M")));
        }
        die();
    }

    public function actionSaveAssign(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $scenario = key_exists("scenario",$_POST)?$_POST['scenario']:"";
            $model = new AssignPlanModel($scenario);
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

    public function actionAjaxFileTable($id=0) {
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $model = new ProjectManageModel();
            $html = $model->getAjaxFileTable($id);
            echo CJSON::encode(array("status"=>1,"html"=>$html));
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

    public function actionFileupload($doctype) {
        $model = new ProjectManageModel();
        if (isset($_POST['ProjectManageModel'])) {
            $model->attributes = $_POST['ProjectManageModel'];
            $id = ($_POST['ProjectManageModel']['scenario']=='new') ? 0 : $model->id;
            $docman = new DocMan($model->docType,$id,get_class($model));
            $docman->masterId = $model->docMasterId[strtolower($doctype)];
            if (isset($_FILES[$docman->inputName])) $docman->files = $_FILES[$docman->inputName];
            $docman->fileUpload();
            echo $docman->genTableFileList(false);
        } else {
            echo "NIL";
        }
    }

    public function actionFileRemove($doctype) {
        $model = new ProjectManageModel();
        if (isset($_POST['ProjectManageModel'])) {
            $model->attributes = $_POST['ProjectManageModel'];
            $docman = new DocMan($model->docType,$model->id,'ProjectManageModel');
            $docman->masterId = $model->docMasterId[strtolower($doctype)];
            $docman->fileRemove($model->removeFileId[strtolower($doctype)]);
            echo $docman->genTableFileList(false);
        } else {
            echo "NIL";
        }
    }

    public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
        $sql = "select b.menu_code from fed_project a
          LEFT JOIN fed_setting b on a.menu_id=b.id
          where a.id = $docId";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $menuCode = $row["menu_code"]."01";
            if (Yii::app()->user->validRWFunction($menuCode)) {
                $docman = new DocMan($doctype,$docId,'ProjectManageModel');
                $docman->masterId = $mastId;
                $docman->fileDownload($fileId);
            } else {
                throw new CHttpException(404,'Access right not match.');
            }
        } else {
            throw new CHttpException(404,'Record not found.');
        }
    }
}