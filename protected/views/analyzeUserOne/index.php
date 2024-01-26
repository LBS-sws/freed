<?php
$this->pageTitle=Yii::app()->name . ' - AnalyzeUserOne Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'AnalyzeUserOne-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Username analyze'); ?></strong>
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
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
        <?php echo TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('freed','Enquiry'), array(
            'submit'=>Yii::app()->createUrl('analyzeUserOne/view')));
        ?>
	</div>
            <div class="text-danger" style="display:inline;padding-left:10px;">
                <span>说明:红色数字提示表示列表当前未完成项目的数量</span>
            </div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'menu_id'); ?>

            <div class="form-group">
                <?php echo $form->labelEx($model,'search_type',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-10">
                    <?php echo $form->inlineRadioButtonList($model, 'search_type',FunctionList::getSelectType(),
                        array('readonly'=>false,'id'=>'search_type')
                    ); ?>
                </div>
            </div>
            <div id="search_div">
                <div data-id="1" <?php if ($model->search_type!=1){ echo "style='display:none'"; } ?>>
                    <div class="form-group">
                        <?php echo $form->labelEx($model,'search_year',array('class'=>"col-sm-2 control-label")); ?>
                        <div class="col-sm-2">
                            <?php echo $form->dropDownList($model, 'search_year',FunctionList::getSelectYear(),
                                array('readonly'=>false,'id'=>'year_one')
                            ); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form->labelEx($model,'search_quarter',array('class'=>"col-sm-2 control-label")); ?>
                        <div class="col-sm-2">
                            <?php echo $form->dropDownList($model, 'search_quarter',FunctionList::getQuMonthList(),
                                array('readonly'=>false)
                            ); ?>
                        </div>
                    </div>
                </div>
                <div data-id="2" <?php if ($model->search_type!=2){ echo "style='display:none'"; } ?>>
                    <div class="form-group">
                        <?php echo $form->labelEx($model,'search_year',array('class'=>"col-sm-2 control-label")); ?>
                        <div class="col-sm-2">
                            <?php echo $form->dropDownList($model, 'search_year',FunctionList::getSelectYear(),
                                array('readonly'=>false,'id'=>'year_two')
                            ); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form->labelEx($model,'search_month',array('class'=>"col-sm-2 control-label")); ?>
                        <div class="col-sm-2">
                            <?php echo $form->dropDownList($model, 'search_month',FunctionList::getSelectMonth(),
                                array('readonly'=>false)
                            ); ?>
                        </div>
                    </div>
                </div>
                <div data-id="3" <?php if ($model->search_type!=3){ echo "style='display:none'"; } ?>>
                    <div class="form-group">
                        <?php echo $form->labelEx($model,'search_start_date',array('class'=>"col-sm-2 control-label")); ?>
                        <div class="col-sm-2">
                            <?php echo $form->textField($model, 'search_start_date',
                                array('readonly'=>false,'prepend'=>"<span class='fa fa-calendar'></span>")
                            ); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form->labelEx($model,'search_end_date',array('class'=>"col-sm-2 control-label")); ?>
                        <div class="col-sm-2">
                            <?php echo $form->textField($model, 'search_end_date',
                                array('readonly'=>false,'prepend'=>"<span class='fa fa-calendar'></span>")
                            ); ?>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</section>


<?php
$js="
    $('#year_one,#year_two').change(function(){
        var year = $(this).val();
        $('#year_one,#year_two').val(year);
    });
    $('input[type=radio]').change(function(){
        var id = $(this).val();
        console.log(id);
        $('#search_div').children('div').hide();
        $('#search_div').children('div[data-id='+id+']').show();
    });
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
$js = Script::genDatePicker(array(
    'AnalyzeUserOneForm_search_start_date',
    'AnalyzeUserOneForm_search_end_date'
));
Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


