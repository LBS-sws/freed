<?php

class AnalyzeUserOneController extends Controller
{
	public $function_id='G05';

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
        $this->function_id = "{$code}03";
        parent::init();
    }
	
	public function filters()
	{
		return array(
			'enforceRegisteredStation',
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
				'actions'=>array(''),
				'expression'=>array('AnalyzeUserOneController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','downExcel'),
				'expression'=>array('AnalyzeUserOneController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionDownExcel()
    {
        $model = new AnalyzeUserOneForm('view');
        if (isset($_POST['AnalyzeUserOneForm'])) {
            $model->attributes = $_POST['AnalyzeUserOneForm'];
            $excelData = key_exists("excel",$_POST)?$_POST["excel"]:array();
            $model->downExcel($excelData);
        }else{
            $model->setScenario("index");
            $this->render('index',array('model'=>$model));
        }
    }

	public function actionIndex($index)
	{
		$model = new AnalyzeUserOneForm('index');
        if($model->retrieveMenuData($index)&&$this->validateMenuCode($model->menu_code)){
            $session = Yii::app()->session;
            if (isset($session['analyzeUserOne_'.$this->function_id]) && !empty($session['analyzeUserOne_'.$this->function_id])) {
                $criteria = $session['analyzeUserOne_'.$this->function_id];
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
        }else {
            throw new CHttpException(404,'The requested page does not exist.');
        }
	}

	public function actionView()
	{
	    set_time_limit(0);
        $model = new AnalyzeUserOneForm('view');
        if (isset($_POST['AnalyzeUserOneForm'])) {
            $model->attributes = $_POST['AnalyzeUserOneForm'];
        }else{
            $session = Yii::app()->session;
            if (isset($session['analyzeUserOne_'.$this->function_id]) && !empty($session['analyzeUserOne_'.$this->function_id])) {
                $criteria = $session['analyzeUserOne_'.$this->function_id];
                $model->setCriteria($criteria);
            }
        }
        if ($model->validate()&&$this->validateMenuCode($model->menu_code)) {
            $model->retrieveData();
            $this->render('form',array('model'=>$model));
        } else {
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
            $this->render('index',array('model'=>$model));
        }
	}

    public static function allowReadWrite() {
        $session = Yii::app()->session;
        $code = isset($session['menu_code'])?$session['menu_code']:"dd";
        return Yii::app()->user->validRWFunction("{$code}03");
    }

    public static function allowReadOnly() {
        $session = Yii::app()->session;
        $code = isset($session['menu_code'])?$session['menu_code']:"dd";
        return Yii::app()->user->validFunction("{$code}03");
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
}
