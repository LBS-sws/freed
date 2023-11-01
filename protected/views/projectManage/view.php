<?php
if($this->function_id!=$model->menu_code){
    //$this->redirect(Yii::app()->createUrl('site/index'));
}
$this->pageTitle=Yii::app()->name . ' - projectManage Form';
?>
<style>
    .div-assign{ border-top: 1px solid #eee;}
    .div-assign-time{ color:#777;text-align: left;padding-top:0px;padding-bottom: 7px;}
    .div-assign img,.project_text img{ max-width: 100%;height: auto !important;}
    .div-assign h4>b{ padding-right: 7px;}
    #assignDiv{ border-bottom: 1px solid #eee;margin-bottom: 10px;}
    #assignDiv>.form-group{ margin-bottom: 0px;padding-bottom: 5px;}
    #assignDiv>.form-group.active{ background: #eee;}

    @media (min-width: 1200px){
        .div-assign-time{ text-align: right;padding-top: 14px;}
    }
</style>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'projectManage-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
    <h1><?php echo Yii::t("freed","project form");?></h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('projectManage/index',array("index"=>$model->menu_id))));
		?>
	</div>
            <div class="btn-group pull-right" role="group">
                <?php echo TbHtml::button('<span class="glyphicon glyphicon-pencil"></span> '.Yii::t('freed','Update'), array(
                    'submit'=>Yii::app()->createUrl('projectManage/edit',array("index"=>$model->id))));
                ?>
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

			<div class="form-group">
				<?php echo $form->labelEx($model,'project_type',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-3">
                    <?php
                    $text = FunctionList::getProjectTypeStr($model->project_type);
                    echo TbHtml::textField("none",$text,array("readonly"=>true));
                    ?>
				</div>
                <?php echo $form->labelEx($model,'assign_user',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    $text = FunctionSearch::getUserDisplayName($model->assign_user);
                    echo TbHtml::textField("none",$text,array("readonly"=>true));
                    ?>
                </div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'project_name',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-7">
                    <?php
                    echo TbHtml::textField("none",$model->project_name,array("readonly"=>true));
                    ?>
				</div>
			</div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'lcu',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    $text = FunctionSearch::getUserDisplayName($model->lcu);
                    echo TbHtml::textField("none",$text,array("readonly"=>true));
                    ?>
                </div>
                <?php echo $form->labelEx($model,'lcd',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    echo TbHtml::textField("none",$model->lcd,array("readonly"=>true));
                    ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'assign_plan',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    $text = FunctionList::getAssignPlanStr($model->assign_plan);
                    echo TbHtml::textField("project_plan",$text,array("readonly"=>true));
                    ?>
                </div>
                <?php echo $form->labelEx($model,'project_status',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    $text = FunctionList::getProjectStatusStr($model->project_status);
                    echo TbHtml::textField("project_status",$text,array("readonly"=>true));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'project_text',array('class'=>"col-lg-2 control-label")); ?>
            </div>
            <div class="box">
                <div class="box-body">
                    <div class="col-lg-12 project_text">
                        <span><?php echo $model->project_text;?></span>
                    </div>
                </div>
            </div>

            <?php
            echo AssignPlanModel::printAssignHtml($model->id);
            ?>

            <div class="form-group">
                <?php echo $form->labelEx($assignModel,'assign_plan',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->numberField($assignModel, 'assign_plan',
                        array('readonly'=>($assignModel->getReadonly()),'id'=>"assign_plan",'min'=>0,'max'=>100,'append'=>"%")
                    ); ?>
                </div>
                <?php echo TbHtml::label(Yii::t("freed","Quick operation"),'',array('class'=>"col-lg-2 control-label"));?>
                <div class="col-lg-5">
                    <?php
                    echo TbHtml::dropDownList("quick","",FunctionList::getQuickList(),array("id"=>"quick","empty"=>"","options"=>FunctionList::getQuickOptionList()));
                    ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($assignModel,'assign_text',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-9">
                    <?php
                    echo $form->textArea($assignModel, 'assign_text',
                        array('readonly'=>($model->scenario=='view'),'id'=>"assign_text",'rows'=>10)
                    ); ?>
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-11 text-right">
                    <?php
                    echo TbHtml::button(Yii::t("freed","send"),array("id"=>"assignBtn","class"=>""));
                    ?>
                </div>
            </div>
		</div>
	</div>
</section>
<?php $this->renderPartial('//projectManage/historylist',array("project_id"=>$model->id)); ?>

<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
    'form'=>$form,
    'doctype'=>'PROM',
    'header'=>Yii::t('misc','Attachment'),
    'ronly'=>($model->scenario=='view')
));
?>
<?php $this->renderPartial('attr',array(
    'model'=>$model,
    'form'=>$form,
    'doctype'=>'PROINFO',
    'header'=>Yii::t('misc','Attachment'),
    'ronly'=>(false)
));
?>

<?php
Script::genFileUpload($model,$form->id,'PROM');
$js="
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
var assignEditor = CKEDITOR.replace('assign_text',config_edit);

$('#quick').change(function(){
    var assign_plan = $(this).val();
    var assign_text = $(this).children('option:selected').data('text');
    if(assign_text=='-2'){
        assign_plan = $('#project_plan').val();
        assign_plan = assign_plan==''?0:parseInt(assign_plan,10);
    }
    if($('#assign_plan').val()==''){
        $('#assign_plan').val(assign_plan);
    }
    if(assign_text!=''&&assignEditor.getData()==''){
        assignEditor.setData(assign_text);
    }
    if(assign_text=='-1'){
        $('#assign_plan').val('');
        assignEditor.setData('');
    }
});
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);

$ajaxLink = Yii::app()->createUrl('projectManage/saveAssign');
$js="
    $('#assignBtn').on('click',function(){
        var assignEditor = CKEDITOR.instances['assign_text'];
        if($(this).data('ajax')==1){
            $('#alertDialog .alertText').text('请勿重复操作');
            $('#alertDialog').modal('show');
            return false;
        }else{
            $(this).data('ajax',1);
        }
        $.ajax({
            type: 'POST',
            url: '$ajaxLink',
            data: {
                scenario:'new',
                project_id:'{$model->id}',
                assign_plan:$('#assign_plan').val(),
                assign_text:assignEditor.getData()
            },
            dataType: 'json',
            success: function(data) {
                $('#assignBtn').data('ajax',0);
                if(data.status==1){
                    $('#assign_plan').val('');
                    $('#quick').val('');
                    $('#project_plan').val(data.project_plan);
                    $('#project_status').val(data.project_status);
                    assignEditor.setData('');
                    $('#alertDialog .alertText').text('已更新进度');
                    var assignObj = $(data.html);
                    $('#assignDiv').append(assignObj);
                    $('#alertDialog').modal('show');
                }else{
                    $('#alertDialog .alertText').html(data.message);
                    $('#alertDialog').modal('show');
                }
            },
            error: function(data) { // if error occured
                $('#assignBtn').data('ajax',0);
                alert('Error occured.please try again');
            }
        });
        notifyFunction();//需要刷新菜单栏的标签
    });
";
Yii::app()->clientScript->registerScript('sendFunction',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);

?>

<?php $this->endWidget(); ?>

<?php
$this->widget('bootstrap.widgets.TbModal', array(
    'id'=>"alertDialog",
    'header'=>Yii::t('dialog',"Advice"),
    'content'=>'<p class="alertText">&nbsp;</p>',
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','OK'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
    ),
    'show'=>false,
));
?>


