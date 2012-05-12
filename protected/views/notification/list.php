<?php $this->pageTitle = "Notifiche"; ?>
<?php $this->breadcrumbs = array('links'=> array('Notifiche'=>'/notification/')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<div class="buttons_bar">
    <?php echo CHtml::link('Segna tutte come lette', array('/notification/markall'), array('class'=>'btn btn-primary read-all-btn')); ?>
</div>
<br/>

<?php $this->widget('bootstrap.widgets.BootGridView', array(
    'id'=>'notifications_gridview',
    'dataProvider'=>$model->search(),
    'template'=>"{items}\n{pager}",
    'itemsCssClass'=>'table table-striped table-bordered table-condensed',
    'columns'=>array(
        array('name'=>'description'),
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
            'template'=>'{view} {mark} {delete}',
            'buttons' => array(
                'mark' => array(
                    'icon' => 'icon-ok',
                    'url'=>'Yii::app()->createUrl(\'/notification/markread\', array(\'id\'=>$data->id))',
                    'options' => array(
                        'title' => 'Letto',
                        'class' => 'mark-read'
                    ),
                    'visible'=>'$data->isUnread()'
                )
                
            )
        ),
    ),
    'filter'=>$model
)); ?>


<?php
Yii::app()->clientScript->registerScript('notification-list-buttons', "
$('.mark-read').live('click', function(e){
    e.preventDefault();
    var mark_read_link = $(e.currentTarget);
    $.ajax({
        url: mark_read_link.attr('href'),
        cache: false,
        success: function(data)
        {
            $.fn.yiiGridView.update('notifications_gridview');
        },
        error: function(xhr)
        {
            alert('Impossibile segnare come letta la notifica');
        }
    });
});

$('.read-all-btn').live('click', function(e){
    e.preventDefault();
    var read_all_link = $(e.currentTarget);
    $.ajax({
        url: read_all_link,
        cache: false,
        success: function(data)
        {
            $.fn.yiiGridView.update('notifications_gridview');
        },
        error: function(xhr)
        {
            alert('Impossibile segnare come lette tutte le notifiche');
        }
    });
});
");