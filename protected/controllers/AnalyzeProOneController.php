<?php

class AnalyzeProOneController extends Controller
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
        $this->function_id = "{$code}02";
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
				'expression'=>array('AnalyzeProOneController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','downExcel'),
				'expression'=>array('AnalyzeProOneController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionDownExcel()
    {
        $model = new AnalyzeProOneForm('view');
        if (isset($_POST['AnalyzeProOneForm'])) {
            $model->attributes = $_POST['AnalyzeProOneForm'];
            $excelData = key_exists("excel",$_POST)?$_POST["excel"]:array();
            $model->downExcel($excelData);
        }else{
            $model->setScenario("index");
            $this->render('index',array('model'=>$model));
        }
    }

	public function actionIndex($index)
	{
		$model = new AnalyzeProOneForm('index');
        if($model->retrieveMenuData($index)&&$this->validateMenuCode($model->menu_code)){
            $session = Yii::app()->session;
            if (isset($session['analyzeProOne_'.$this->function_id]) && !empty($session['analyzeProOne_'.$this->function_id])) {
                $criteria = $session['analyzeProOne_'.$this->function_id];
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
        $model = new AnalyzeProOneForm('view');
        if (isset($_POST['AnalyzeProOneForm'])) {
            $model->attributes = $_POST['AnalyzeProOneForm'];
        }else{
            $session = Yii::app()->session;
            if (isset($session['analyzeProOne_'.$this->function_id]) && !empty($session['analyzeProOne_'.$this->function_id])) {
                $criteria = $session['analyzeProOne_'.$this->function_id];
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
        return Yii::app()->user->validRWFunction("{$code}02");
    }

    public static function allowReadOnly() {
        $session = Yii::app()->session;
        $code = isset($session['menu_code'])?$session['menu_code']:"dd";
        return Yii::app()->user->validFunction("{$code}02");
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
