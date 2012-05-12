<?php $this->pageTitle = "Elenco Tags"; ?>
<?php $this->breadcrumbs = array('links'=> array('Tags'=>'/tag/')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<div class="btn-toolbar">
<?php echo CHtml::link('<i class="icon-plus icon-white"></i> Aggiungi', array('/tag/create'), array('class'=>'btn btn-primary create')); ?>
</div>

<?php $this->widget('bootstrap.widgets.BootGridView', array(
    'id'=>'tags_gridview',
    'dataProvider'=>$model->search(),
    'template'=>"{items}\n{pager}",
    'itemsCssClass'=>'table table-striped table-bordered table-condensed',
    'columns'=>array(
        array('name'=>'name'),
        array(
            'class'=>'bootstrap.widgets.BootButtonColumn',
            'htmlOptions'=>array('style'=>'width: 50px'),
            'template'=>'{update} {delete}'
            ),
    ),
    'filter'=>$model
)); ?>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'tag_create_dialog',
    'htmlOptions'=>array('class'=>'hide span4', 'style'=>'height: auto; max-height: none;'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Aggiungi Tag</h3>
</div>
<?php $this->renderPartial('_ajaxform', array('model'=>new Tag('create'))); ?>
<?php $this->endWidget(); ?>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'tag_update_dialog',
    'htmlOptions'=>array('class'=>'hide span4', 'style'=>'height: auto; max-height: none;'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Modifica Tag</h3>
</div>
<div id="tag_update_container" style="width: 100%"></div>
<?php $this->endWidget(); ?>

<?php
Yii::app()->clientScript->registerScript('role-list-buttons', "
    
   $('a.create').on('click', function(e)
   {
        e.preventDefault();
        $('#tag_create_dialog').modal('show');
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
                $('#tag_update_container').html(data);
                $('#tag_update_dialog').modal('show');                
            },
            error: function(xhr)
            {
                alert('Impossibile completare l\'operazione');
            }
        });

   });
");

Yii::app()->clientScript->registerScript('tagyiiactiveform', "
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
                        $('#tag_create_dialog').modal('hide');
                        $('#tag_update_dialog').modal('hide');                        
                        $(form).resetForm();
                        $.fn.yiiGridView.update('tags_gridview');
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