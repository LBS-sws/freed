<tr>
    <th width="30%">
        <?php echo TbHtml::label($this->getLabelName('username'), false); ?>
    </th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('email_type'), false); ?>
	</th>
	<th>
		<?php echo Yii::app()->user->validRWFunction('SS03') ?
				TbHtml::Button('+',array('id'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
