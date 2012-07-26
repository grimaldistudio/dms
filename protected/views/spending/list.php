<?php if($search_type==Spending::SEARCH_ALL): ?>
<?php $this->pageTitle = "Cerca Spese"; ?>
<?php $this->breadcrumbs = array('links'=> array('Cerca Spese')); ?>
<?php elseif($search_type==Spending::SEARCH_MY): ?>
<?php $this->pageTitle = "Spese Create da Me"; ?>
<?php $this->breadcrumbs = array('links'=> array('Spese Create da Me')); ?>
<?php elseif($search_type==Spending::SEARCH_DISABLED): ?>
<?php $this->pageTitle = "Cestino Spese"; ?>
<?php $this->breadcrumbs = array('links'=> array('Cestino Spese')); ?>
<?php endif; ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php 
$this->renderPartial('_searchform', array('model'=>$model));

$this->widget('bootstrap.widgets.BootListView', array(
		'dataProvider'=> $model->search($search_type),
		'itemView'=>'_item',   // refers to the partial view named '_proficiency'
                'template'=>"{items}\n{pager}",    
		'id' => 'spendingslistview',
		'sortableAttributes'=>array(
				'title' => 'Numero di Protocollo',
				'amount' => 'Importo',
                                'spending_date' => 'Data'
		),
));
?>


<?php
Yii::app()->clientScript->registerScript('spending-controls', "
    $('a.btn-disable').on('click', function(e)
    {
        e.preventDefault();
        if(!confirm('Sei sicuro di voler rimuovere la spesa dall\'elenco?'))
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
                    $.fn.yiiListView.update('spendingslistview');
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
    
    $('a.btn-enable').on('click', function(e)
    {
        e.preventDefault();
        if(!confirm('Sei sicuro di voler ripubblicare la spesa nell\'elenco?'))
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
                    $.fn.yiiListView.update('spendingslistview');
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
    
    $('a.btn-delete').on('click', function(e)
    {
        e.preventDefault();
        if(!confirm('Sei sicuro di voler cancellare definitivamente la spesa?'))
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
                    $.fn.yiiListView.update('spendingslistview');
                else
                    alert(data.errors.join(','));
            },
            error: function(xhr)
            {
                alert('Impossibile cancellare il documento');
            },
            complete: function()
            {
                current_link.removeClass('disabled');            
            }
        });
    });
");
?>