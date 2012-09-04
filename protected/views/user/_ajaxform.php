<?php $form_id = $model->scenario=='update'?'user-form-update':'user-form'; ?>
<?php $action = $model->scenario=='update'?Yii::app()->createUrl('/user/update/', array('id'=>$model->id)):Yii::app()->createUrl('/user/create'); ?>
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

    <div class="row">
        <div class="span4">
        <?php echo $form->textFieldRow($model, 'email'); ?>
        <?php echo $form->textFieldRow($model, 'firstname'); ?>
        <?php echo $form->textFieldRow($model, 'lastname'); ?>
        <?php echo $form->textFieldRow($model, 'telephone'); ?>
        <?php if(Yii::app()->user->isSuperadmin()): ?> 
        <?php echo $form->checkBoxRow($model, 'is_admin');  ?>
        <?php endif; ?>
        </div>

        <div class="span4">
        <?php echo $form->dropDownListRow($model, 'group_ids', CHtml::listData(Group::model()->findAll(), 'id', 'name'), array('multiple'=>'multiple', 'key'=>'group_ids', 'class'=>'multiselect')); ?>
        <?php echo $form->dropDownListRow($model, 'role_ids', CHtml::listData(Role::model()->findAll(), 'id', 'name'), array('multiple'=>'multiple','key'=>'role_ids', 'class'=>'multiselect')); ?>
        </div>
    </div>
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