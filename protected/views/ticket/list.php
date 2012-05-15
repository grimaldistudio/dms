<?php $this->pageTitle = "Elenco Tickets"; ?>
<?php $this->breadcrumbs = array('links'=> array('Tickets'=>'/ticket/', 'Elenco')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->widget('bootstrap.widgets.BootGridView', array(
    'id'=>'tickets_gridview',
    'dataProvider'=>$model->search(),
    'template'=>"{items}\n{pager}",
    'itemsCssClass'=>'table table-striped table-bordered table-condensed',
    'columns'=>array(
        array('name'=>'id'),
        array('name'=>'document_title',
              'value'=>'$data->document->getTitle()'),
        array('name'=>'user_email',
              'value'=>'$data->user->email'),
        array('name'=>'replier_email',
              'value'=>'$data->replier_id>0?$data->replier->email:"n/d"'),        
        array('name'=>'status',
              'filter' => $model->getStatusArray(),
              'value' => '$data->getStatusDesc()'),                
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
            'class'=>'bootstrap.widgets.BootButtonColumn',
            'htmlOptions'=>array('style'=>'width: 70px'),
            'template'=>'{view} {update}',
            'buttons' => array(
                'update' => array(
                    'visible' => '$data->isOpen()'
                )
            )
        ),
    ),
    'filter'=>$model
)); ?>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'ticket_view_dialog',
    'htmlOptions'=>array('class'=>'hide'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Dettagli Ticket</h3>
</div>
<div class="modal-body">

</div>
<div class="modal-footer">
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>
<?php $this->endWidget(); ?>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'ticket_update_dialog',
    'htmlOptions'=>array('class'=>'hide span7', 'style'=>'height: auto; max-height: none;'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Aggiorna Ticket</h3>
</div>
<div id="ticket_update_container" style="width: 100%"></div>
<?php $this->endWidget(); ?>

<?php
Yii::app()->clientScript->registerScript('group-list-buttons', "
    
   $('a.update').live('click', function(e)
   {
        e.preventDefault();
        var update_url = $(e.currentTarget).attr('href');
        $.ajax({
            'url': update_url,
            'cache': false,
            'type':'GET',
            'dataType': 'html',
            success: function(data)
            {
                $('#ticket_update_container').html(data);
                $('#ticket_update_dialog').modal('show');                
            },
            error: function(xhr)
            {
                alert('Impossibile completare l\'operazione');
            }
        });

   });
   

    $('a.view').live('click', function(e){
        e.preventDefault();
        var view_url = $(e.currentTarget).attr('href'); 
        $.ajax({
            'url': view_url,
            'cache': false,
            'type': 'GET',
            'dataType': 'html',
            'success': function(data)
            {
                $('#ticket_view_dialog div.modal-body').html(data);
                $('#ticket_view_dialog').modal('show');
            },
            'error': function(xhr,errorstatus,errothrown)
            {
                alert('Impossibile recuperare i dettagli del gruppo');
            }
        });
   });
");

Yii::app()->clientScript->registerScript('ticketyiiactiveform', "
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
                        $('#ticket_update_dialog').modal('hide');                        
                        $.fn.yiiGridView.update('tickets_gridview');
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