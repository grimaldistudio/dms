<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'role-form',
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

<?php echo $form->textFieldRow($model, 'name'); ?>
<?php echo $form->textAreaRow($model, 'description'); ?>
<?php echo $form->dropDownListRow($model, 'right_ids', CHtml::listData(Right::model()->findAll(), 'id', 'name'), array('multiple'=>'multiple', 'key'=>'right_ids', 'class'=>'multiselect')); ?>

<?php echo CHtml::htmlButton('<i class="icon-ok" ></i> Salva', array('class'=>'btn', 'type'=>'submit')); ?>
<?php $this->endWidget(); ?>

<?php $this->widget(
      'application.extensions.emultiselect.EMultiSelect',
      array('options'=> array('sortable'=>false, 'searchable'=>true, 'height'=>140))
);?>
