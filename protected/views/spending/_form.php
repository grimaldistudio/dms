<?php 
if($tmp==false)
    $id_array = array('id'=>$model->id);
else
    $id_array = array();
?>
<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'spending-form',
    'htmlOptions'=>array('class'=>'well'),
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'validateOnChange'=>false,
        'inputContainer'=>'div.control-group',
    )
)); ?>
<?php echo $form->errorSummary($model);  ?>
<p class="help-block">I campi marcati con <span class="required">*</span> sono obbligatori.</p>

<div class="row">
    <div class="span8">
<?php echo $form->textFieldRow($model, 'title'); ?>
<?php echo $form->textAreaRow($model, 'receiver'); ?>
<?php echo $form->textFieldRow($model, 'amount'); ?>
<?php echo $form->textFieldRow($model, 'attribution_norm'); ?>
<?php echo $form->textFieldRow($model, 'attribution_mod'); ?>
<?php echo $form->textFieldRow($model, 'office'); ?>
<?php echo $form->textFieldRow($model, 'employee'); ?>
<?php echo $form->textFieldRow($model, 'spending_date', array('class' => 'date_field', 'value'=>$model->spending_date>0?date('d/m/Y', is_int($model->spending_date)?$model->spending_date:strtotime($model->spending_date)):'')); ?>

        <div class="control-group">
            <?php echo $form->labelEx($model, 'description'); ?>
            <?php $this->widget('application.extensions.tinymce.ETinyMce', array('model'=>$model, 'attribute'=> 'description', 'useSwitch'=>false, 'language'=>'it')); ?>
            <?php echo $form->error($model, 'description'); ?>
        </div>

        <?php echo $form->checkBoxRow($model, 'publication_requested'); ?>
        
        <?php echo CHtml::htmlButton('<i class="icon-ok icon-white" ></i> Salva', array('class'=>'btn btn-primary', 'type'=>'submit')); ?>        

    </div>
    
    <div class="span4">
        
        <div id="cv-box">
            <p>CV</p>
            <?php echo CHtml::button('Carica', array('id'=>'cv-upload-button', 'class'=>'btn btn-primary')); ?>
            <div id="cv-container">
                <?php if($model->hasCV($tmp)): ?>
                    <?php echo $model->getCVName($tmp); ?> (<?php echo $model->getCVSize($tmp); ?>) KB <?php echo CHtml::link('  ', array_merge(array('/spending/deletecv'), $id_array), array('class'=>'icon-trash delete-file-button')); ?>
                <?php endif; ?>
            </div>
        </div>

        <br/>
        <br/>
        
        <div id="contract-box">
            <p>Contratto</p>
            <?php echo CHtml::button('Carica', array('id'=>'contract-upload-button', 'class'=>'btn btn-primary')); ?>
            <div id="contract-container">
                <?php if($model->hasContract($tmp)): ?>
                    <?php echo $model->getContractName($tmp); ?> (<?php echo $model->getContractSize($tmp); ?> KB) <?php echo CHtml::link('  ', array_merge(array('/spending/deletecontract'), $id_array), array('class'=>'icon-trash delete-file-button')); ?>
                <?php endif; ?>
            </div>
        </div>
             
        <br/>
        <br/>
        
        <div id="project-box">
            <p>Progetto</p>
            <?php echo CHtml::button('Carica', array('id'=>'project-upload-button', 'class'=>'btn btn-primary')); ?>
            <div id="project-container">
                <?php if($model->hasProject($tmp)): ?>
                    <?php echo $model->getProjectName($tmp); ?> (<?php echo $model->getProjectSize($tmp); ?> KB) <?php echo CHtml::link('  ', array_merge(array('/spending/deleteproject'), $id_array), array('class'=>'icon-trash delete-file-button')); ?>
                <?php endif; ?>
            </div>
        </div>
        
        <br/>
        <br/>
        
        <div id="capitulate-box">
            <p>Capitolato</p>
            <?php echo CHtml::button('Carica', array('id'=>'capitulate-upload-button', 'class'=>'btn btn-primary')); ?>
            <div id="capitulate-container">
                <?php if($model->hasCapitulate($tmp)): ?>
                    <?php echo $model->getCapitulateName($tmp); ?> (<?php echo $model->getCapitulateSize($tmp); ?> KB) <?php echo CHtml::link('  ', array_merge(array('/spending/deletecapitulate'), $id_array), array('class'=>'icon-trash delete-file-button')); ?>
                <?php endif; ?>
            </div>
        </div>

        <br/>
        <br/>
        
        <div id="other-box">
            <p>Altra documentazione</p>
            <div id="other-container">
                <?php foreach($model->listOtherDocuments($tmp) as $other): ?>
                    <div class="other-file-item"><?php echo $other; ?> (<?php echo $model->getOtherSize($other, $tmp); ?> KB) <?php echo CHtml::link('  ', array_merge(array('/spending/deleteother', 'filename'=>$other), $id_array), array('class'=>'icon-trash delete-other-file-button')); ?></div>
                <?php endforeach; ?>
            </div>
            <?php $style = ''; ?>
            <?php if(!$model->canAddNewOther($tmp)) $style = 'display:none'; ?>
            <?php echo CHtml::button('Carica', array('id'=>'other-upload-button', 'class'=>'btn btn-primary', 'style'=>$style)); ?>
        </div>
    </div>
    
</div>
<?php $this->endWidget(); ?>


<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'cv-upload-dialog',
    'htmlOptions'=>array('class'=>'hide'),
)); ?>
<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'cv-upload-form',
    'action'=>array_merge(array('/spending/uploadcv'), $id_array),
    'htmlOptions'=>array('class'=>'well', 'enctype'=>'multipart/form-data'),
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'validateOnChange'=>false,
        'inputContainer'=>'div.control-group',
        'afterValidate'=>'js:uploadCV'
    )
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Carica file Curriculum Vitae</h3>
</div>
<div class="modal-body">
    <div id="cv-error-summary" class="alert alert-block alert-error" style="display:none"><ul></ul></div>
    <?php echo $form->fileFieldRow($model, 'cv_file'); ?>

</div>
<div class="modal-footer">
    <?php echo CHtml::htmlButton('<i class="icon-ok icon-white" ></i> Carica', array('class'=>'btn btn-primary', 'type'=>'submit')); ?>        
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>
<?php $this->endWidget(); ?>
<?php $this->endWidget(); ?>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'contract-upload-dialog',
    'htmlOptions'=>array('class'=>'hide'),
)); ?>
<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'contract-upload-form',
    'action'=>array_merge(array('/spending/uploadcontract'), $id_array),
    'htmlOptions'=>array('class'=>'well', 'enctype'=>'multipart/form-data'),
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'validateOnChange'=>false,
        'inputContainer'=>'div.control-group',
        'afterValidate'=>'js:uploadContract'
    )
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Carica file contratto</h3>
</div>
<div class="modal-body">
    <div id="contract-error-summary" class="alert alert-block alert-error" style="display:none"><ul></ul></div>    
    <?php echo $form->fileFieldRow($model, 'contract_file'); ?>

</div>
<div class="modal-footer">
    <?php echo CHtml::htmlButton('<i class="icon-ok icon-white" ></i> Carica', array('class'=>'btn btn-primary', 'type'=>'submit')); ?>        
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>
<?php $this->endWidget(); ?>
<?php $this->endWidget(); ?>


<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'project-upload-dialog',
    'htmlOptions'=>array('class'=>'hide'),
)); ?>
<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'project-upload-form',
    'action'=>array_merge(array('/spending/uploadproject'), $id_array),
    'htmlOptions'=>array('class'=>'well', 'enctype'=>'multipart/form-data'),
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'validateOnChange'=>false,
        'inputContainer'=>'div.control-group',
        'afterValidate'=>'js:uploadProject'
    )
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Carica file progetto</h3>
</div>
<div class="modal-body">
    <div id="project-error-summary" class="alert alert-block alert-error" style="display:none"><ul></ul></div>    
    <?php echo $form->fileFieldRow($model, 'project_file'); ?>
</div>
<div class="modal-footer">
    <?php echo CHtml::htmlButton('<i class="icon-ok icon-white" ></i> Carica', array('class'=>'btn btn-primary', 'type'=>'submit')); ?>        
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>
<?php $this->endWidget(); ?>
<?php $this->endWidget(); ?>


<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'capitulate-upload-dialog',
    'htmlOptions'=>array('class'=>'hide'),
)); ?>
<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'capitulate-upload-form',
    'action'=>array_merge(array('/spending/uploadcapitulate'), $id_array),
    'htmlOptions'=>array('class'=>'well', 'enctype'=>'multipart/form-data'),
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'validateOnChange'=>false,
        'inputContainer'=>'div.control-group',
        'afterValidate'=>'js:uploadCapitulate'
    )
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Carica file capitolato</h3>
</div>
<div class="modal-body">
    <div id="capitulate-error-summary" class="alert alert-block alert-error" style="display:none"><ul></ul></div>
    <?php echo $form->fileFieldRow($model, 'capitulate_file'); ?>
</div>
<div class="modal-footer">
    <?php echo CHtml::htmlButton('<i class="icon-ok icon-white" ></i> Carica', array('class'=>'btn btn-primary', 'type'=>'submit')); ?>        
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>
<?php $this->endWidget(); ?>
<?php $this->endWidget(); ?>


<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'other-upload-dialog',
    'htmlOptions'=>array('class'=>'hide'),
)); ?>
<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'other-upload-form',
    'action'=>array_merge(array('/spending/uploadother'), $id_array),
    'htmlOptions'=>array('class'=>'well', 'enctype'=>'multipart/form-data'),
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'validateOnChange'=>false,
        'inputContainer'=>'div.control-group',
        'afterValidate'=>'js:uploadOther'
    )
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Carica altra documentazione</h3>
</div>
<div class="modal-body">
    <div id="other-error-summary" class="alert alert-block alert-error" style="display:none"><ul></ul></div>
    <?php echo $form->fileFieldRow($model, 'other_file'); ?>
</div>
<div class="modal-footer">
    <?php echo CHtml::htmlButton('<i class="icon-ok icon-white" ></i> Carica', array('class'=>'btn btn-primary', 'type'=>'submit')); ?>        
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>
<?php $this->endWidget(); ?>
<?php $this->endWidget(); ?>


<?php 
Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-i18n.min.js');
Yii::app()->clientScript->registerScriptFile('/js/jquery.form.js');

Yii::app()->clientScript->registerScript('spending-create-upload-scripts', "
    function uploadCV(form, data, hasError){
        var form_id =$(form).attr('id');
        var summaryID = 'cv-error-summary';
        if(!hasError)
        {
            $(form).ajaxSubmit({
                beforeSubmit: function()
                {
                    $('#'+summaryID).html('');
                    $('#'+summaryID).hide();
                },
                success: function(rawData)
                {
                    console.log(rawData);
                    try
                    {
                        data = $.parseJSON(rawData);
                    }
                    catch(e)
                    {
                        console.log(e);
                        alert('Errore durante il caricamento del file');
                        return false;
                    }
                    
                    if(data.success==1)
                    {
                        $('#cv-upload-dialog').modal('hide');                        
                        $(form).resetForm();
                        // add file to the list
                        addFile('cv-container', data.filename, data.filesize, data.download_url, data.delete_url);
                    }
                    else
                    {
                        listErrors(summaryID, data.errors);
                        $('#'+summaryID).show();
                    }
                },
                error: function(xhr)
                {
                    console.log(xhr);
                    $('#'+summaryID).html('Impossibile completare l\'operazione');
                    $('#'+summaryID).show();
                }
            });
        }
        return false;    
    }
    
    function uploadContract(form, data, hasError){
        var form_id =$(form).attr('id');
        var summaryID = 'contract-error-summary';
        if(!hasError)
        {
            $(form).ajaxSubmit({
                beforeSubmit: function()
                {
                    $('#'+summaryID).html('');
                    $('#'+summaryID).hide();
                },
                success: function(rawData)
                {
                    console.log(rawData);
                    try
                    {
                        data = $.parseJSON(rawData);
                    }
                    catch(e)
                    {
                        console.log(e);
                        alert('Errore durante il caricamento del file');
                        return false;
                    }
                    
                    if(data.success==1)
                    {
                        $('#contract-upload-dialog').modal('hide');                        
                        $(form).resetForm();
                        // add file to the list
                        addFile('contract-container', data.filename, data.filesize, data.download_url, data.delete_url);
                    }
                    else
                    {
                        listErrors(summaryID, data.errors);
                        $('#'+summaryID).show();
                    }
                },
                error: function(xhr)
                {
                    console.log(xhr);
                    $('#'+summaryID).html('Impossibile completare l\'operazione');
                    $('#'+summaryID).show();
                }
            });
        }
        return false;    
    }
    
    function uploadProject(form, data, hasError){
        var form_id =$(form).attr('id');
        var summaryID = 'project-error-summary';
        if(!hasError)
        {
            $(form).ajaxSubmit({
                beforeSubmit: function()
                {
                    $('#'+summaryID).html('');
                    $('#'+summaryID).hide();
                },
                success: function(rawData)
                {
                    console.log(rawData);
                    try
                    {
                        data = $.parseJSON(rawData);
                    }
                    catch(e)
                    {
                        console.log(e);
                        alert('Errore durante il caricamento del file');
                        return false;
                    }
                    
                    if(data.success==1)
                    {
                        $('#project-upload-dialog').modal('hide');                        
                        $(form).resetForm();
                        // add file to the list
                        addFile('project-container', data.filename, data.filesize, data.download_url, data.delete_url);
                    }
                    else
                    {
                        listErrors(summaryID, data.errors);
                        $('#'+summaryID).show();
                    }
                },
                error: function(xhr)
                {
                    console.log(xhr);
                    $('#'+summaryID).html('Impossibile completare l\'operazione');
                    $('#'+summaryID).show();
                }
            });
        }
        return false;    
    }
    
    function uploadCapitulate(form, data, hasError){
        var form_id =$(form).attr('id');
        var summaryID = 'capitulate-error-summary';
        if(!hasError)
        {
            $(form).ajaxSubmit({
                beforeSubmit: function()
                {
                    $('#'+summaryID).html('');
                    $('#'+summaryID).hide();
                },
                success: function(rawData)
                {
                    console.log(rawData);
                    try
                    {
                        data = $.parseJSON(rawData);
                    }
                    catch(e)
                    {
                        console.log(e);
                        alert('Errore durante il caricamento del file');
                        return false;
                    }
                    
                    if(data.success==1)
                    {
                        $('#capitulate-upload-dialog').modal('hide');                        
                        $(form).resetForm();
                        // add file to the list
                        addFile('capitulate-container', data.filename, data.filesize, data.delete_url);
                    }
                    else
                    {
                        listErrors(summaryID, data.errors);
                        $('#'+summaryID).show();
                    }
                },
                error: function(xhr)
                {
                    console.log(xhr);
                    $('#'+summaryID).html('Impossibile completare l\'operazione');
                    $('#'+summaryID).show();
                }
            });
        }
        return false;    
    }
    
    function uploadOther(form, data, hasError){
        var form_id =$(form).attr('id');
        var summaryID = 'other-error-summary';
        if(!hasError)
        {
            $(form).ajaxSubmit({
                beforeSubmit: function()
                {
                    $('#'+summaryID).html('');
                    $('#'+summaryID).hide();
                },
                success: function(rawData)
                {
                    console.log(rawData);
                    try
                    {
                        data = $.parseJSON(rawData);
                    }
                    catch(e)
                    {
                        console.log(e);
                        alert('Errore durante il caricamento del file');
                        return false;
                    }
                    
                    if(data.success==1)
                    {
                        $('#other-upload-dialog').modal('hide');                        
                        $(form).resetForm();
                        // add file to the list
                        addOtherFile('other-container', data.filename, data.filesize, data.delete_url, data.can_add);
                    }
                    else
                    {
                        listErrors(summaryID, data.errors);
                        $('#'+summaryID).show();
                    }
                },
                error: function(xhr)
                {
                    console.log(xhr);
                    $('#'+summaryID).html('Impossibile completare l\'operazione');
                    $('#'+summaryID).show();
                }
            });
        }
        return false;    
    }
    
    function addFile(container_id, filename, filesize, delete_url)
    {
        filesize = filesize/1000.0;
        $('#'+container_id).html('');
        $('#'+container_id).html(filename + ' ('+filesize.toFixed(2)+' KB)'+' <a class=\"icon-trash delete-file-button\" href=\"'+delete_url+'\">   </a>');
    }
    
    function addOtherFile(container_id, filename, filesize, delete_url, can_add)
    {
        filesize = filesize/1000.0;
        if(can_add==1)
            $('#other-upload-button').show();
        else
            $('#other-upload-button').hide();
        $('#'+container_id).append('<div class=\"other-file-item\" >'+filename + ' ('+filesize.toFixed(2)+' KB)'+' <a class=\"icon-trash delete-other-file-button\" href=\"'+delete_url+'\">   </a></div>');
    }
    
", CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript('spending-form', "
    $.datepicker.setDefaults( $.datepicker.regional['it'] );

    $( '.date_field' ).datepicker({
            showAnim: 'fold',
            dateFormat: 'dd/mm/yy'
    });  
    
    $('a.delete-file-button').on('click', function(e){
        e.preventDefault();
        
        if(!confirm('Sei sicuro di voler cancellare il file?'))
            return false;
        
        var link = $(e.currentTarget);
        var delete_url = link.attr('href');
        $.ajax({
            url: delete_url,
            timeout: 10000,
            cache: false,
            dataType: 'json',
            type: 'POST',
            success: function(data){
                if(data.success==1)
                    link.parent().html('');
                else
                    alert('Impossibile cancellare il file');
            },
            error: function(xhr){
                console.log(xhr);
                alert('Impossibile cancellare il file');
            }
        });
        return false;
    });

    $('a.delete-other-file-button').live('click', function(e){
        e.preventDefault();
        
        if(!confirm('Sei sicuro di voler cancellare il file?'))
            return false;
            
        var link = $(e.currentTarget);
        var delete_url = link.attr('href');
        $.ajax({
            url: delete_url,
            timeout: 10000,
            cache: false,
            dataType: 'json',
            type: 'POST',
            success: function(data){
                if(data.success==1){
                    $('#other-upload-button').show();
                    link.parent().remove();
                }
                else
                    alert('Impossibile cancellare il file');
            },
            error: function(xhr){
                console.log(xhr);
                alert('Impossibile cancellare il file');
            }
        });
        return false;
    });

    $('#cv-upload-button').on('click', function(e){
        $('#cv-upload-dialog').modal('show');
    });
    
    $('#project-upload-button').on('click', function(e){
        $('#project-upload-dialog').modal('show');
    });
    
    $('#contract-upload-button').on('click', function(e){
        $('#contract-upload-dialog').modal('show');
    });
    
    $('#capitulate-upload-button').on('click', function(e){
        $('#capitulate-upload-dialog').modal('show');
    });
    
    $('#other-upload-button').on('click', function(e){
        $('#other-upload-dialog').modal('show');
    });
 ");   
?>