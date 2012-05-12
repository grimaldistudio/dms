<?php $form_id = $model->scenario=='update'?'tag-form-update':'tag-form'; ?>
<?php $action = $model->scenario=='update'?Yii::app()->createUrl('/tag/update/', array('id'=>$model->id)):Yii::app()->createUrl('/tag/create'); ?>
<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>$form_id,
    'action'=>$action,
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

<?php echo $form->textFieldRow($model, 'name'); ?>
</div>

<div class="modal-footer">
    <?php echo CHtml::htmlButton('<i class="icon-ok icon-white" ></i> Salva', array('class'=>'btn btn-primary', 'type'=>'submit')); ?>    
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>

<?php $this->endWidget(); ?>