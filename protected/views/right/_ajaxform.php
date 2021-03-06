<?php $form_id = $model->scenario=='update'?'right-form-update':'right-form'; ?>
<?php $action = $model->scenario=='update'?Yii::app()->createUrl('/right/update/', array('id'=>$model->id)):Yii::app()->createUrl('/right/create'); ?>
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

        <?php echo $form->textFieldRow($model, 'key'); ?>
        <?php echo $form->textFieldRow($model, 'name'); ?>
        <?php echo $form->dropDownListRow($model, 'role_ids', CHtml::listData(Role::model()->findAll(), 'id', 'name'), array('multiple'=>'multiple','key'=>'role_ids', 'class'=>'multiselect')); ?>
</div>

<div class="modal-footer">
    <?php echo CHtml::htmlButton('<i class="icon-ok icon-white" ></i> Salva', array('class'=>'btn btn-primary', 'type'=>'submit')); ?>    
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>

<?php $this->endWidget(); ?>

<?php $this->widget(
      'application.extensions.emultiselect.EMultiSelect',
      array('options'=> array('sortable'=>false, 'searchable'=>true, 'height'=>140))
);?>