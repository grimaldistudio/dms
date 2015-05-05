<?php $this->pageTitle = "Documento ".$model->getTitle(); ?>
<?php $this->breadcrumbs = array('links'=> array('Documenti'=>array('/document/search', 'doc_type'=>$model->main_document_type), 'Modifica')); ?>
<h1><?php echo $this->pageTitle; ?></h1>
<?php
$this->renderPartial('/documentright/_form', array('model'=>$model));
?>

<div class="row">
<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'document-update-form',
    'htmlOptions'=>array('class'=>'well span6 pull-left'),
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'validateOnChange'=>false,
        'inputContainer'=>'div.control-group',
    )
)); ?>
    
<?php if(Yii::app()->user->isAdmin() || $model->creator_id == Yii::app()->user->id): ?>    
    <?php if($this->isAllowed('document', 'updaterights')) echo CHtml::htmlButton('<i class="icon-share icon-white"></i> Condividi', array('class'=>'btn btn-primary pull-right open_document_rights')); ?>
<?php endif; ?>    

<?php echo $form->errorSummary($model);  ?>
<p class="help-block">I campi marcati con <span class="required">*</span> sono obbligatori.</p>

<?php 
$disabled = "disabled";
if($model->scenario == 'publish_admin')
    $disabled = "";
?>
<?php echo $form->textFieldRow($model, 'identifier', array('disabled'=>$disabled)); ?>
<?php echo $form->textFieldRow($model, 'publication_number', array('disabled'=>$disabled)); ?>
<?php echo $form->textFieldRow($model, 'name', array('disabled'=>$disabled)); ?>

<?php echo $form->hiddenField($model, 'revision'); ?>
<?php echo $form->textAreaRow($model, 'change_description', array('rows'=>5, 'class'=>'span6')) ; ?>

<div class="control-group">
<?php echo $form->labelEx($model, 'description'); ?>
<?php $this->widget('application.extensions.tinymce.ETinyMce', array('model'=>$model, 'attribute'=> 'description', 'useSwitch'=>false, 'language'=>'it')); ?>
<?php echo $form->error($model, 'description'); ?>
</div>

<div class="control-group">
<?php echo $form->labelEx($model, 'tagsname'); ?>
<?php echo $form->textField($model, 'tagsname', array('id'=>'tagsname')); ?>
<?php echo $form->error($model, 'tagsname'); ?>
<p class="help-block">Inserisci al massimo 5 parole che identificano al meglio il documento ad es. "anagrafe, certificato, richiesta, nascita"</p>
</div>

<div class="control-group">
    <?php echo $form->labelEx($model, 'entity'); ?>
    <?php echo $form->textField($model, 'entity'); ?>
    <?php echo $form->error($model, 'entity'); ?>
    <p class="help-block">Lasciare vuoto se l'ente corrisponde a <?php echo Yii::app()->params['entity'] ;?></p>
</div>

<?php echo $form->textFieldRow($model, 'proposer_service'); ?>

<?php echo $form->textFieldRow($model, 'act_number'); ?>

<?php echo $form->textFieldRow($model, 'act_date', array('class' => 'date_field', 'value'=>$model->act_date?date('d/m/Y', is_int($model->act_date)?$model->act_date:strtotime($model->act_date)):'')); ?>

<?php echo $form->textFieldRow($model, 'publication_date_from', array('class' => 'date_field', 'value'=>$model->publication_date_from?date('d/m/Y', is_int($model->publication_date_from)?$model->publication_date_from:strtotime($model->publication_date_from)):'')); ?>

<?php echo $form->textFieldRow($model, 'publication_date_to', array('class' => 'date_field', 'value'=>$model->publication_date_to?date('d/m/Y', is_int($model->publication_date_to)?$model->publication_date_to:strtotime($model->publication_date_to)):'')); ?>

<?php echo $form->dropDownListRow($model, 'document_type', $model->getTypeOptions()); ?>


<?php
if (Yii::app()->user->hasRole(4)) echo "OK"; 

?>

<?php echo $form->checkBoxRow($model, 'publication_requested'); ?>

<?php echo $form->checkBoxRow($model, 'sync_file'); ?>

<?php echo $form->checkBoxRow($model, 'is_visible_to_all'); ?>
<span class="help-block">Se si spunta questa opzione, il documento sarà visibile in lettura a tutti gli utenti del sistema.</span>
<br/>

<div class="buttons_bar">
<?php echo CHtml::htmlButton('<i class="icon-ok icon-white"></i> Salva', array('class'=>'btn btn-primary', 'type'=>'submit')); ?>
    <span>&nbsp;</span>
<?php if(Yii::app()->user->isAdmin() || $model->creator_id == Yii::app()->user->id): ?>    
    <?php if($this->isAllowed('document', 'updaterights')) echo CHtml::htmlButton('<i class="icon-share icon-white"></i> Condividi', array('class'=>'btn btn-primary open_document_rights')); ?>
<?php endif; ?>
</div>
<?php $this->endWidget(); ?>


<?php $this->renderPartial('_preview', array(
                            'total_pages'=>$model->num_pages, 
                            'full_size_url'=> array('/document/viewpdf', 'id'=>$model->id), 
                            'preview_url'=>Yii::app()->createUrl('/document/previewdoc', array('id'=>$model->id,'t'=>time()))
                            )); ?>

</div>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'sender_create_dialog',
    'htmlOptions'=>array('class'=>'hide span7', 'style'=>'height: auto; max-height: none;'),
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
Yii::app()->clientScript->registerScript('document-update-form', "

    $.datepicker.setDefaults( $.datepicker.regional['it'] );

    $( '.date_field' ).datepicker({
            showAnim: 'fold',
            dateFormat: 'dd/mm/yy'
    });
    
   var priorities = ".CJSON::encode($model->getPriorityOptions()).";
       
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