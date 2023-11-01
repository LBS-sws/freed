<?php
if (empty($model->id)&&$model->scenario == "edit"){
    $this->redirect(Yii::app()->createUrl('menuSet/index'));
}
$this->pageTitle=Yii::app()->name . ' - menuSet Form';
?>
<style>
    #staffDiv .checkbox-inline{width: 100px;}
</style>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'menuSet-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
    'htmlOptions'=>array('enctype' => 'multipart/form-data')
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('freed','menu form'); ?></strong>
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
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('menuSet/index')));
		?>

        <?php if ($model->scenario!='view'): ?>
            <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
                'submit'=>Yii::app()->createUrl('menuSet/save')));
            ?>
            <?php if ($model->scenario=='edit'): ?>
                <?php
                echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add'), array(
                    'submit'=>Yii::app()->createUrl('menuSet/new'),
                ));
                ?>
                <?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
                        'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
                );
                ?>
            <?php endif; ?>
        <?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo CHtml::hiddenField('dtltemplate'); ?>

            <div class="form-group">
                <?php echo $form->labelEx($model,'menu_code',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'menu_code',
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
                <div class="col-lg-5">
                    <p class="form-control-static text-warning">请填写两位大写的字母（A-Z）例如：TE</p>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'menu_name',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->textField($model, 'menu_name',
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'z_index',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php
                    echo $form->numberField($model, 'z_index',
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
                <div class="col-lg-2">
                    <p class="form-control-static text-warning">数值越小越靠前（升序）</p>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'display',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-5">
                    <?php
                    $list = array(Yii::t("freed","none"),Yii::t("freed","show"));
                    echo $form->inlineRadioButtonList($model, 'display',$list,
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
            </div>


            <div class="box">
                <div class="box-body table-responsive">
                    <div class="col-lg-8 col-lg-offset-2">
                        <div class="row">
                            <?php
                            $this->widget('ext.layout.TableView2Widget', array(
                                'model'=>$model,
                                'attribute'=>'detail',
                                'viewhdr'=>'//menuSet/_formhdr',
                                'viewdtl'=>'//menuSet/_formdtl',
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</section>

<?php
$this->renderPartial('//site/removedialog');
?>
<?php
$js = "
$('table').on('change','[id^=\"MenuSetForm\"]',function() {
	var n=$(this).attr('id').split('_');
	$('#MenuSetForm_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);

$language = Yii::app()->language;
$js = <<<EOF
$('table').on('click','#btnDelRow', function() {
	$(this).closest('tr').find('[id*=\"_uflag\"]').val('D');
	$(this).closest('tr').hide();
});
EOF;
Yii::app()->clientScript->registerScript('removeRow',$js,CClientScript::POS_READY);

$js = <<<EOF
$(document).ready(function(){
	var ct = $('#tblDetail tr').eq(1).html();
	$('#dtltemplate').attr('value',ct);
});

$('#btnAddRow').on('click',function() {
	var r = $('#tblDetail tr').length;
	if (r>0) {
		var nid = '';
		var ct = $('#dtltemplate').val();
		$('#tblDetail tbody:last').append('<tr>'+ct+'</tr>');
		$('#tblDetail tr').eq(-1).find('[id*=\"MenuSetForm_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);
			
			if (id.indexOf('_username') != -1) $(this).val('');
			if (id.indexOf('_email_type') != -1) $(this).val('1');
			if (id.indexOf('_id') != -1) $(this).attr('value',0);
		});
		if (nid != '') {
			var topos = $('#'+nid).position().top;
			$('#tbl_detail').scrollTop(topos);
		}
	}
});
EOF;
Yii::app()->clientScript->registerScript('addRow',$js,CClientScript::POS_READY);


$js = Script::genDeleteData(Yii::app()->createUrl('menuSet/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

</div><!-- form -->

