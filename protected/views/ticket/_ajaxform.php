<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'ticket-create-form',
    'action'=>Yii::app()->createUrl('/document/newticket', array('id'=>$document->id)),
    'htmlOptions'=>array('class'=>'well'),
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'validateOnChange'=>false,
        'inputContainer'=>'div.control-group',
        'afterValidate'=>'js:submitAjaxForm'
    )
)); ?>

<div class="modal-body">
<?php echo $form->errorSummary($model);  ?>
<p class="help-block">I campi marcati con <span class="required">*</span> sono obbligatori.</p>

<p style="font-weight: bold">
    <?php echo $document->getTitle(); ?>
</p>
<?php echo $form->dropDownListRow($model,'access_level',DocumentRight::model()->getPrivilegeArray(), array('empty'=>'Seleziona il permesso')); ?>
<?php echo $form->textAreaRow($model, 'request', array('rows'=>10, 'cols'=>70, 'style'=>'width:auto')); ?>

<?php if(count($last_tickets)>0): ?>
<p class="alert alert-warning">Attenzione: hai già aperto dei tickets per questo documento.</p>
<table class="table table-condensed table-striped">
    <tr>
        <th><?php echo $model->getAttributeLabel('id'); ?></th>
        <th><?php echo $model->getAttributeLabel('access_level'); ?></th>
        <th><?php echo $model->getAttributeLabel('replier_id'); ?></th>
        <th><?php echo $model->getAttributeLabel('granted_access_level'); ?></th>        
        <th><?php echo $model->getAttributeLabel('date_created'); ?></th>        
    </tr>
    <?php foreach($last_tickets as $ticket): ?>
    <tr>
        <td># <?php echo CHtml::encode($ticket['id']); ?></td>
        <td><?php echo Ticket::model()->getAccessLevelDesc($ticket['access_level']); ?></td>        
        <td><?php echo CHtml::encode($ticket['email']); ?></td>
        <td><?php echo Ticket::model()->getGrantedAccessLevelDesc($ticket['granted_access_level']); ?></td>
        <td><?php echo date('Y-m-d h:i:s', strtotime($ticket['date_created'])); ?></td>        
    </tr>
    <?php endforeach; ?>
</table>
<?php echo CHtml::link('Vedi i tuoi tickets', array('/ticket/my')); ?>
<?php endif; ?>

<div class="modal-footer">
    <?php echo CHtml::htmlButton('<i class="icon-ok icon-white" ></i> Salva', array('class'=>'btn btn-primary', 'type'=>'submit')); ?>    
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>

<?php $this->endWidget(); ?>