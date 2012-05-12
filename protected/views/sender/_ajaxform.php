<?php $form_id = $model->scenario=='update'?'sender-form-update':'sender-form'; ?>
<?php $action = $model->scenario=='update'?Yii::app()->createUrl('/sender/update/', array('id'=>$model->id)):Yii::app()->createUrl('/sender/create'); ?>
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
<?php echo $form->dropDownListRow($model,'country_id',CHtml::listData(Country::model()->findAll(), 'id', 'name'), array('empty'=>'Seleziona Nazione')); ?>
<div id="italy_address" <?php if($model->country_id!=110):?>style="display:none"<?php endif; ?>>
        <div class="control-group">
            <?php echo $form->labelEx($model,'city_id'); ?>
            <?php echo $form->hiddenField($model, 'city_id')?>
            <?php echo $form->textField($model, 'city_autocomplete', array('value'=>CHtml::encode($model->citye))); ?>
            <?php echo $form->error($model,'city_id'); ?>
        </div>
</div>

<div id="foreign_address" <?php if($model->country_id<=0 || $model->country_id==110):?>style="display:none"<?php endif; ?>>
    <?php echo $form->textFieldRow($model,'city'); ?>
    <?php 	
            $options = array('class' => 'foreign_address_field');	
            if($model->country_id<=0 || $model->country_id==110)
                $options['readonly'] = 'readonly';
    ?>
    <?php echo $form->textFieldRow($model,'province', $options); ?>
    <?php echo $form->textFieldRow($model,'postal_code', $options); ?>
</div>

    
<?php echo $form->textAreaRow($model, 'address'); ?>
</div>

<div class="modal-footer">
    <?php echo CHtml::htmlButton('<i class="icon-ok icon-white" ></i> Salva', array('class'=>'btn btn-primary', 'type'=>'submit')); ?>    
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>

<?php $this->endWidget(); ?>

<?php Yii::app()->clientScript->registerScript('sender-form-controls', "
        var country_id_elem = $('#".CHtml::activeId($model,'country_id')."');
   	country_id_elem.change(function()
	{
		var selected_country = country_id_elem.val();
		if(selected_country>0)
		{
			if(selected_country==110) // Italy
			{
				$('#italy_address').show();
				$('#foreign_address').hide();
				$('.foreign_address_field').attr('readonly', 'readonly');
				$('.foreign_address_field').val('');							
				$('#".CHtml::activeId($model, 'city')."').val('');						
				$('#".CHtml::activeId($model, 'city_autocomplete')."').val('');										
			}
			else
			{
				$('#italy_address').hide();
				$('#foreign_address').show();
				$('.foreign_address_field').removeAttr('readonly');							
			}

		}
		else{
			$('#italy_address').hide();
			$('#foreign_address').hide();
			$('.foreign_address_field').attr('readonly', 'readonly');
			$('.foreign_address_field').val('');							
			$('#".CHtml::activeId($model, 'city')."').val('');				    	
                        $('#".CHtml::activeId($model, 'city_autocomplete')."').val('');															
		}
	});

	$( '#".CHtml::activeId($model, 'city_autocomplete')."' ).autocomplete({
		minLength: 2,
		source: '".Yii::app()->createUrl('/geo/cities')."',
		focus: function( event, ui ) {
                    if( ui.item.value>0)
                        $( '#".CHtml::activeId($model, 'city_autocomplete')."' ).val( ui.item.label );
                    return false;
		},
		select: function( event, ui ) {
                    if(ui.item.value >0 ){
                        $( '#".CHtml::activeId($model, 'city_autocomplete')."' ).val( ui.item.label );
                        $( '#".CHtml::activeId($model, 'city_id')."' ).val( ui.item.value );
                    }
                    return false;
		},
    		change: function( event, ui ) {
                if ( !ui.item ) {
                    $( '#".CHtml::activeId($model, 'city_autocomplete')."' ).val( '' );
                    $( '#".CHtml::activeId($model, 'city_id')."' ).val( '' );
                }
	    }			
	}); 
");
?>