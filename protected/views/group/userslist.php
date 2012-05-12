<?php $this->pageTitle = $group->name.": Elenco Utenti"; ?>
<?php $this->breadcrumbs = array('links'=> array('Gruppi'=>'/group/', $group->name=>'/group/'.$group->id, 'Elenco Utenti')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<div class="btn-group toolbar">
    <h4>Aggiungi utenti: </h4>
    <p class="help-block">Max. 5 utenti per volta</p>
    <div class="pull-left">
        <?php echo CHtml::textField('user_ids', '', array('id'=>'user_ids', 'class'=>'span3', 'placeholder'=>'Nome o e-mail')); ?>
    </div>
    <?php echo CHtml::link("Aggiungi", array('/group/addusers', 'id'=>$group->id), array('class'=>'pull-left btn btn-primary btn-addusers')); ?>    
</div>

<?php $this->widget('bootstrap.widgets.BootGridView', array(
    'id'=>'users_groups_gridview',
    'dataProvider'=>$model->gsearch(),
    'template'=>"{items}\n{pager}",
    'itemsCssClass'=>'table table-striped table-bordered table-condensed',
    'columns'=>array(
        array('name'=>'firstname'),
        array('name'=>'lastname'),
        array('name'=>'email'),        
        array(
            'class'=>'bootstrap.widgets.BootButtonColumn',
            'buttons'=>array(
                'delete'=>array(
                    'icon'=>'icon-remove',
                    'label'=>'Rimuovi',
                    'url'=>'Yii::app()->createUrl(\'/group/removeuser\', array(\'id\'=>$this->grid->filter->group_id, \'user_id\'=>$data->id))',
                )
            ),
            'template'=>'{delete}'
        ),
    ),
    'filter'=>$model
)); ?>

<?php
Yii::app()->clientScript->registerPackage('tokenInput');
Yii::app()->clientScript->registerScript('groups_useradd_js', "
   $('#user_ids').tokenInput('".Yii::app()->createUrl('/user/autocomplete')."', {
       preventDuplicates: true,
       hintText: 'Nome o E-mail',
       searchingText: 'Ricerca in corso...',
       noResultsText: 'Nessun risultato trovato',
       theme: 'facebook',
       tokenLimit: 5
    }); 
    
    $('.btn-addusers').on('click', function(e){
        e.preventDefault();
        var post_link = $(e.target);
        var post_url = post_link.attr('href');
        $.ajax({
            url: post_url,
            data: { user_ids: $('#user_ids').val()},
            type: 'POST',
            dataType: 'json',
            beforeSend: function()
            {
                post_link.addClass('disabled');
            },
            success: function(data)
            {
                $('#user_ids').tokenInput('clear');
                $.fn.yiiGridView.update('users_groups_gridview');
            },
            complete: function()
            {
                post_link.removeClass('disabled');
            },
            error: function(xhr)
            {
            
            }
        });
    });
", CClientScript::POS_READY);
?>
