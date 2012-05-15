<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'ticket-update-form',
    'action'=>Yii::app()->createUrl('/ticket/update/', array('id'=>$model->id)),
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

<p>
    <b>Documento:</b>
    <?php echo CHtml::encode($model->document->getTitle()); ?>
</p>

<p>
    <b>Creato da:</b>    
    <?php echo CHtml::encode($model->user->getFullName()); ?>
</p>    

<p>
    <b>Accesso richiesto:</b>
    <?php echo CHtml::encode($model->getAccessLevelDesc()); ?>
</p>

<p>
    <b>Richiesta:</b>    
    <?php echo CHtml::encode($model->request); ?>
</p>

<?php echo $form->dropDownListRow($model,'granted_access_level',$model->getGrantedAccessLevelArray(), array('empty'=>'Seleziona il permesso')); ?>
<?php echo $form->textAreaRow($model, 'reply', array('rows'=>10, 'cols'=>50, 'style'=>'width: auto')); ?>
</div>

<div class="modal-footer">
    <?php echo CHtml::htmlButton('<i class="icon-ok icon-white" ></i> Salva', array('class'=>'btn btn-primary', 'type'=>'submit')); ?>    
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>

<?php $this->endWidget(); ?>