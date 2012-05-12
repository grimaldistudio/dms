<?php $this->pageTitle = $scenario=='archive'?"Archivia Documento":"Protocolla Documento"; ?>
<?php $this->breadcrumbs = array('links'=> array('Documenti'=>'/document/pending', $scenario=='archive'?'Archivia':'Protocolla')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<div class="row">
<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'protocol-form',
    'htmlOptions'=>array('class'=>'well span8 pull-left'),
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'validateOnChange'=>false,
        'inputContainer'=>'div.control-group',
    )
)); ?>
<?php echo $form->errorSummary($model);  ?>
<p class="help-block">I campi marcati con <span class="required">*</span> sono obbligatori.</p>

<?php if($scenario!='archive'): ?>
<?php echo $form->textFieldRow($model, 'identifier'); ?>
<?php endif; ?>
<?php echo $form->textFieldRow($model, 'name'); ?>
<?php echo $form->textFieldRow($model, 'subject', array('class'=>'span6')); ?>

<div class="control-group">
<?php echo $form->labelEx($model, 'description'); ?>
<?php $this->widget('application.extensions.tinymce.ETinyMce', array('model'=>$model, 'attribute'=> 'description', 'useSwitch'=>false, 'language'=>'it')); ?>
<?php echo $form->error($model, 'description'); ?>
</div>

<?php echo $form->dropDownListRow($model, 'document_type', $model->getTypeOptions()); ?>
<?php echo $form->checkBoxRow($model, 'comments_enabled'); ?>

<div class="control-group">
<?php echo $form->label($model, 'priority'); ?>
<span id="priority"><?php echo $model->getPriorityDesc(); ?></span>
<?php echo $form->hiddenField($model, 'priority', array('id'=>'priority_field')); ?>
<?php $this->widget('zii.widgets.jui.CJuiSlider', array(
    'value'=>$model->priority,
    'options'=>array(
        'min'=>Document::LOW_PRIORITY,
        'max'=>Document::VERY_HIGH_PRIORITY,
        'step'=>1,
        'slide'=>'js:function(event, ui){ $(\'#priority_field\').val( ui.value ); $(\'#priority\').text( priorities[ui.value] ); }'
    ),
    'htmlOptions'=>array(
        'style'=>'width: 210px'
    )
)); ?>
<?php echo $form->error($model, 'priority'); ?>
</div>

<div class="control-group">
<?php echo $form->labelEx($model, 'tagsname'); ?>
<?php echo $form->textField($model, 'tagsname', array('id'=>'tagsname')); ?>
<?php echo $form->error($model, 'tagsname'); ?>
<p class="help-block">Inserisci al massimo 5 parole che identificano al meglio il documento ad es. "anagrafe, certificato, richiesta, nascita"</p>
</div>

<div class="control-group">
<?php echo $form->labelEx($model, 'sender_id'); ?>
<?php echo $form->hiddenField($model, 'sender_id', array('id'=>'sender_id')); ?>
<?php echo $form->textArea($model, 'senderaddress', array('id'=>'senderaddress', 'readonly'=>'readonly', 'class'=>'span6')); ?>
<br/>
<?php echo $form->textField($model, 'sendername', array('id'=>'sendername')); ?>
<?php echo $form->error($model, 'sender_id'); ?>
</div>

<?php echo $form->textFieldRow($model, 'date_received', array('id'=>'date_received')); ?>

<?php echo CHtml::htmlButton('<i class="icon-ok icon-white"></i> Protocolla', array('class'=>'btn btn-primary', 'type'=>'submit')); ?>

<?php $this->endWidget(); ?>


<?php $this->renderPartial('_preview', array(
                                            'total_pages'=>$total_pages, 
                                            'full_size_url'=> array('/document/previewpdf', 'group_id'=>$group_id, 'document_name'=>$document_name), 
                                            'preview_url'=>Yii::app()->createUrl('/document/preview', array('group_id'=>$group_id, 'document_name'=>$document_name)
                                            ))); ?>

</div>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'sender_create_dialog',
    'htmlOptions'=>array('class'=>'hide span9', 'style'=>'height: auto; max-height: none;'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Aggiungi Mittente</h3>
</div>
<?php $this->renderPartial('//sender/_ajaxform', array('model'=>new Sender('create'))); ?>
<?php $this->endWidget(); ?>


<?php
Yii::app()->clientScript->registerPackage('tagit');

Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-i18n.min.js');

Yii::app()->clientScript->registerScript('protocol-form', "
    
    var priorities = ".CJSON::encode($model->getPriorityOptions()).";
    
    $.datepicker.setDefaults( $.datepicker.regional['it'] );

    $( '#date_received' ).datepicker({
            defaultDate: new Date(),
            showAnim: 'fold',
            dateFormat: 'dd/mm/yy'
    });  

       $('#tagsname').tagit({
            allowSpaces: false,
            tagSource: function(search, showChoices) {
                var that = this;
                $.ajax({
                    cache: false,
                    url: '".Yii::app()->createUrl('/tag/autocomplete')."',
                    type: 'GET',
                    dataType: 'json',
                    data: search,
                    success: function(choices) {
                        if(choices.length>0)
                            showChoices(that._subtractArray(choices, that.assignedTags()));
                    }
                });
            }        
       });
       
    $( '#sendername' ).autocomplete({
            minLength: 2,
            source: function(req, add){
            $.ajax({
                    url: '".Yii::app()->createUrl('/sender/autocomplete')."',
                    dataType: 'json',
                    type: 'GET',
                    cache: false,
                    data: req,
                    success: function(data){
                            if(data.length>0)
                                add(data);
                            else{
                                data.push({
                                    value: 0,
                                    label: 'Nessun risultato - Aggiungi mittente'
                                });
                                add(data);
                            }
                    },
                    error: function(jqhr, errorStatus, errorThrown){
                        alert('Impossibile recuperare la lista dei mittenti');
                    }
            });
        },
        focus: function( event, ui ) {
            if( ui.item.value>0)
            {
                $( '#sendername' ).val( ui.item.label );
                $( '#senderaddress' ).val( ui.item.address );                
            }
            return false;
        },
        select: function( event, ui ) {
            if(ui.item.value == 0){
                $('#sender_create_dialog').modal('show');
            }
            else{
                $( '#sendername' ).val( ui.item.label );
                $( '#sender_id' ).val( ui.item.value );
                $( '#senderaddress' ).val( ui.item.address );                            
            }
            return false;
        },
        change: function( event, ui ) {
            if ( !ui.item ) {
                $( '#sender_id' ).val( '' );
                $( '#sendername' ).val( '' );
                $( '#senderaddress' ).val( '' );                
            }
        }			
    });

",  CClientScript::POS_READY);

Yii::app()->clientScript->registerScript('senderyiiactiveform', "
   function submitAjaxForm(form, data, hasError)
   {
        var form_id =$(form).attr('id');
        var summaryID = $(form).yiiactiveform.getSettings(form).summaryID;
        if(!hasError)
        {
            $(form).ajaxSubmit({
                dataType: 'json',
                timeout: 10000,                
                beforeSubmit: function()
                {
                    $('#'+summaryID).html('');
                    $('#'+summaryID).hide();
                },
                success: function(data)
                {
                    if(data.success==1)
                    {
                        $('#sender_create_dialog').modal('hide');
                        $('#sendername').val(data.label);
                        $('#sender_id').val(data.id);
                        $('#senderaddress').val(data.address);                        
                        $(form).resetForm();
                    }
                    else
                    {
                        listErrors(summaryID, data.errors);
                        $('#'+summaryID).show();
                    }
                },
                error: function(xhr)
                {
                    $('#'+summaryID).html('Impossibile completare l\'operazione');
                    $('#'+summaryID).show();
                }
            });
        }
        return false;
   }
       
", CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile('/js/jquery.form.js');
?>