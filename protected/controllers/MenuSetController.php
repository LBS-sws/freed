<?php

/**
 * Created by PhpStorm.
 * User: 沈超
 * Date: 2017/6/7 0007
 * Time: 上午 11:30
 */
class MenuSetController extends Controller
{
	public $function_id='SS03';

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
                'actions'=>array('new','edit','save','delete','ajaxDepartment'),
                'expression'=>array('MenuSetController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view'),
                'expression'=>array('MenuSetController','allowReadOnly'),
            ),
            array('allow',
                'actions'=>array('ajaxStaff'),
                'expression'=>array('MenuSetController','allowRead'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('SS03');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('SS03');
    }

    public static function allowRead() {
        return true;
    }
    public function actionIndex($pageNum=0){
        $model = new MenuSetList;
        if (isset($_POST['MenuSetList'])) {
            $model->attributes = $_POST['MenuSetList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['menuSet_01']) && !empty($session['menuSet_01'])) {
                $criteria = $session['menuSet_01'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
    }


    public function actionNew()
    {
        $model = new MenuSetForm('new');
        $this->render('form',array('model'=>$model,));
    }

    public function actionEdit($index)
    {
        $model = new MenuSetForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
    }

    public function actionView($index)
    {
        $model = new MenuSetForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
    }


    public function actionSave()
    {
        if (isset($_POST['MenuSetForm'])) {
            $model = new MenuSetForm($_POST['MenuSetForm']['scenario']);
            $model->attributes = $_POST['MenuSetForm'];
            if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                $this->redirect(Yii::app()->createUrl('menuSet/edit',array('index'=>$model->id)));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model,));
            }
        }
    }

    //刪除測驗單
    public function actionDelete(){
        $model = new MenuSetForm('delete');
        if (isset($_POST['MenuSetForm'])) {
            $model->attributes = $_POST['MenuSetForm'];
            if($model->validate()){
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
            }else{
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','This record is used by some user records'));
                $this->redirect(Yii::app()->createUrl('menuSet/edit',array('index'=>$model->id)));
            }
        }
        $this->redirect(Yii::app()->createUrl('menuSet/index'));
    }

    //所有城市列表
    public function getAllCityList(){
        $suffix = Yii::app()->params['envSuffix'];
        $arr = array(""=>"全部");
        $rows = Yii::app()->db->createCommand()->select()->from("security$suffix.sec_city")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $arr[$row["code"]] = $row["name"];
            }
        }
        return $arr;
    }

    //員工列表的異步請求
    public function actionAjaxStaff(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $city = $_POST['city'];
            $list =array("status"=>0);
            $arr = MenuSetForm::getAllStaffList($city);
            if(!empty($arr)){
                $list = array("status"=>1,"data"=>$arr);
            }
            echo CJSON::encode($list);
        }else{
            $this->redirect(Yii::app()->createUrl('menuSet/index'));
        }
    }

    //部門的異步請求
    public function actionAjaxDepartment(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $department = $_POST['department'];
            $arr = MenuSetForm::searchDepartment($department);
            echo CJSON::encode($arr);
        }else{
            echo "Error:404";
        }
        Yii::app()->end();
    }
}