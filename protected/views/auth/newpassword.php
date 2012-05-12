<?php
$this->pageTitle=Yii::app()->name . ' - Reimposta Password';
?>
<h1>Reimposta Password</h1>
<?php $form=$this->beginWidget('bootstrap.widgets.BootActiveForm', array(
	'id'=>'new-password-form',
	'enableClientValidation'=>true,
        'htmlOptions'=>array('class'=>'well'),
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
                'validateOnChange' => false,
                'inputContainer'=>'div.control-group'
	),
)); ?>

<p class="help-block">I campi marcati con <span class="required">*</span> sono obbligatori.</p>

<?php echo $form->passwordFieldRow($model,'password'); ?>
<?php echo $form->passwordFieldRow($model,'confirm_password'); ?>
<?php echo CHtml::htmlButton('<i class="icon-ok"></i> Salva', array('class'=>'btn', 'type'=>'submit')); ?>

<?php $this->endWidget(); ?>