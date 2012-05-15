<?php $this->pageTitle = "Elenco Permessi"; ?>
<?php $this->breadcrumbs = array('links'=> array('Permessi'=>'/right/')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<div class="btn-toolbar">
<?php echo CHtml::link('<i class="icon-plus icon-white"></i> Aggiungi', array('/right/create'), array('class'=>'btn btn-primary create')); ?>
</div>

<?php $this->widget('bootstrap.widgets.BootGridView', array(
    'id'=>'rights_gridview',
    'dataProvider'=>$model->search(),
    'template'=>"{items}\n{pager}",
    'itemsCssClass'=>'table table-striped table-bordered table-condensed',
    'columns'=>array(
        array('name'=>'name'),
        array('name'=>'key'),
        array(
            'class'=>'bootstrap.widgets.BootButtonColumn',
            'htmlOptions'=>array('style'=>'width: 50px'),
        ),
    ),
    'filter'=>$model
)); ?>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'right_view_dialog',
    'htmlOptions'=>array('class'=>'hide'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Dettagli Permesso</h3>
</div>
<div class="modal-body">

</div>
<div class="modal-footer">
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>
<?php $this->endWidget(); ?>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'right_create_dialog',
    'htmlOptions'=>array('class'=>'hide span7', 'style'=>'height: auto; max-height: none;'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Aggiungi Utente</h3>
</div>
<?php $this->renderPartial('_ajaxform', array('model'=>new Right('create'))); ?>
<?php $this->endWidget(); ?>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'right_update_dialog',
    'htmlOptions'=>array('class'=>'hide span7', 'style'=>'height: auto; max-height: none;'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Modifica Permesso</h3>
</div>
<div id="right_update_container" style="width: 100%"></div>
<?php $this->endWidget(); ?>

<?php
Yii::app()->clientScript->registerScript('right-list-buttons', "
    
   $('a.create').on('click', function(e)
   {
        e.preventDefault();
        $('#right_create_dialog').modal('show');
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
                $('#right_update_container').html(data);
                $('#right_update_dialog').modal('show');                
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
                $('#right_view_dialog div.modal-body').html(data);
                $('#right_view_dialog').modal('show');
            },
            'error': function(xhr,errorstatus,errothrown)
            {
                alert('Impossibile recuperare i dettagli dell\'utente');
            }
        });
   });
");

Yii::app()->clientScript->registerScript('rightyiiactiveform', "
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
                        $('#right_create_dialog').modal('hide');
                        $('#right_update_dialog').modal('hide');                        
                        $(form).resetForm();
                        $.fn.yiiGridView.update('rights_gridview');
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