<?php $this->pageTitle = "Risultati ricerca"; ?>
<?php $this->breadcrumbs = array('links'=> array('Documenti'=>array('/document/search', 'doc_type'=>$doc_type), 'Risultati ricerca')); ?>
<h1><?php echo $this->pageTitle; ?></h1>


<?php if((isset($idmodel) && $idmodel->hasResults()) || (isset($tagsmodel) && $tagsmodel->hasResults()) || (isset($datemodel) || $datemodel->hasResults())): ?>
<?php Yii::app()->clientScript->registerScript('search-form-controls', "
    $('a.show-search-form').live('click', function(e){
        e.preventDefault();
        var link = $(e.currentTarget);
        var form_container = $('#form-container');
        if(form_container.is(':visible'))
        {
            link.text('Mostra modulo ricerca');
            form_container.hide();
        }
        else
        {
            link.text('Nascondi modulo ricerca');
            form_container.show();        
        }
    });
"); ?>
<div class="buttons_bar">

<span>
    <?php echo CHtml::link('Mostra modulo ricerca', '#', array('class'=>'btn btn-primary show-search-form')); ?>
</span>

<span>
    <?php echo CHtml::link('Nuova Ricerca', array('/document/search', 'doc_type'=>$doc_type), array('class'=>'btn btn-primary')); ?>
</span>
</div>
<div id="form-container" style="display:none">
<?php else: ?>
<div id="form-container">    
<?php endif; ?>
    <?php 
    $array_model = array('tagsmodel'=>$tagsmodel, 'datemodel'=>$datemodel); 
    if(isset($idmodel))
        $array_model['idmodel'] = $idmodel;
    ?>
    <?php $this->renderPartial($searchform_template, $array_model); ?>
</div>

<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'ticket_create_dialog',
    'htmlOptions'=>array('class'=>'hide'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Apri Ticket</h3>
</div>
<div id="ticket_container">
</div>
<?php $this->endWidget(); ?>

<?php
if(isset($idmodel) && $idmodel->hasDataProvider()):
    $this->widget('bootstrap.widgets.BootListView', array(
		'dataProvider'=> $idmodel->getDataProvider(),
		'itemView'=>$result_template,   // refers to the partial view named '_proficiency'
		'id' => 'documentslistview',
                'template'=>"{items}\n{pager}",        
		'sortableAttributes'=>array(
				'identifier' => 'Numero di Protocollo',
				'name' => 'Nome',
                                'date_received' => 'Data di ricezione',
                                'last_updated' => 'Ultimo aggiornamento'                    
		),
    ));
elseif($tagsmodel->hasDataProvider()):
    $this->widget('bootstrap.widgets.BootListView', array(
		'dataProvider'=> $tagsmodel->getDataProvider(),
		'itemView'=>$result_template,   // refers to the partial view named '_proficiency'
		'id' => 'documentslistview',
                'template'=>"{items}\n{pager}",        
		'sortableAttributes'=>array(
				'identifier' => 'Numero di Protocollo',
				'name' => 'Nome',
                                'date_received' => 'Data di ricezione',
                                'last_updated' => 'Ultimo aggiornamento'
		),
    ));
elseif($datemodel->hasDataProvider()):
    $this->widget('bootstrap.widgets.BootListView', array(
		'dataProvider'=> $datemodel->getDataProvider(),
		'itemView'=>$result_template,   // refers to the partial view named '_proficiency'
                'template'=>"{items}\n{pager}",        
		'id' => 'documentslistview',
		'sortableAttributes'=>array(
				'identifier' => 'Numero di Protocollo',
				'name' => 'Nome',
                                'date_received' => 'Data di ricezione',
                                'last_updated' => 'Ultimo aggiornamento'
		),
    ));

endif;
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
    
    $('a.new-ticket-btn').live('click', function(e){
        e.preventDefault();
        var new_ticket_link = $(e.currentTarget).attr('href');
        $.ajax({
            url: new_ticket_link,
            cache: false,
            dataType: 'html',
            success: function(data)
            {
                $('#ticket_container').html(data);
                $('#ticket_create_dialog').modal('show');
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
                dataType: 'json',
                timeout: 10000,                
                beforeSubmit: function()
                {
                    $('#'+summaryID).html('');
                    $('#'+summaryID).hide();
                },
                success: function(data)
                {
                    if(data.success==1)
                    {
                        $('#ticket_create_dialog').modal('hide');
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
?>

    