<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'document_upload_dialog',
    'htmlOptions'=>array('class'=>'hide'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Carica Nuovo Documento</h3>
</div>
<div class="modal-body">
<?php 
    $form=$this->beginWidget('bootstrap.widgets.BootActiveForm', array(
                'id'=>'document-upload-form',
                'action'=>array('/document/upload'),
                'htmlOptions'=>array('class'=>'well'),    
		'enableClientValidation' => true,
		'clientOptions' => array(
			'validateOnSubmit' => true,
			'validateOnChange' => false,
			'afterValidate' => 'js:submitDocumentUploadForm',
                        'inputContainer'=>'div.control-group',
		),
		'htmlOptions'=>array('enctype' => 'multipart/form-data')
		)
	); 
?>

<?php echo $form->errorSummary($model); ?>
	
<?php echo $form->fileFieldRow($model, 'document_file'); ?>

</div>

<div class="modal-footer">
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
    <?php echo CHtml::htmlButton('<i class="icon-ok icon-white"></i> Carica', array('class'=>'btn btn-primary upload-new-document', 'type'=>'submit')); ?>
</div>
<?php $this->endWidget(); ?>

<?php $this->endWidget(); ?>

<?php Yii::app()->clientScript->registerScript('document-upload-scripts', "
    $('a.upload-new-document').live('click', function(e)
    {
        e.preventDefault();
        $('#document_upload_dialog').modal('show');
    });
"
);
?>
<?php
Yii::app()->clientScript->registerScript('documentuploadform', "
   function submitDocumentUploadForm(form, data, hasError)
   {
        var form_id =$(form).attr('id');
        var summaryID = $(form).yiiactiveform.getSettings(form).summaryID;
        if(!hasError)
        {
            $(form).ajaxSubmit({
                timeout: 20000,                
                beforeSubmit: function()
                {
                    $('#'+summaryID).html('');
                    $('#'+summaryID).hide();
                },
                success: function(rawdata)
                {
                    try{
                        var data = jQuery.parseJSON(rawdata);
                        if(data.success==1)
                        {
                            $('#document_upload_dialog').modal('hide');
                            $(form).resetForm();
                            $(form).clearForm();
                            window.location = data.success_url;
                        }
                        else
                        {
                            listErrors(summaryID, data.errors);
                            $('#'+summaryID).show();
                        }
                    }
                    catch(e)
                    {
                        $('#'+summaryID).html('Impossibile completare l\'operazione');
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