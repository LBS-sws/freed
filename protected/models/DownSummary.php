<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/3/14 0014
 * Time: 11:57
 */
class DownSummary{

    protected $objPHPExcel;

    protected $current_row = 0;
    protected $header_title;
    protected $header_string;
    protected $sheet_id=0;
    public $colTwo=2;
    public $th_num=0;

    public function SetHeaderTitle($invalue) {
        $this->header_title = $invalue;
    }

    public function SetHeaderString($invalue) {
        $this->header_string = $invalue;
    }

    public function init() {
        //Yii::$enableIncludePath = false;
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        spl_autoload_unregister(array('YiiBase','autoload'));
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $this->objPHPExcel = new PHPExcel();
        $this->setReportFormat();
        $this->outHeader();
    }

    public function setSummaryHeader($headerArr,$bool=false){
        if(!empty($headerArr)){
            for ($i=0;$i<$this->colTwo;$i++){
                $startStr = $this->getColumn($i);
                $this->objPHPExcel->getActiveSheet()->mergeCells($startStr.$this->current_row.':'.$startStr.($this->current_row+1));
            }
            $colOne = 0;
            $colTwo = $this->colTwo;
            foreach ($headerArr as $list){
                $startStr = $this->getColumn($colOne);
                $colspan = key_exists("colspan",$list)?count($list["colspan"])-1:0;
                $this->objPHPExcel->getActiveSheet()
                    ->setCellValueByColumnAndRow($colOne, $this->current_row, $list["name"]);
                $colOne+=$colspan;
                $colOne++;
                $endStr = $this->getColumn($colOne-1);
                if(!empty($colspan)){
                    $this->objPHPExcel->getActiveSheet()->mergeCells($startStr.$this->current_row.':'.$endStr.$this->current_row);
                }
                if(key_exists("background",$list)){
                    $background = $list["background"];
                    $background = end(explode("#",$background));
                    $endRow = $bool?$this->current_row+1:$this->current_row;
                    $this->setHeaderStyleTwo("{$startStr}{$this->current_row}:{$endStr}{$endRow}",$background);
                }
                if(isset($list["colspan"])){
                    foreach ($list["colspan"] as $item){
                        $this->objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($colTwo, $this->current_row+1, $item["name"]);
                        $colTwo++;
                        $this->th_num++;
                    }
                }else{
                    $this->th_num++;
                }
            }
            $endStr = $this->getColumn($this->th_num-1);
            $this->objPHPExcel->getActiveSheet()->getStyle("A{$this->current_row}:{$endStr}".($this->current_row+1))->applyFromArray(
                array(
                    'font'=>array(
                        'bold'=>true,
                        'color'=>array('rgb'=>$bool?'ffffff':'000000')
                    ),
                    'alignment'=>array(
                        'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    ),
                    'borders'=>array(
                        'allborders'=>array(
                            'style'=>PHPExcel_Style_Border::BORDER_THIN,
                        ),
                    )
                )
            );

            $this->current_row+=2;
        }
        $this->setSummaryWidth();
    }

    public function setUServiceHeader($headerArr){
        if(!empty($headerArr)){
            $endStr = $this->getColumn(count($headerArr)-1);
            $this->objPHPExcel->getActiveSheet()->getStyle("A{$this->current_row}:{$endStr}".($this->current_row))->applyFromArray(
                array(
                    'font'=>array(
                        'bold'=>true,
                    ),
                    'alignment'=>array(
                        'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    ),
                    'borders'=>array(
                        'allborders'=>array(
                            'style'=>PHPExcel_Style_Border::BORDER_THIN,
                        ),
                    )
                )
            );
            $colOne = 0;
            foreach ($headerArr as $list){
                $startStr = $this->getColumn($colOne);
                $this->objPHPExcel->getActiveSheet()
                    ->setCellValueByColumnAndRow($colOne, $this->current_row, $list["name"]);

                if(key_exists("background",$list)){
                    $background = $list["background"];
                    $background = end(explode("#",$background));
                    $this->setHeaderStyleTwo("{$startStr}{$this->current_row}",$background);
                }
                $colOne++;
                $this->th_num++;
            }
            $this->current_row++;
        }
        $this->setSummaryWidth();
    }

    private function setSummaryWidth(){
        for ($col=0;$col<$this->th_num;$col++){
            $width = 13;
            $this->objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setWidth($width);
        }
    }

    public function setAttrDetailData($data){
        if(!empty($data)){
            $endStr = $this->getColumn($this->th_num-1);
            foreach ($data as $region){
                foreach ($region as $keyStr=>$list){
                    $col = 0;
                    foreach ($list as $item){
                        $this->objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($col, $this->current_row, $item);
                        $col++;
                    }
                    if($keyStr=='count'){//汇总
                        $this->objPHPExcel->getActiveSheet()
                            ->getStyle("A{$this->current_row}:{$endStr}{$this->current_row}")
                            ->applyFromArray(
                                array(
                                    'font'=>array(
                                        'bold'=>true,
                                        'color'=>array('rgb'=>strpos($keyStr,'average_')!==false?'FF0000':'000000')
                                    ),
                                    'borders' => array(
                                        'top' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN
                                        )
                                    )
                                )
                            );
                        $this->current_row++;
                    }
                    $this->current_row++;
                }
            }
        }
    }

    protected function setReportFormat() {
        $this->objPHPExcel->getDefaultStyle()->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $this->objPHPExcel->getDefaultStyle()->getFont()
            ->setSize(10);
        $this->objPHPExcel->getDefaultStyle()->getAlignment()
            ->setWrapText(true);
        $this->objPHPExcel->getActiveSheet()->getDefaultRowDimension()
            ->setRowHeight(-1);
    }

    public function outHeader($sheetid=0){
        $this->objPHPExcel->setActiveSheetIndex($sheetid)
            ->setCellValueByColumnAndRow(0, 1, $this->header_title)
            ->setCellValueByColumnAndRow(0, 2, $this->header_string);
        $this->objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
        $height = $this->colTwo==2?20:50;
        $this->objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight($height);
        $this->objPHPExcel->getActiveSheet()->mergeCells("A1:C1");
        $this->objPHPExcel->getActiveSheet()->mergeCells("A2:C2");
        $this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()
            ->setSize(14)
            ->setBold(true);
        $this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getAlignment()
            ->setWrapText(false);
        $this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()
            ->setSize(12)
            ->setBold(true)
            ->setItalic(true);
        $this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getAlignment()
            ->setWrapText(true);

        $this->current_row = 4;
    }

    public function outExcel($name="summary"){
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
        ob_start();
        $objWriter->save('php://output');
        $output = ob_get_clean();
        spl_autoload_register(array('YiiBase','autoload'));
        $filename= iconv('utf-8','gbk//ignore',$name);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="'.$filename.'.xlsx"');
        header("Content-Transfer-Encoding:binary");
        echo $output;
    }

    protected function setHeaderStyleTwo($cells,$color="AFECFF") {
        $styleArray = array(
            'font'=>array(
                'bold'=>true,
            ),
            'alignment'=>array(
                'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            'borders'=>array(
                'allborders'=>array(
                    'style'=>PHPExcel_Style_Border::BORDER_THIN,
                ),
            ),
            'fill'=>array(
                'type'=>PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor'=>array(
                    'argb'=>$color,
                ),
            ),
        );
        $this->objPHPExcel->getActiveSheet()->getStyle($cells)
            ->applyFromArray($styleArray);
    }
    protected function getColumn($index){
        $index++;
        $mod = $index % 26;
        $quo = ($index-$mod) / 26;

        if ($quo == 0) return chr($mod+64);
        if (($quo == 1) && ($mod == 0)) return 'Z';
        if (($quo > 1) && ($mod == 0)) return chr($quo+63).'Z';
        if ($mod > 0) return chr($quo+64).chr($mod+64);
    }

    public function addSheet($sheet_name){
        $this->current_row=0;
        $this->th_num=0;
        $this->sheet_id++;
        $this->objPHPExcel->createSheet(); //插入工作表
        $this->objPHPExcel->setActiveSheetIndex($this->sheet_id); //切换到新创建的工作表
        $this->setSheetName($sheet_name);
    }

    public function setSheetName($sheet_name){
        $this->objPHPExcel->getActiveSheet()->setTitle($sheet_name);
    }
}