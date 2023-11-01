<?php
if($this->function_id!=$model->menu_code){
    $this->redirect(Yii::app()->createUrl('site/index'));
}
$this->pageTitle=Yii::app()->name . ' - projectManage';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'projectManage-list',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    #ProjectManageList_noOfItem{ width: 30%;display: inline-block;}
    .show-tr{ cursor: pointer; }
    .input-group-left{ display: table-cell;padding: 0px;width: 80px;}
    .clickSpanImg{ padding: 0px 10px; }
    @media (min-width: 1200px){
        .form-group-p{ padding:6px 0px;width: 10px;}
        .plan-div { width: 12%;}
    }
</style>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('app','Project manage'); ?></strong>
    </h1>
    <!--
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="#">Layout</a></li>
            <li class="active">Top Navigation</li>
        </ol>
    -->
</section>

<section class="content">
    <div class="box">
        <div class="box-body">
            <div class="form-group">
                <?php echo $form->labelEx($model,'project_type',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->dropDownList($model, 'project_type',FunctionList::getProjectTypeList(true),array('class'=>'changeSubmit')); ?>
                </div>
                <?php echo $form->labelEx($model,'project_code',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'project_code', array('maxlength'=>20,'autocomplete'=>'off')); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'project_name',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-7">
                    <?php echo $form->textField($model, 'project_name', array('maxlength'=>20,'autocomplete'=>'off')); ?>
                </div>
            </div>
            <div class="form-group">
                <?php
                $assUserList = FunctionSearch::getAssignUserList($model->menu_id,false);
                ?>
                <?php echo $form->labelEx($model,'lcu',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->dropDownList($model, 'lcu',$assUserList,array('class'=>'changeSubmit','empty'=>Yii::t("freed", "-- All --"))); ?>
                </div>
                <?php echo $form->labelEx($model,'assign_user',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->dropDownList($model, 'assign_user',$assUserList,array('class'=>'changeSubmit','empty'=>Yii::t("freed", "-- All --"))); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'lcd',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'lcd_start', array('placeholder'=>"开始时间",'id'=>'lcd_start','prepend'=>"<span class='fa fa-calendar'></span>",'autocomplete'=>'off')); ?>
                </div>
                <p class="col-lg-2 form-group-p">-</p>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'lcd_end', array('placeholder'=>"结束时间",'id'=>'lcd_end','prepend'=>"<span class='fa fa-calendar'></span>",'autocomplete'=>'off')); ?>
                </div>
                <?php echo $form->labelEx($model,'assign_plan',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-1 plan-div">
                    <?php echo $form->numberField($model, 'plan_min', array('placeholder'=>"最小值")); ?>
                </div>
                <p class="col-lg-2 form-group-p">-</p>
                <div class="col-lg-1 plan-div">
                    <?php echo $form->numberField($model, 'plan_max', array('placeholder'=>"最大值")); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-12 text-center">
                <?php
                echo TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('misc','Search'), array(
                    'class'=>'clickSubmit')
                );
                ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    $search_add_html="";
    if (Yii::app()->user->validRWFunction($this->function_id)){
        $search_add_html = TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add'), array(
            'submit'=>Yii::app()->createUrl('projectManage/Add',array("index"=>$model->menu_id)),
        ));
    }
   $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('freed','project list'),
        'model'=>$model,
        'viewhdr'=>'//projectManage/_listhdr',
        'viewdtl'=>'//projectManage/_listdtl',
        'gridsize'=>'24',
        'height'=>'600',
       'hasSearchBar'=>false,
       'item_bool'=>true,
       'search_add_html'=>$search_add_html,
       'searchlinkparam'=>array("index"=>$model->menu_id),
    ));
    ?>
</section>
<?php
echo $form->hiddenField($model,'pageNum');
echo $form->hiddenField($model,'totalRow');
echo $form->hiddenField($model,'orderField');
echo $form->hiddenField($model,'orderType');
?>
<?php $this->endWidget(); ?>

<?php
$submitUrl = Yii::app()->createUrl('projectManage/index',array("index"=>$model->menu_id,"pageNum"=>1));
$js ="
function submitMyForm(){
    jQuery.yii.submitForm(this,'{$submitUrl}',{});
}

$('.clickSubmit').on('click',submitMyForm);
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
$js = Script::genDatePicker(array(
    'lcd_start',
    'lcd_end'
));
Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);

$ajaxUrl = Yii::app()->createUrl('projectManage/ajaxDetail',array("index"=>$model->menu_id));
$js = <<<EOF
$('.show-tr').on('click',function(e){
    var span = $(this).children("span").eq(0);
    var id = $(this).children("span").eq(0).data('id');
    var that = $(this).parents('tr:first');
    
    if(span.hasClass('fa-plus-square')){
        if($(this).data('show')==1){
		    $('.detail_'+id).show();
        }else{
            $(this).data('show',1);
            $.ajax({
                type: 'GET',
                url: '{$ajaxUrl}',
                data: {
                    'id':id,
                },
                dataType: 'json',
                success: function(data) {
                    var assignObj = $(data['html']);
                    assignObj.find('img').each(function(){
                        var spanObj = $('<span>[图片]</span>');
                        spanObj.addClass('clickSpanImg');
                        spanObj.data('src',$(this).attr('src'));
                        $(this).before(spanObj);
                        $(this).remove();
                    });
                    that.after(assignObj);
                },
                error: function(data) { // if error occured
                    alert('Error occured.please try again');
                }
            });
        }
        span.removeClass('fa-plus-square').addClass('fa-minus-square');
    }else{
		$('.detail_'+id).hide();
        span.removeClass('fa-minus-square').addClass('fa-plus-square');
    }
    e.stopPropagation();
    return false;
});
EOF;
Yii::app()->clientScript->registerScript('showClick',$js,CClientScript::POS_READY);

$js = Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);

?>

