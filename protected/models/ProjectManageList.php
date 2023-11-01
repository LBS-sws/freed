<?php

class ProjectManageList extends CListPageModel
{
    public $menu_id;
    public $menu_name;
    public $menu_code;
    public $code_pre="01";

    //
    public $lcu="";
    public $assign_user="";
    public $plan_min="";
    public $plan_max="";
    public $assign_plan=100;
    public $project_type="";
    public $project_code="";
    public $project_name="";
    public $lcd_start="";
    public $lcd_end="";
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
            'project_status'=>Yii::t('freed','project status'),
            'assign_plan'=>Yii::t('freed','assign plan'),
            'lcu'=>Yii::t('freed','File builder'),
            'lcd'=>Yii::t('freed','File date'),
            'assign_user'=>Yii::t('freed','assign user'),
		);
	}

    public function rules()
    {
        return array(
            array('attr, menu_id, pageNum, noOfItem, totalRow, searchField, searchValue, orderField, orderType','safe',),
            array('lcu,assign_user,plan_max,plan_min,project_type,project_code,project_name,lcd_start,lcd_end','safe',),
        );
    }

    public function retrieveAll($menu_id,$pageNum=1){
        $menu = Yii::app()->db->createCommand()->select("menu_name,menu_code")
            ->from("fed_setting")
            ->where("id =:id",array(":id"=>$menu_id))->queryRow();
        if($menu){
            $this->menu_id = $menu_id;
            $this->menu_name = $menu["menu_name"];
            $this->menu_code = $menu["menu_code"].$this->code_pre;
            $this->retrieveDataByPage($pageNum);
            return true;
        }
        return false;
    }

	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select a.*,b.disp_name as lcu_name, f.disp_name as assign_user_name
                from fed_project a 
                LEFT JOIN security{$suffix}.sec_user b ON a.lcu=b.username
                LEFT JOIN security{$suffix}.sec_user f ON a.assign_user=f.username
                where a.menu_id={$this->menu_id}
			";
        $sql2 = "select count(a.id) 
                from fed_project a 
                LEFT JOIN security{$suffix}.sec_user b ON a.lcu=b.username
                LEFT JOIN security{$suffix}.sec_user f ON a.assign_user=f.username
                where a.menu_id={$this->menu_id}
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'project_code':
					$clause .= General::getSqlConditionClause('a.project_code',$svalue);
					break;
				case 'project_name':
					$clause .= General::getSqlConditionClause('a.project_name',$svalue);
					break;
			}
		}
		$clause.=$this->searchItemAll();
		
		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by id desc ";
        }

        $sql = $sql2.$clause.$order;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();

		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();

		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
                $detailCount = Yii::app()->db->createCommand()->select("count(id)")
                    ->from("fed_project_assign")
                    ->where('project_id=:id',array(':id'=>$record['id']))->queryScalar();
				$this->attr[] = array(
					'menu_code'=>$this->menu_code,
					'id'=>$record['id'],
					'project_code'=>$record['project_code'],
					'project_name'=>$record['project_name'],
					'project_type'=>FunctionList::getProjectTypeStr($record['project_type']),
					'project_status'=>FunctionList::getProjectStatusStr($record['project_status']),
					'assign_plan'=>FunctionList::getAssignPlanStr($record['assign_plan']),
					'lcu'=>$record['lcu_name'],
                    'lcd'=>date("Y/m/d",strtotime($record['lcd'])),
					'assign_user'=>$record['assign_user_name'],
					'detailCount'=>$detailCount,
					'color'=>FunctionList::getTableColor($record),
				);
			}
		}
		$session = Yii::app()->session;
        $session['projectManage_'.$this->menu_code] = $this->getCriteria();
		return true;
	}

	public function getProjectInfoHtmlTr($project_id){
        $suffix = Yii::app()->params['envSuffix'];
        $html="";
        $rows = Yii::app()->db->createCommand()->select("a.*,b.disp_name")
            ->from("fed_project_assign a")
            ->leftJoin("security{$suffix}.sec_user b","a.username=b.username")
            ->where('a.project_id=:id',array(':id'=>$project_id))->order("a.lcd desc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $html.= "<tr class='detail_$project_id'>";
                $html.= "<td colspan=2>";
                $html.= "<strong>跟进账号：</strong>";
                $html.=$row["disp_name"];
                $html.= "<br><strong>跟进进度：</strong>";
                $html.=FunctionList::getAssignPlanStr($row["assign_plan"]);
                $html.="</td>";
                $html.= "<td colspan=2><strong>跟进时间：</strong>";
                $html.=$row["lcd"];
                $html.="</td>";
                $html.= "<td colspan=5><strong>跟进内容：</strong>";
                $html.=$row["assign_text"];
                $html.="</td>";
                $html.= "</tr>";
            }
        }
        /*
        $html.= <<<EOF
<tr class='detail_$id'>
	<td colspan=2></td>
	<td><strong>$lbl_status_dt:&nbsp;</strong>$fld_status_dt</td>
	<td><strong>$lbl_status:&nbsp;</strong>$fld_status</td>
	<td><strong>$lbl_cust_type_desc:&nbsp;</strong>$fld_cust_type_desc</td>
	<td><strong>$lbl_product_desc:&nbsp;</strong>$fld_product_desc</td>
	<td><strong>$lbl_first_dt:&nbsp;</strong>$fld_first_dt</td>
	<td><strong>$lbl_amt_paid:&nbsp;</strong>$fld_amt_paid $fld_paid_type</td>
</tr>
EOF;
        */
        return $html;
    }

	private function searchItemAll(){
        //assign_user,assign_eq,assign_plan,project_type,project_code,project_name,lcd_start,lcd_end
        $arr = array(
            "lcd_start"=>" and DATE_FORMAT(a.lcd,'%Y/%m/%d')>='{item}'",
            "lcd_end"=>" and DATE_FORMAT(a.lcd,'%Y/%m/%d')<='{item}'",
            "project_name"=>" and a.project_name like '%{item}%'",
            "project_code"=>" and a.project_code like'%{item}%' ",
            "project_type"=>" and a.project_type = '{item}'",
            "assign_user"=>" and a.assign_user = '{item}'",
            "plan_min"=>" and a.assign_plan >= '{item}'",
            "plan_max"=>" and a.assign_plan <= '{item}'",
            "lcu"=>" and a.lcu = '{item}'",
        );
        $sql = "";
        foreach ($arr as $key=>$item){
            if ($this->$key!=="") {
                $svalue = str_replace("'","\'",$this->$key);
                $sql.= str_replace('{item}',$svalue,$item);
            }
        }
        return $sql;
    }

    public function getCriteria() {
        return array(
            'lcu'=>$this->lcu,
            'assign_user'=>$this->assign_user,
            'plan_min'=>$this->plan_min,
            'plan_max'=>$this->plan_max,
            'project_type'=>$this->project_type,
            'project_code'=>$this->project_code,
            'project_name'=>$this->project_name,
            'lcd_start'=>$this->lcd_start,
            'lcd_end'=>$this->lcd_end,
            'searchField'=>$this->searchField,
            'searchValue'=>$this->searchValue,
            'orderField'=>$this->orderField,
            'orderType'=>$this->orderType,
            'noOfItem'=>$this->noOfItem,
            'pageNum'=>$this->pageNum,
            'filter'=>$this->filter,
            'city'=>$this->city,
            'dateRangeValue'=>$this->dateRangeValue,
        );
    }
}
