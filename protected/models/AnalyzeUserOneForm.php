<?php

class AnalyzeUserOneForm extends CFormModel
{
    public $menu_id;
    public $menu_name;
    public $menu_code;
    public $code_pre="03";
	/* User Fields */
    public $search_start_date;//查詢開始日期
    public $search_end_date;//查詢結束日期
    public $search_type=3;//查詢類型 1：季度 2：月份 3：天
    public $search_year;//查詢年份
    public $search_month;//查詢月份
    public $search_quarter;//查詢季度
	public $start_date;
	public $end_date;
	public $day_num=0;

    public $data=array();

	public $th_sum=0;//所有th的个数

    public $downJsonText='';
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'start_date'=>Yii::t('freed','start date'),
            'end_date'=>Yii::t('freed','end date'),
            'day_num'=>Yii::t('freed','day num'),
            'search_type'=>Yii::t('freed','search type'),
            'search_start_date'=>Yii::t('freed','start date'),
            'search_end_date'=>Yii::t('freed','end date'),
            'search_year'=>Yii::t('freed','search year'),
            'search_quarter'=>Yii::t('freed','search quarter'),
            'search_month'=>Yii::t('freed','search month'),
		);
	}

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('menu_id,search_type,search_start_date,search_end_date,search_year,search_quarter,search_month','safe'),
            array('total_all,total_finish,total_unfinished','safe'),
            array('search_type','required'),
            array('search_type','validateDate'),
            array('menu_id','validateMenu'),
        );
    }

    public function validateMenu($attribute, $params) {
        if(!$this->retrieveMenuData($this->menu_id)){
            $this->addError($attribute, "数据异常，请刷新重试");
        }
    }

    public function validateDate($attribute, $params) {
        switch ($this->search_type){
            case 1://1：季度
                if(empty($this->search_year)||empty($this->search_quarter)){
                    $this->addError($attribute, "查询季度不能为空");
                }else{
                    $dateStr = $this->search_year."/".$this->search_quarter."/01";
                    $this->start_date = date("Y/m/01",strtotime($dateStr));
                    $this->end_date = date("Y/m/t",strtotime($dateStr." + 2 month"));
                }
                break;
            case 2://2：月份
                if(empty($this->search_year)||empty($this->search_month)){
                    $this->addError($attribute, "查询月份不能为空");
                }else{
                    $dateTimer = strtotime($this->search_year."/".$this->search_month."/01");
                    $this->start_date = date("Y/m/01",$dateTimer);
                    $this->end_date = date("Y/m/t",$dateTimer);
                }
                break;
            case 3://3：天
                if(empty($this->search_start_date)||empty($this->search_start_date)){
                    $this->addError($attribute, "查询日期不能为空");
                }else{
                    $this->start_date = $this->search_start_date;
                    $this->end_date = $this->search_end_date;
                }
                break;
        }
    }

    public function setCriteria($criteria)
    {
        if (count($criteria) > 0) {
            foreach ($criteria as $k=>$v) {
                $this->$k = $v;
            }
        }
    }

    public function getCriteria() {
        return array(
            'menu_id'=>$this->menu_id,
            'search_year'=>$this->search_year,
            'search_month'=>$this->search_month,
            'search_type'=>$this->search_type,
            'search_quarter'=>$this->search_quarter,
            'search_start_date'=>$this->search_start_date,
            'search_end_date'=>$this->search_end_date
        );
    }

    public function retrieveMenuData($menu_id){ //
        $menu = Yii::app()->db->createCommand()->select("*")
            ->from("fed_setting")
            ->where("id =:id",array(":id"=>$menu_id))->queryRow();
        if($menu){
            $this->menu_id = $menu_id;
            $this->menu_name = $menu["menu_name"];
            $this->menu_code = $menu["menu_code"].$this->code_pre;
            return true;
        }
        return false;
    }

    public function retrieveData() {
        $data = array();
        $defMoreList = $this->defMoreList();

        $projectInfoRows = $this->getProjectInfoRows();
        if($projectInfoRows){
            foreach ($projectInfoRows as $infoRow){
                $infoRowDetail = $this->getProjectDetail($infoRow);
                $temp = $defMoreList;
                $this->addTemp($temp,$infoRowDetail);

                $data[]=$temp;
            }
        }

        $this->data = $data;
        $session = Yii::app()->session;
        $session['analyzeUserOne_'.$this->menu_code] = $this->getCriteria();
        return true;
    }

    private function getProjectDetail($attr){
        $startDate = $this->start_date." 00:00:00";
        $endDate = $this->end_date." 23:59:59";
        $list = $attr;
        $username = $attr["username"];
        $list["dis_name"]=FunctionSearch::getUserDisplayName($username);
        $row = Yii::app()->db->createCommand()
            ->select("
            sum(if(a.lcu='{$username}',1,0)) as lcu_sum,
            sum(if(FIND_IN_SET('{$username}',a.assign_user),1,0)) as assign_user_sum
            ")
            ->from("fed_project a")
            ->where("a.menu_id=:id and a.status_type=1 and 
            (a.lcu='{$username}' or FIND_IN_SET('{$username}',a.assign_user)) and 
            a.lcd BETWEEN '{$startDate}' and '{$endDate}'",array(":id"=>$this->menu_id))
            ->group("a.menu_id")
            ->queryRow();
        if($row){
            foreach ($row as $key=>$item){
                $list[$key] = $item;
            }
        }
        //作为项目其它人
        $other_user_sum = Yii::app()->db->createCommand()
            ->select("a.project_id")
            ->from("fed_project_assign a")
            ->leftJoin("fed_project b","a.project_id=b.id")
            ->where("b.menu_id=:id and b.status_type=1 and a.username='{$username}' and 
            a.username!=b.lcu and !FIND_IN_SET(a.username,b.assign_user) and
            b.lcd BETWEEN '{$startDate}' and '{$endDate}'",array(":id"=>$this->menu_id))
            ->group("a.project_id")
            ->queryRow();
        $list["other_user_sum"] = $other_user_sum?count($other_user_sum):0;
        return $list;
    }

    private function addTemp(&$temp,$infoRow){
        foreach ($temp as $key=>$item){
            if(key_exists($key,$infoRow)){
                $temp[$key] = $infoRow[$key];
            }
        }
    }

    private function getProjectInfoRows(){
        $startDate = $this->start_date." 00:00:00";
        $endDate = $this->end_date." 23:59:59";
        $rows = Yii::app()->db->createCommand()
            ->select("a.username,
            sum(a.diff_timer) as project_len,
            count(a.username) as project_num,
            sum(if(a.username=b.lcu,a.diff_timer,0)) as lcu_len,
            sum(if(a.username=b.lcu,1,0)) as lcu_num,
            sum(if(FIND_IN_SET(a.username,b.assign_user),a.diff_timer,0)) as assign_user_len,
            sum(if(FIND_IN_SET(a.username,b.assign_user),1,0)) as assign_user_num,
            sum(if((a.username!=b.lcu and !FIND_IN_SET(a.username,b.assign_user)),a.diff_timer,0)) as other_user_len,
            sum(if((a.username!=b.lcu and !FIND_IN_SET(a.username,b.assign_user)),1,0)) as other_user_num 
            ")
            ->from("fed_project_assign a")
            ->leftJoin("fed_project b","a.project_id=b.id")
            ->where("b.menu_id=:id and b.status_type=1 and b.lcd BETWEEN '{$startDate}' and '{$endDate}'",array(":id"=>$this->menu_id))
            ->group("a.username")
            ->order("a.username desc")
            ->queryAll();
        return $rows;
    }

    //設置默認值
    private function defMoreList(){
        $arr=array(
            "username"=>0,//登录账号
            "dis_name"=>0,//账号昵称
            "project_sum"=>0,//项目总数量
            "project_num"=>0,//总跟进次数
            "project_len"=>0,//总跟进时长

            "lcu_sum"=>0,//建档人项目数量
            "lcu_num"=>0,//建档人跟进次数
            "lcu_len"=>0,//建档人跟进时长

            "assign_user_sum"=>0,//跟进人项目数量
            "assign_user_num"=>0,//跟进人跟进次数
            "assign_user_len"=>0,//跟进人跟进时长

            "other_user_sum"=>0,//其它人项目数量
            "other_user_num"=>0,//其它人跟进次数
            "other_user_len"=>0,//其它人跟进时长
        );
        return $arr;
    }

    protected function resetTdRow(&$list,$bool=false){
        $list["project_sum"] =$list["lcu_sum"];
        $list["project_sum"]+=$list["assign_user_sum"];
        $list["project_sum"]+=$list["other_user_sum"];
    }

    //顯示表格內容
    public function analyzeUserOneHtml(){
        $html= '<table id="analyzeUserOne" class="table table-fixed table-condensed table-bordered table-hover">';
        $html.=$this->tableTopHtml();
        $html.=$this->tableBodyHtml();
        $html.=$this->tableFooterHtml();
        $html.="</table>";
        return $html;
    }

    private function getTopArr(){
        $topList=array(
            array("name"=>Yii::t("freed","username"),"rowspan"=>2),//账号昵称
            array("name"=>Yii::t("freed","project total"),"rowspan"=>2),//项目总数量
            array("name"=>Yii::t("freed","assign total"),"rowspan"=>2),//总跟进次数
            array("name"=>Yii::t("freed","duration total"),"rowspan"=>2),//总跟进时长
            array("name"=>Yii::t("freed","apply user analyze"),"background"=>"#f7fd9d",
                "colspan"=>array(
                    array("name"=>Yii::t("freed","project number")),//项目数量
                    array("name"=>Yii::t("freed","assign number")),//跟进次数
                    array("name"=>Yii::t("freed","assign duration")),//跟进时长
                )
            ),//建档人分析
            array("name"=>Yii::t("freed","assign user analyze"),"background"=>"#C5D9F1",
                "colspan"=>array(
                    array("name"=>Yii::t("freed","project number")),//项目数量
                    array("name"=>Yii::t("freed","assign number")),//跟进次数
                    array("name"=>Yii::t("freed","assign duration")),//跟进时长
                )
            ),//跟进人分析
            array("name"=>Yii::t("freed","other user analyze"),"background"=>"#D9D9D9",
                "colspan"=>array(
                    array("name"=>Yii::t("freed","project number")),//项目数量
                    array("name"=>Yii::t("freed","assign number")),//跟进次数
                    array("name"=>Yii::t("freed","assign duration")),//跟进时长
                )
            ),//其它人分析
        );

        return $topList;
    }

    //顯示提成表的表格內容（表頭）
    protected function tableTopHtml(){
        $this->th_sum = 0;
        $topList = self::getTopArr();
        $trOne="";
        $trTwo="";
        $html="<thead>";
        foreach ($topList as $list){
            $clickName=$list["name"];
            $colList=key_exists("colspan",$list)?$list['colspan']:array();
            $style = "";
            $colNum=0;
            if(key_exists("background",$list)){
                $style.="background:{$list["background"]};";
            }
            if(key_exists("color",$list)){
                $style.="color:{$list["color"]};";
            }
            if(!empty($colList)){
                foreach ($colList as $col){
                    $colNum++;
                    $trTwo.="<th style='{$style}'><span>".$col["name"]."</span></th>";
                    $this->th_sum++;
                }
            }else{
                $this->th_sum++;
            }
            $colNum = empty($colNum)?1:$colNum;
            $trOne.="<th style='{$style}' colspan='{$colNum}'";
            if($colNum>1){
                $trOne.=" class='click-th'";
            }
            if(key_exists("rowspan",$list)){
                $trOne.=" rowspan='{$list["rowspan"]}'";
            }
            if(key_exists("startKey",$list)){
                $trOne.=" data-key='{$list['startKey']}'";
            }
            $trOne.=" ><span>".$clickName."</span></th>";
        }
        $html.=$this->tableHeaderWidth();//設置表格的單元格寬度
        $html.="<tr>{$trOne}</tr><tr>{$trTwo}</tr>";
        $html.="</thead>";
        return $html;
    }

    //設置表格的單元格寬度
    private function tableHeaderWidth(){
        $html="<tr>";
        for($i=0;$i<$this->th_sum;$i++){
            $width=90;
            $html.="<th class='header-width' data-width='{$width}' width='{$width}px'>{$i}</th>";
        }
        return $html."</tr>";
    }

    public function tableBodyHtml(){
        $html="";
        if(!empty($this->data)){
            $this->downJsonText=array();
            $html.="<tbody>";
            $html.=$this->showServiceHtml($this->data);
            $html.="</tbody>";
            $this->downJsonText=json_encode($this->downJsonText);
            $html.=TbHtml::hiddenField("excel",$this->downJsonText);
        }
        return $html;
    }

    //获取td对应的键名
    private function getDataAllKeyStr(){
        $bodyKey = array(
            "dis_name","project_sum","project_num","project_len",
            "lcu_sum","lcu_num","lcu_len",
            "assign_user_sum","assign_user_num","assign_user_len",
            "other_user_sum","other_user_num","other_user_len",
        );
        return $bodyKey;
    }

    //設置百分比顏色
    private function getTextColorForKeyStr($text,$keyStr){
        $tdClass = "";
        return $tdClass;
    }

    //將数据寫入表格
    private function showServiceHtml($data){
        $bodyKey = $this->getDataAllKeyStr();
        $html="";
        if(!empty($data)){
            foreach ($data as $forKey=>$row){
                $this->resetTdRow($row);
                $html.="<tr>";
                foreach ($bodyKey as $keyStr){
                    $text = key_exists($keyStr,$row)?$row[$keyStr]:"0";
                    $tdClass = self::getTextColorForKeyStr($text,$keyStr);
                    $exprData = self::tdClick($tdClass,$keyStr,$row["username"]);//点击后弹窗详细内容

                    $text = AnalyzeProOneForm::showNum($text,$keyStr);
                    $this->downJsonText["excel"][$forKey][$row['username']][$keyStr]=$text;
                    $html.="<td class='{$tdClass}' {$exprData}><span>{$text}</span></td>";
                }
                $html.="</tr>";
            }
            $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
            $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
        }
        return $html;
    }
    public function tableFooterHtml(){
        $html="<tfoot>";
        if(!empty($this->data)){
            $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
        }else{
            $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'><h4>查询时间段内没有跟进项目</h4></td></tr>";
        }
        $html.="</tfoot>";
        return $html;
    }

    //下載
    public function downExcel($excelData){
        if(!is_array($excelData)){
            $excelData = json_decode($excelData,true);
            $excelData = key_exists("excel",$excelData)?$excelData["excel"]:array();
        }
        $this->validateDate("","");
        $headList = $this->getTopArr();
        $excel = new DownSummary();
        $excel->SetHeaderTitle(Yii::t("app","Username analyze"));
        $excel->SetHeaderString($this->start_date." ~ ".$this->end_date);
        $excel->init();
        $excel->colTwo = 4;
        $excel->setSummaryHeader($headList);
        $excel->setAttrDetailData($excelData);
        $excel->outExcel(Yii::t("app","Username analyze"));
    }

    protected function clickList(){
        return array(
            "new_month_n"=>array("title"=>Yii::t("freed","Last Month Single + New(INV)"),"type"=>"ServiceINVMonthNew"),
        );
    }

    private function tdClick(&$tdClass,$keyStr,$username){
        $expr = " data-user='{$username}'";
        $list = $this->clickList();
        if(key_exists($keyStr,$list)){
            $tdClass.=" td_detail";
            $expr.= " data-type='{$list[$keyStr]['type']}'";
            $expr.= " data-title='{$list[$keyStr]['title']}'";
        }

        return $expr;
    }
}