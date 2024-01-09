<?php
$withrow = $this->record['detailCount']>0;
$idX = $this->record['id'];
$color = $this->record['color'];
?>
<tr class='clickable-row <?php echo $color;?>' data-href='<?php echo $this->getLink("none11", 'projectManage/edit', 'projectManage/view', array('index'=>$this->record['id']));?>'>


    <td><?php echo $this->drawEditButton("none11", 'projectManage/edit', 'projectManage/view', array('index'=>$this->record['id'])); ?></td>

    <td><?php echo $this->record['project_code']; ?></td>
    <td><?php echo $this->record['project_type']; ?></td>
    <td><?php echo $this->record['project_name']; ?></td>
    <td><?php echo $this->record['lcu']; ?></td>
    <td><?php echo $this->record['lcd']; ?></td>
    <td><?php echo $this->record['plan_date']; ?></td>
    <td><?php echo $this->record['assign_user']; ?></td>
    <td><?php echo $this->record['assign_plan']; ?></td>

    <?php
    $iconX = $withrow ? "<span data-id='$idX' class='fa fa-plus-square'></span>" : "<span class='fa fa-square'></span>";
    $tdHtml = "";
    if($withrow){
        $tdHtml.= "<td class='show-tr'>";
    }else{
        $tdHtml.= "<td>";
    }
    $tdHtml.=$iconX;
    $tdHtml.="</td>";
    echo $tdHtml;
    ?>
</tr>
