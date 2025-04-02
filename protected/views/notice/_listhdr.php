<tr>
	<th>
		<?php echo TbHtml::link($this->getLabelName('lcd').$this->drawOrderArrow('lcd'),'#',$this->createOrderLink('notice-list','lcd'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('note_type').$this->drawOrderArrow('note_type'),'#',$this->createOrderLink('notice-list','note_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('flow_title').$this->drawOrderArrow('flow_title'),'#',$this->createOrderLink('notice-list','flow_title'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('ready_bool').$this->drawOrderArrow('ready_bool'),'#',$this->createOrderLink('notice-list','ready_bool'))
			;
		?>
	</th>
</tr>
