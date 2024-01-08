<?php
if($this->function_id!=$model->menu_code){
    $this->redirect(Yii::app()->createUrl('site/index'));
}
$this->pageTitle=Yii::app()->name . ' - projectManage Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'projectManage-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    *.readonly{ pointer-events: none;}
</style>

<section class="content-header">
    <h1><?php echo Yii::t("freed","project form");?></h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('projectManage/add',array("index"=>$model->menu_id))));
			}
		?>
		<?php
        if(empty($model->id)){
            $backUrl = Yii::app()->createUrl('projectManage/index',array("index"=>$model->menu_id));
        }else{
            $backUrl = Yii::app()->createUrl('projectManage/view',array("index"=>$model->id));
        }
        echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>$backUrl));
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('projectManage/save')));
			?>
<?php endif ?>
<?php if ($model->scenario!='new' && $model->scenario!='view'): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>
        <div class="btn-group pull-right" role="group">
            <?php if ($model->scenario!='new'): ?>
                <?php echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('freed','Flow Info'), array(
                        'data-toggle'=>'modal','data-target'=>'#flowinfodialog',)
                );
                ?>
            <?php endif ?>
            <?php
            $counter = ($model->no_of_attm['prom'] > 0) ? ' <span id="docprom" class="label label-info">'.$model->no_of_attm['prom'].'</span>' : ' <span id="docprom"></span>';

            echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('misc','Attachment').$counter, array(
                    'name'=>'btnFile','id'=>'btnFile','data-toggle'=>'modal','data-target'=>'#fileuploadprom',)
            );
            ?>
        </div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'menu_id'); ?>
			<?php echo $form->hiddenField($model, 'lcu'); ?>
			<?php echo $form->hiddenField($model, 'lcd'); ?>
			<?php echo $form->hiddenField($model, 'project_status'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'project_type',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-3">
				<?php
                echo $form->dropDownList($model, 'project_type',FunctionList::getProjectTypeList(),
					array('readonly'=>($model->scenario=='view'))
				); ?>
				</div>
                <?php echo $form->labelEx($model,'assign_user',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    echo $form->dropDownList($model, 'assign_user',FunctionSearch::getAssignUserList($model->menu_id),
                        array('readonly'=>($model->scenario=='view'),'id'=>'assign_user')
                    ); ?>
                </div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'project_name',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-7">
				<?php
                echo $form->textField($model, 'project_name',
					array('readonly'=>($model->scenario=='view'),'autocomplete'=>'off')
				); ?>
				</div>
			</div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'project_text',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-9">
                    <?php
                    echo $form->textArea($model, 'project_text',
                        array('readonly'=>($model->scenario=='view'),'id'=>"project_text",'rows'=>10)
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'emailList',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-6" >
                    <div>
                        <?php
                        echo TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('freed','look'), array(
                                'id'=>'show_email')
                        );
                        ?>
                    </div>
                    <div id="email_div" style="display: none;">
                        <?php
                        echo ProjectManageModel::emailTable($model->menu_id,$model->emailList);
                        ?>
                    </div>
                </div>
            </div>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//projectManage/historylist',array("project_id"=>$model->id)); ?>

<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
    'form'=>$form,
    'doctype'=>'PROM',
    'header'=>Yii::t('misc','Attachment'),
    'ronly'=>($model->scenario=='view')
));
?>
<?php
Script::genFileUpload($model,$form->id,'PROM');
$js="
$('#show_email').click(function(){
    if($('#email_div').css('display')=='none'){
        $('#email_div').slideDown(100);
    }else{
        $('#email_div').slideUp(100);
    }
});

$('.td_end').click(function(e){
    if($(this).find('.fa').length>0){
        var id=$(this).data('id');
        var history_code=$(this).prevAll('.history_code').eq(0).text();
        var history_date=$(this).prevAll('.history_date').eq(0).text();
        $('#attrModel').find('.modal-title>small').remove();
        $('#attrModel').find('.modal-title').append('<small>（'+history_code+' _ '+history_date+'）</small>');
        
        $.ajax({
            type: 'get',
            url: '".Yii::app()->createUrl('projectManage/AjaxFileTable')."',
            data: {id:id},
            dataType: 'json',
            success: function(data){
                $('#tblFileproinfo>tbody').html(data.html);
                $('#attrModel').modal('show');
            }
        });
    }
    e.stopPropagation();
});
";
Yii::app()->clientScript->registerScript('attrInfoFunction',$js,CClientScript::POS_READY);
$uploadImage = Yii::app()->createUrl('projectManage/uploadImgArea');
$js = "
var config_edit={
      toolbar: [
        {
          name: 'clipboard',
          items: ['Undo', 'Redo']
        },
        {
          name: 'styles',
          items: ['Format', 'Font', 'FontSize']
        },
        {
          name: 'colors',
          items: ['TextColor', 'BGColor']
        },
        {
          name: 'align',
          items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
        },
        '/',
        {
          name: 'basicstyles',
          items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat', 'CopyFormatting']
        },
        {
          name: 'links',
          items: ['Link', 'Unlink']
        },
        {
          name: 'paragraph',
          items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote']
        },
        {
          name: 'insert',
          items: ['Image', 'Table']
        },
        {
          name: 'tools',
          items: ['Maximize']
        },
        {
          name: 'editing',
          items: ['Scayt']
        }
      ],
    extraAllowedContent: 'h3{clear};h2{line-height};h2 h3{margin-left,margin-top}',

    extraPlugins: 'print,format,font,colorbutton,justify,uploadimage',
// Configure your file manager integration. This example uses CKFinder 3 for PHP.
    filebrowserUploadUrl: '{$uploadImage}',
    filebrowserImageUploadUrl: '{$uploadImage}',

      // Upload dropped or pasted images to the CKFinder connector (note that the response type is set to JSON).
    uploadUrl: '{$uploadImage}',
    image_previewText: ' ',

      removeDialogTabs: 'image:advanced;link:advanced',
      removeButtons: 'PasteFromWord'
};
CKEDITOR.replace('project_text',config_edit);

$('#assign_user').change(function(){
    var username = $(this).val();
    if(username!=''){
        $('#emailTable>tbody>tr').each(function(){
            if($(this).data('user')==username){
                $(this).find('.changeEmailType').val(1).change();
            }
        });
    }
});

$('.changeEmailType').change(function(){
	var n=$(this).attr('id').split('_');
	$('#emailList_'+n[1]+'_uflag').val('Y');
});
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);

$js = Script::genDeleteData(Yii::app()->createUrl('projectManage/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

/*
if ($model->scenario!='view') {
    $js = Script::genDatePicker(array(
        'StudyArticleModel_study_date',
    ));
    Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}
*/
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
/*
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . "/js/jquery-form.js", CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . "/js/ajaxFile.js", CClientScript::POS_END);
*/
?>

<?php $this->endWidget(); ?>


