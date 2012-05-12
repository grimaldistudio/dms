<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'login-info-form',
    'htmlOptions'=>array('class'=>'well'),
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'validateOnChange'=>false,
        'inputContainer'=>'div.control-group',
        'afterValidate'=>'js:jqFormYii'
    )
)); ?>
<?php echo $form->errorSummary($model);  ?>
<div class="alert alert-success" id="login-info-form-success" style="display:none">
    <span id="login-info-form-success-content"></span>    
</div>
<p class="help-block">I campi marcati con <span class="required">*</span> sono obbligatori.</p>

<?php echo CHtml::hiddenField('login', 1); ?>
<?php echo $form->passwordFieldRow($model, 'new_password'); ?>
<?php echo $form->passwordFieldRow($model, 'confirm_password'); ?>

<?php echo CHtml::htmlButton('<i class="icon-ok" ></i> Salva', array('class'=>'btn', 'type'=>'submit')); ?>
<?php $this->endWidget(); ?>