<?php $this->pageTitle = "Rubrica Mittenti"; ?>
<?php $this->breadcrumbs = array('links'=> array('Mittenti'=>'/sender/')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<div class="btn-toolbar">
<?php echo CHtml::link('<i class="icon-plus icon-white"></i> Aggiungi', array('/sender/create'), array('class'=>'btn btn-primary create')); ?>
</div>

<?php $this->widget('bootstrap.widgets.BootGridView', array(
    'id'=>'senders_gridview',
    'dataProvider'=>$model->search(),
    'template'=>"{items}\n{pager}",
    'itemsCssClass'=>'table table-striped table-bordered table-condensed',
    'columns'=>array(
        array('name'=>'name'),
        array(
            'header'=>'Indirizzo', 
            'value'=>'$data->getFullAddress()',
            'filter'=>false, 
            'sortable'=>false
        ),
        array(
            'filter' => false,
            'name' => 'date_created',
            'type' => 'datetime',
            'value' => 'strtotime($data->date_created)',
        ),
        array(
            'filter' => false,
            'name' => 'last_updated',
            'type' => 'datetime',
            'value' => 'strtotime($data->last_updated)',
        ),    
        array(
            'name' => 'status',
            'filter' => $model->getStatusArray(),
            'value' => '$data->getStatusDesc()'                   
        ),                
        array(
            'class'=>'bootstrap.widgets.BootButtonColumn',
            'htmlOptions'=>array('style'=>'width: 50px'),
        ),
    ),
    'filter'=>$model
)); ?>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'sender_view_dialog',
    'htmlOptions'=>array('class'=>'hide'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Dettagli Mittente</h3>
</div>
<div class="modal-body">

</div>
<div class="modal-footer">
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>
<?php $this->endWidget(); ?>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'sender_create_dialog',
    'htmlOptions'=>array('class'=>'hide span7', 'style'=>'height: auto; max-height: none;'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Aggiungi Mittente</h3>
</div>
<?php $this->renderPartial('_ajaxform', array('model'=>new Sender('create'))); ?>
<?php $this->endWidget(); ?>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'sender_update_dialog',
    'htmlOptions'=>array('class'=>'hide span7', 'style'=>'height: auto; max-height: none;'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Modifica Mittente</h3>
</div>
<div id="sender_update_container" style="width: 100%"></div>
<?php $this->endWidget(); ?>

<?php
Yii::app()->clientScript->registerScript('sender-list-buttons', "
    
   $('a.create').on('click', function(e)
   {
        e.preventDefault();
        $('#sender_create_dialog').modal('show');
   });
   
   $('a.update').live('click', function(e)
   {
        e.preventDefault();
        var update_url = $(e.target.parentNode).attr('href');
        $.ajax({
            'url': update_url,
            'cache': false,
            'type':'GET',
            'dataType': 'html',
            success: function(data)
            {
                $('#sender_update_container').html(data);
                $('#sender_update_dialog').modal('show');                
            },
            error: function(xhr)
            {
                alert('Impossibile completare l\'operazione');
            }
        });

   });
   

   $('a.view').live('click', function(e){
        e.preventDefault();
        var view_url = $(e.target.parentNode).attr('href'); 
        $.ajax({
            'url': view_url,
            'cache': false,
            'type': 'GET',
            'dataType': 'html',
            'success': function(data)
            {
                $('#sender_view_dialog div.modal-body').html(data);
                $('#sender_view_dialog').modal('show');
            },
            'error': function(xhr,errorstatus,errothrown)
            {
                alert('Impossibile recuperare i dettagli dell\'ruolo');
            }
        });
   });
");

Yii::app()->clientScript->registerScript('senderyiiactiveform', "
   function submitAjaxForm(form, data, hasError)
   {
        var form_id =$(form).attr('id');
        var summaryID = $(form).yiiactiveform.getSettings(form).summaryID;
        if(!hasError)
        {
            $(form).ajaxSubmit({
                'dataType': 'json',
                'timeout': 10000,                
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
                        $('#sender_update_dialog').modal('hide');                        
                        $(form).resetForm();
                        $.fn.yiiGridView.update('senders_gridview');
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