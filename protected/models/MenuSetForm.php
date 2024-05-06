<?php

/**
 * UserForm class.
 * UserForm is the data structure for keeping
 * user form data. It is used by the 'user' action of 'SiteController'.
 */
class MenuSetForm extends CFormModel
{
	/* User Fields */
	public $id = 0;
	public $menu_name;
	public $menu_code;
	public $user_str;

	public $display=1;
	public $z_index=0;

	private $join_user=array();
    public $detail = array(
        array('id'=>0,
            'set_id'=>0,
            'username'=>'',
            'email_type'=>1,
            'uflag'=>'N',
        ),
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
            'menu_code'=>Yii::t('freed','menu code'),
            'user_str'=>Yii::t('freed','join user'),
            'menu_name'=>Yii::t('freed','menu name'),
            'display'=>Yii::t('freed','display'),
            'z_index'=>Yii::t('freed','z_index'),

            'username'=>Yii::t('freed','join user'),
            'email_type'=>Yii::t('freed','email on-off'),
        );
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			//array('id, position, leave_reason, remarks, email, staff_type, leader','safe'),
            array('id, menu_code, menu_name, z_index, display,detail','safe'),
			array('menu_code,menu_name','required'),
			array('menu_name','validateName'),
			array('menu_code','validateCode'),
			array('detail','validateDetail'),
			array('menu_code','validateDel','on'=>array("delete")),
            array('z_index', 'numerical', 'integerOnly'=>true),
		);
	}

	public function validateDetail($attribute, $params){
	    $this->user_str="";
	    $this->join_user=array();
	    $disName=array();
	    $list=array();
	    $userList = FunctionSearch::getMenuUserList();
	    $emailTypeList = FunctionList::getEmailTypeList();
	    if(!empty($this->detail)){
	        foreach ($this->detail as $row){
                $temp = $row;
                if($row["uflag"]!="D"){
                    if(empty($row["username"])){
                        continue;
                    }
                    if(!key_exists($row["username"],$userList)){
                        $message = "参加账号不存在，请重试:".$row["username"];
                        $this->addError($attribute,$message);
                        return false;
                    }
                    if(!key_exists($row["email_type"],$emailTypeList)){
                        $message = "邮箱类型异常，请重试:".$row["username"];
                        $this->addError($attribute,$message);
                        return false;
                    }
                    if(in_array($row["username"],$this->join_user)){
                        continue;//如果存在，跳过
                    }
                    $disName[]=$userList[$row["username"]];
                    $this->join_user[]=$row["username"];
                }
                $list[]=$temp;
            }
        }
        if(empty($list)){
            $message = "参加账号不能为空";
            $this->addError($attribute,$message);
        }else{
            $this->user_str = implode(",",$disName);
            $this->detail = $list;
        }
    }

	public function validateName($attribute, $params){
        $id = -1;
        if(!empty($this->id)){
            $id = $this->id;
        }
        $row = Yii::app()->db->createCommand()->select("id")->from("fed_setting")
            ->where('menu_name=:menu_name and id!=:id',
                array(':menu_name'=>$this->menu_name,':id'=>$id))->queryRow();
        if($row){
            $message = "菜单名称已存在，请重新命名";
            $this->addError($attribute,$message);
        }
    }
	public function validateCode($attribute, $params){
        $id = -1;
        if(!empty($this->id)){
            $id = $this->id;
        }
        if(strlen($this->menu_code)>=5){
            $message = "编号长度不能大于4";
            $this->addError($attribute,$message);
        }
        $row = Yii::app()->db->createCommand()->select("id")->from("fed_setting")
            ->where('menu_code=:menu_code and id!=:id',
                array(':menu_code'=>$this->menu_code,':id'=>$id))->queryRow();
        if($row||in_array($this->menu_code,array("TP","SC","SS","EM","SA"))){
            $message = "菜单编号已存在，请重新命名";
            $this->addError($attribute,$message);
        }
    }


    //刪除验证
	public function validateDel($attribute, $params){
        $row = Yii::app()->db->createCommand()->select("project_code")->from("fed_project")
            ->where('menu_id=:id', array(':id'=>$this->id))->queryRow();
        if ($row){
            $message = "该菜单项目已有跟进，无法删除。({$row["project_code"]})";
            $this->addError($attribute,$message);
            return false;
        }
        return true;
    }

	public function retrieveData($index)
	{
        $city = Yii::app()->user->city();
        $city_allow = Yii::app()->user->city_allow();
        $row = Yii::app()->db->createCommand()->select()->from("fed_setting")
            ->where("id=:id", array(':id'=>$index))->queryRow();
		if ($row)
		{
            $this->id = $row['id'];
            $this->menu_code = $row['menu_code'];
            $this->menu_name = $row['menu_name'];
            $this->user_str = $row['user_str'];
            $this->display = $row['display'];
            $this->z_index = $row['z_index'];
            $sql = "select * from fed_setting_info where set_id=".$index." ";
            $infoRows = Yii::app()->db->createCommand($sql)->queryAll();
            if($infoRows){
                $this->detail=array();
                foreach ($infoRows as $infoRow){
                    $temp = array();
                    $temp["id"] = $infoRow["id"];
                    $temp["set_id"] = $infoRow["set_id"];
                    $temp["username"] = $infoRow["username"];
                    $temp["email_type"] = $infoRow["email_type"];
                    $temp['uflag'] = 'N';
                    $this->detail[] = $temp;
                }
            }
		}
		return true;
	}
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveStaff($connection);
            $this->saveDetail($connection);
            $this->updateAccess($connection);//修改权限
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			var_dump($e);
			throw new CHttpException(404,'Cannot update.');
		}
	}

    protected function updateAccess(&$connection)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $systemId = Yii::app()->params['systemId'];
        $updateUsername=array();
        if(in_array($this->getScenario(),array("new","edit"))){
            $access=$this->menu_code."01";
            foreach ($this->join_user as $username){
                $row = Yii::app()->db->createCommand()->select("a.username")
                    ->from("security{$suffix}.sec_user_access a")
                    ->where("a.username='{$username}' and a.system_id='{$systemId}' and (a.a_read_only like '%{$access}%' or a.a_read_write like '%{$access}%')")
                    ->queryRow();
                if(!$row){
                    $updateUsername[]=$username;
                }
            }

            if(!empty($updateUsername)){
                $updateUsername = implode("','",$updateUsername);
                $connection->createCommand("update security{$suffix}.sec_user_access set a_read_write=CONCAT(a_read_write,'{$access}') WHERE system_id='{$systemId}' and username in ('{$updateUsername}')")->execute();
            }
        }

        if ($this->scenario=='new'){
            $this->setScenario("edit");
        }
    }

    protected function saveDetail(&$connection)
    {
        $uid = Yii::app()->user->id;
        if(isset($this->detail)){
            foreach ($this->detail as $row) {
                $sql = '';
                switch ($this->scenario) {
                    case 'delete':
                        $sql = "delete from fed_setting_info where set_id = :set_id";
                        break;
                    case 'new':
                        if ($row['uflag']=='Y') {
                            $sql = "insert into fed_setting_info(
									set_id, username, email_type,lcu
								) values (
									:set_id,:username,:email_type,:lcu
								)";
                        }
                        break;
                    case 'edit':
                        switch ($row['uflag']) {
                            case 'D':
                                $sql = "delete from fed_setting_info where id = :id";
                                break;
                            case 'Y':
                                $sql = ($row['id']==0)
                                    ?
                                    "insert into fed_setting_info(
									  set_id, username, email_type,lcu
									) values (
									  :set_id,:username,:email_type,:lcu
									)"
                                    :
                                    "update fed_setting_info set
										username = :username, 
										email_type = :email_type,
										luu = :luu 
									where id = :id
									";
                                break;
                        }
                        break;
                }

                if ($sql != '') {
//                print_r('<pre>');
//                print_r($sql);exit();
                    $command=$connection->createCommand($sql);
                    if (strpos($sql,':id')!==false)
                        $command->bindParam(':id',$row['id'],PDO::PARAM_INT);
                    if (strpos($sql,':set_id')!==false)
                        $command->bindParam(':set_id',$this->id,PDO::PARAM_INT);
                    if (strpos($sql,':username')!==false)
                        $command->bindParam(':username',$row['username'],PDO::PARAM_STR);
                    if (strpos($sql,':email_type')!==false)
                        $command->bindParam(':email_type',$row['email_type'],PDO::PARAM_INT);
                    if (strpos($sql,':luu')!==false)
                        $command->bindParam(':luu',$uid,PDO::PARAM_STR);
                    if (strpos($sql,':lcu')!==false)
                        $command->bindParam(':lcu',$uid,PDO::PARAM_STR);
                    $command->execute();
                }
            }
        }
        return true;
    }

	protected function saveStaff(&$connection)
	{
		$sql = '';
        $city = Yii::app()->user->city();
        $city_allow = Yii::app()->user->city_allow();
        $uid = Yii::app()->user->id;
		switch ($this->scenario) {
			case 'delete':
                $sql = "delete from fed_setting where id = :id ";
				break;
			case 'new':
				$sql = "insert into fed_setting(
							menu_code,menu_name,user_str, display, z_index, lcu
						) values (
							:menu_code,:menu_name,:user_str, :display, :z_index, :lcu
						)";
				break;
			case 'edit':
				$sql = "update fed_setting set
							menu_code = :menu_code, 
							menu_name = :menu_name, 
							user_str = :user_str, 
							display = :display, 
							z_index = :z_index,  
							luu = :luu
						where id = :id
						";
				break;
		}

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':menu_name')!==false)
			$command->bindParam(':menu_name',$this->menu_name,PDO::PARAM_STR);
		if (strpos($sql,':menu_code')!==false)
			$command->bindParam(':menu_code',$this->menu_code,PDO::PARAM_STR);
		if (strpos($sql,':user_str')!==false)
			$command->bindParam(':user_str',$this->user_str,PDO::PARAM_STR);
		if (strpos($sql,':display')!==false)
			$command->bindParam(':display',$this->display,PDO::PARAM_INT);
		if (strpos($sql,':z_index')!==false)
			$command->bindParam(':z_index',$this->z_index,PDO::PARAM_INT);

		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);

		$command->execute();

        if ($this->scenario=='new'){
            $this->id = Yii::app()->db->getLastInsertID();
        }

        $this->saveMenuFile();
        return true;
	}

	private function menuDetailList(){
	    return array(
            array("itemName"=>"Project manage","action"=>"projectManage","num"=>"01"),//项目管理
            array("itemName"=>"Project analyze","action"=>"analyzeProOne","num"=>"02"),//项目分析
            array("itemName"=>"Username analyze","action"=>"analyzeUserOne","num"=>"03"),//员工分析
        );
    }

	private function saveMenuFile(){
	    $list = array();
	    $arr = $this->menuDetailList();
        $rows = Yii::app()->db->createCommand()->select("id,menu_name,menu_code")->from("fed_setting")
            ->where("display=1")->order("z_index asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $items=array();
                foreach ($arr as $item){
                    $items[$item["itemName"]]=array(
                        "access"=>$row['menu_code'].$item['num'],
                        "url"=>"/{$item['action']}/index?index={$row['id']}&menu_code={$row['menu_code']}"
                    );
                }
                $list[$row["menu_name"]]=array(
                    'access'=>$row['menu_code'],
                    'icon'=>'fa-bookmark',
                    'items'=>$items
                );
            }
        }
        $file=Yii::app()->basePath.'/config/menuExtra.php';
        $menuitems=array();
        if (file_exists($file)){
            $menuitems = require($file);
        }
        $file=Yii::app()->basePath.'/config/menu.php';
        $list = array_merge($list,$menuitems);
        $text='<?php return '.var_export($list,true).';';
        if(false!==fopen($file,'w+')){
            file_put_contents($file,$text);
        }else{
            var_dump("文件不存在");die();
        }
    }
}
