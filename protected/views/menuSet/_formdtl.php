<tr>
	<td>
		<?php echo TbHtml::dropDownList($this->getFieldName('username'),  $this->record['username'],FunctionSearch::getMenuUserList(),
								array('disabled'=>$this->model->scenario=='view','class'=>'username','prepend'=>'<span class="fa fa-calendar"></span>')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::dropDownList($this->getFieldName('email_type'),  $this->record['email_type'],FunctionList::getEmailTypeList(),
								array('disabled'=>$this->model->scenario=='view','rows'=>3)
		); ?>
	</td>
	<td>
		<?php 
			echo Yii::app()->user->validRWFunction('SS03')
				? TbHtml::Button('-',array('id'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('set_id'),$this->record['set_id']); ?>
	</td>
</tr>
