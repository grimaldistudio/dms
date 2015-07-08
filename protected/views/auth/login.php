<?php
$this->pageTitle=Yii::app()->name . ' - Login';
?>
<div class="text-center center" style="text-align: center;margin:0 0 10px 0;"><img src="/dms/images/brand-gaeta.png" />
<h1><?php echo Yii::app()->name?> del Comune di Gaeta</h1>
</div>

<?php $form=$this->beginWidget('bootstrap.widgets.BootActiveForm', array(
	'id'=>'login-form',
        'htmlOptions' => array('class'=>'well'),
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
                'validateOnChange' => false,
                'inputContainer' => 'div.control-group'
	),
)); ?>

<?php echo $form->errorSummary($model);  ?>
<p class="help-block">I campi marcati con <span class="required">*</span> sono obbligatori.</p>

<?php echo $form->textFieldRow($model,'username'); ?>
<?php echo $form->passwordFieldRow($model,'password'); ?>
<?php echo $form->checkBoxRow($model,'rememberMe'); ?>
<?php echo CHtml::htmlButton('<i class="icon-ok" ></i> Login', array('class'=>'btn', 'type'=>'submit')); ?>
<?php echo CHtml::link('Password dimenticata?', '/auth/forgotpassword'); ?>

<?php $this->endWidget(); ?>