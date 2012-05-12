<?php
$this->pageTitle=Yii::app()->name . ' - Password Dimenticata';
?>
<h1>Password Dimenticata</h1>
<p class="help-block">Compilare la seguente form con il tuo indirizzo e-mail al quale ricevere le istruzioni per impostare una nuova password:</p>

<?php $form=$this->beginWidget('bootstrap.widgets.BootActiveForm', array(
	'id'=>'forgot-password-form',
	'enableClientValidation'=>true,
        'htmlOptions' => array('class'=>'well'),
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
                'validateOnChange'=>false,
                'inputContainer' =>'div.control-group'
	),
)); ?>

        <?php echo $form->errorSummary($model); ?>

	<p class="help-block">I campi marcati con <span class="required">*</span> sono obbligatori.</p>

        <?php echo $form->textFieldRow($model,'username'); ?>
        <?php echo CHtml::htmlButton('<i class="icon-ok"></i> Recupera Password', array('class'=>'btn', 'type'=>'submit')); ?>
        <?php echo CHtml::link('Login <i class="icon-arrow-right"></i>', '/auth/login'); ?>
<?php $this->endWidget(); ?>