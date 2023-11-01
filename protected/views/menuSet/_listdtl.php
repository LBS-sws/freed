
<tr class='clickable-row' data-href='<?php echo $this->getLink('SS03', 'menuSet/edit', 'menuSet/view', array('index'=>$this->record['id']));?>'>


    <td><?php echo $this->drawEditButton('SS03', 'menuSet/edit', 'menuSet/view', array('index'=>$this->record['id'])); ?></td>

    <td><?php echo $this->record['menu_code']; ?></td>
    <td><?php echo $this->record['menu_name']; ?></td>
    <td><?php echo $this->record['user_str']; ?></td>
    <td><?php echo $this->record['display']; ?></td>
    <td><?php echo $this->record['z_index']; ?></td>
</tr>
