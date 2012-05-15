<?php $this->pageTitle = "Elenco Gruppi"; ?>
<?php $this->breadcrumbs = array('links'=> array('Gruppi'=>'/group/')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<div class="btn-toolbar">
<?php echo CHtml::link('<i class="icon-plus icon-white"></i> Aggiungi', array('/group/create'), array('class'=>'btn btn-primary create')); ?>
</div>

<?php $this->widget('bootstrap.widgets.BootGridView', array(
    'id'=>'groups_gridview',
    'dataProvider'=>$model->search(),
    'template'=>"{items}\n{pager}",
    'itemsCssClass'=>'table table-striped table-bordered table-condensed',
    'columns'=>array(
        array('name'=>'name'),
        array('name'=>'email'),
        array('name'=>'telephone'),
        array('name'=>'fax'),        
	array('filter' => $model->getStatusArray(),
            'name' => 'status',
            'value' => '$data->getStatusDesc()'),        
        array(
            'class'=>'bootstrap.widgets.BootButtonColumn',
            'htmlOptions'=>array('style'=>'width: 70px'),
            'buttons'=>array(
                'users' => array('url'=>'Yii::app()->createUrl(\'/group/users\', array(\'id\'=>$data->id))',
                                'icon'=>'icon-user',
                                'label'=>'Utenti',
                                'options'=>array('class'=>'users')
                    )
            ),
            'template'=>'{view} {update} {delete} {users}'
        ),
    ),
    'filter'=>$model
)); ?>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'group_view_dialog',
    'htmlOptions'=>array('class'=>'hide'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Dettagli Gruppo</h3>
</div>
<div class="modal-body">

</div>
<div class="modal-footer">
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>
<?php $this->endWidget(); ?>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'users_groups_dialog',
    'htmlOptions'=>array('class'=>'hide'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Utenti Gruppo</h3>
</div>
<div class="modal-body">

</div>
<div class="modal-footer">
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>
<?php $this->endWidget(); ?>


<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'group_create_dialog',
    'htmlOptions'=>array('class'=>'hide span7', 'style'=>'height: auto; max-height: none;'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Aggiungi Gruppo</h3>
</div>
<?php $this->renderPartial('_ajaxform', array('model'=>new Group('create'))); ?>
<?php $this->endWidget(); ?>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'group_update_dialog',
    'htmlOptions'=>array('class'=>'hide span7', 'style'=>'height: auto; max-height: none;'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Modifica Gruppo</h3>
</div>
<div id="group_update_container" style="width: 100%"></div>
<?php $this->endWidget(); ?>

<?php
Yii::app()->clientScript->registerScript('group-list-buttons', "
    
   $('a.create').on('click', function(e)
   {
        e.preventDefault();
        $('#group_create_dialog').modal('show');
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
                $('#group_update_container').html(data);
                $('#group_update_dialog').modal('show');                
            },
            error: function(xhr)
            {
                alert('Impossibile completare l\'operazione');
            }
        });

   });
   
    $('a.users').live('click', function(e){
        e.preventDefault();
        var users_url = $(e.target.parentNode).attr('href');
        $.ajax({
            url: users_url,
            cache: false,
            type: 'GET',
            dataType: 'html',
            success: function(data)
            {
                $('#users_groups_dialog div.modal-body').html(data);
                $('#users_groups_dialog').modal('show');
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
                $('#group_view_dialog div.modal-body').html(data);
                $('#group_view_dialog').modal('show');
            },
            'error': function(xhr,errorstatus,errothrown)
            {
                alert('Impossibile recuperare i dettagli del gruppo');
            }
        });
   });
");

Yii::app()->clientScript->registerScript('groupyiiactiveform', "
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
                        $('#group_create_dialog').modal('hide');
                        $('#group_update_dialog').modal('hide');                        
                        $(form).resetForm();
                        $.fn.yiiGridView.update('groups_gridview');
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