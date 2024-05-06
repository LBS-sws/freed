<?php

/**
 * Created by PhpStorm.
 * User: 沈超
 * Date: 2017/6/7 0007
 * Time: 上午 11:30
 */
class StatisticProAllController extends Controller
{
	public $function_id='SA01';

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
                'actions'=>array(),
                'expression'=>array('StatisticProAllController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view','downExcel'),
                'expression'=>array('StatisticProAllController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('SA01');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('SA01');
    }

    public function actionDownExcel()
    {
        $model = new StatisticProAllForm('view');
        if (isset($_POST['StatisticProAllForm'])) {
            $model->attributes = $_POST['StatisticProAllForm'];
            $excelData = key_exists("excel",$_POST)?$_POST["excel"]:array();
            $model->downExcel($excelData);
        }else{
            $model->setScenario("index");
            $this->render('index',array('model'=>$model));
        }
    }

    public function actionIndex()
    {
        $model = new StatisticProAllForm('index');
        $session = Yii::app()->session;
        if (isset($session['statisticProAll_01']) && !empty($session['statisticProAll_01'])) {
            $criteria = $session['statisticProAll_01'];
            $model->setCriteria($criteria);
        }else{
            $model->search_year = date("Y");
            $model->search_month = date("n");
            $model->search_start_date = date("Y/m/01");
            $model->search_end_date = date("Y/m/d");
            $i = ceil($model->search_month/3);//向上取整
            $model->search_quarter = 3*$i-2;
        }
        $this->render('index',array('model'=>$model));
    }

    public function actionView()
    {
        set_time_limit(0);
        $model = new StatisticProAllForm('view');
        if (isset($_POST['StatisticProAllForm'])) {
            $model->attributes = $_POST['StatisticProAllForm'];
        }else{
            $session = Yii::app()->session;
            if (isset($session['statisticProAll_01']) && !empty($session['statisticProAll_01'])) {
                $criteria = $session['statisticProAll_01'];
                $model->setCriteria($criteria);
            }
        }
        if ($model->validate()) {
            $model->retrieveData();
            $this->render('form',array('model'=>$model));
        } else {
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
            $this->render('index',array('model'=>$model));
        }
    }
}