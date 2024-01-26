<tr>
    <th width="1%"></th>
	<th width="14%">
		<?php echo TbHtml::link($this->getLabelName('project_code').$this->drawOrderArrow('a.project_code'),'#',$this->createOrderLink('projectManage-list','a.project_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('project_type').$this->drawOrderArrow('a.project_type'),'#',$this->createOrderLink('projectManage-list','a.project_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('project_name').$this->drawOrderArrow('a.project_name'),'#',$this->createOrderLink('projectManage-list','a.project_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('lcu').$this->drawOrderArrow('a.lcu'),'#',$this->createOrderLink('projectManage-list','a.lcu'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('lcd').$this->drawOrderArrow('a.lcd'),'#',$this->createOrderLink('projectManage-list','a.lcd'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('plan_date').$this->drawOrderArrow('a.plan_date'),'#',$this->createOrderLink('projectManage-list','a.plan_date'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('urgency').$this->drawOrderArrow('a.urgency'),'#',$this->createOrderLink('projectManage-list','a.urgency'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('assign_user').$this->drawOrderArrow('a.assign_user'),'#',$this->createOrderLink('projectManage-list','a.assign_user'))
        ;
        ?>
    </th>
	<th width="10%">
		<?php echo TbHtml::link($this->getLabelName('assign_plan').$this->drawOrderArrow('a.assign_plan'),'#',$this->createOrderLink('projectManage-list','a.assign_plan'))
			;
		?>
	</th>
    <th width="1%">&nbsp;</th>
</tr>
