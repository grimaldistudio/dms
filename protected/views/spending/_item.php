<?php $model = Spending::model(); ?>
<div class="row">
<div class="span8">
<table class="table table-striped table-condensed detail-view " >
    
    <tr>
        <th><label><?php echo $model->getAttributeLabel('title'); ?></label></th>
        <td><p><?php echo CHtml::encode($data['title']); ?></p></td>
    </tr>    
    
    <tr>
        <th><label><?php echo $model->getAttributeLabel('receiver'); ?></label></th>
        <td><p><?php echo CHtml::encode($data['receiver']); ?></p></td>
    </tr>    
    
    <tr>
        <th><label><?php echo $model->getAttributeLabel('amount'); ?></label></th>
        <td><p><?php echo sprintf("€ %.2f", $data['amount']); ?></p></td>
    </tr>    
    
    <tr>
        <th><label><?php echo $model->getAttributeLabel('spending_date'); ?></label></th>
        <td><p><?php echo date('d-m-Y', strtotime($data['spending_date'])); ?></p></td>
    </tr>
    
    <tr>
        <th colspan="2">
            <?php echo CHtml::link('Vedi', array('/spending/view', 'id'=>$data['id']), array('class'=>'btn btn-primary')); ?>
            <?php if($data['status']==Spending::DISABLED_STATUS): ?>
                <?php echo CHtml::link('Ripubblica su elenco', array('/spending/enable', 'id'=>$data['id']), array('class'=>'btn btn-primary btn-enable')); ?>            
                <?php echo CHtml::link('Rimuovi definitivamente', array('/spending/delete', 'id'=>$data['id']), array('class'=>'btn btn-primary btn-delete')); ?>
            <?php endif; ?>

            <?php if(Yii::app()->user->isAdmin() || $data['creator_id']==Yii::app()->user->id): ?>
                <?php if($data['status']==Spending::ACTIVE_STATUS): ?>
                    <?php echo CHtml::link('Rimuovi da elenco', array('/spending/disable', 'id'=>$data['id']), array('class'=>'btn btn-primary btn-disable')); ?>                
                <?php endif; ?>
                <?php echo CHtml::link('Modifica', array('/spending/update', 'id'=>$data['id']), array('class'=>'btn btn-primary')); ?>    
            <?php endif; ?>
        </th>
    </tr>
</table>
</div>
</div>