<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'personal-info-form',
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
<div class="alert alert-success" id="personal-info-form-success" style="display:none">
    <span id="personal-info-form-success-content"></span>
</div>
<p class="help-block">I campi marcati con <span class="required">*</span> sono obbligatori.</p>

<?php echo $form->textFieldRow($model, 'firstname'); ?>
<?php echo $form->textFieldRow($model, 'lastname'); ?>
<?php echo $form->textFieldRow($model, 'telephone'); ?> 
<?php if($model->is_admin): ?>
<div class="control-group">
    <label><b>Amministratore</b></label>
</div>
<?php else: ?>
<div class="control-group">
<label>Gruppi</label>
<ul>
    <?php foreach($model->groups as $group): ?>
    <li><?php echo $group->name; ?></li>
    <?php endforeach; ?>
</ul>

<label>Ruoli</label>
<ul>
    <?php foreach($model->roles as $role): ?>
    <li><?php echo $role->name; ?></li>
    <?php endforeach; ?>
</ul>
</div>
<?php endif; ?>
<?php echo CHtml::htmlButton('<i class="icon-ok" ></i> Salva', array('class'=>'btn', 'type'=>'submit')); ?>
<?php $this->endWidget(); ?>