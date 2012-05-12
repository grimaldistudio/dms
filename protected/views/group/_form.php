<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'group-form',
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

<?php echo $form->textFieldRow($model, 'email'); ?>
<?php echo $form->textFieldRow($model, 'name'); ?>
<?php echo $form->textFieldRow($model, 'telephone'); ?> 
<?php echo $form->textFieldRow($model, 'fax'); ?> 
<?php echo $form->textAreaRow($model, 'description'); ?>

<?php echo CHtml::htmlButton('<i class="icon-ok" ></i> Salva', array('class'=>'btn', 'type'=>'submit')); ?>
<?php $this->endWidget(); ?>