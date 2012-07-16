<?php $this->pageTitle = "Documenti Assegnati a Me"; ?>
<?php $this->breadcrumbs = array('links'=> array('Documenti'=>'/document/my', 'Assegnati a Me')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php 
$this->renderPartial('_filterform', array('model'=>$model));

$this->widget('bootstrap.widgets.BootListView', array(
		'dataProvider'=> $model->my(),
		'itemView'=>'_minidoc',   // refers to the partial view named '_proficiency'
                'template'=>"{items}\n{pager}",    
		'id' => 'documentslistview',
		'sortableAttributes'=>array(
				'identifier' => 'Numero di Protocollo',
				'name' => 'Nome',
                                'date_created' => 'Data di creazione'
		),
));
?>


<?php
Yii::app()->clientScript->registerScript('document-disable-controls', "
    $('a.btn-disable').live('click', function(e)
    {
        e.preventDefault();
        if(!confirm('Sei sicuro di voler rimuovere il documento dall\'elenco?'))
            return false;        
        var current_link = $(e.currentTarget);
        $.ajax({
            url: current_link.attr('href'),
            method: 'POST',
            cache: false,
            dataType: 'json',
            beforeSend: function()
            {
                current_link.addClass('disabled');
            },
            success: function(data)
            {
                if(data.success==1)
                    $.fn.yiiListView.update('documentslistview');
                else
                    alert(data.errors.join(','));
            },
            error: function(xhr)
            {
                alert('Impossibile disabilitare il documento');
            },
            complete: function()
            {
                current_link.removeClass('disabled');            
            }
        });
    });
");
?>