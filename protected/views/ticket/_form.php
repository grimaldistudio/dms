<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'ticket-form',
    'htmlOptions'=>array('class'=>'well'),
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'validateOnChange'=>false,
        'inputContainer'=>'div.control-group',
    )
)); ?>
<?php echo $form->errorSummary($model);  ?>
<p class="help-block">I campi marcati con <span class="required">*</span> sono obbligatori.</p>
<p style="font-weight: bold">
    Documento: <?php echo $document->getTitle(); ?>
</p>    
<?php echo $form->dropDownListRow($model,'access_level',DocumentRight::model()->getPrivilegeArray(), array('empty'=>'Seleziona il permesso')); ?>
<?php echo $form->textAreaRow($model, 'request', array('rows'=>10, 'cols'=>70, 'style'=>'width:auto')); ?>

<?php echo CHtml::htmlButton('<i class="icon-ok icon-white" ></i> Salva', array('class'=>'btn btn-primary', 'type'=>'submit')); ?>
<?php $this->endWidget(); ?>