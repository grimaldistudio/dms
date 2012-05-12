<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'user-form',
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
<?php echo $form->textFieldRow($model, 'firstname'); ?>
<?php echo $form->textFieldRow($model, 'lastname'); ?>
<?php echo $form->textFieldRow($model, 'telephone'); ?> 
<?php echo $form->checkBoxRow($model, 'is_admin');  ?>
<?php echo $form->dropDownListRow($model, 'group_ids', CHtml::listData(Group::model()->findAll(), 'id', 'name'), array('multiple'=>'multiple', 'key'=>'group_ids', 'class'=>'multiselect')); ?>
<?php echo $form->dropDownListRow($model, 'role_ids', CHtml::listData(Role::model()->findAll(), 'id', 'name'), array('multiple'=>'multiple','key'=>'role_ids', 'class'=>'multiselect')); ?>

<?php echo CHtml::htmlButton('<i class="icon-ok" ></i> Salva', array('class'=>'btn', 'type'=>'submit')); ?>
<?php $this->endWidget(); ?>

<?php $this->widget(
      'application.extensions.emultiselect.EMultiSelect',
      array('options'=> array('sortable'=>false, 'searchable'=>true, 'height'=>140))
);?>