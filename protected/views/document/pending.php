<?php if(!isset($is_ajax)):?>
<?php $this->pageTitle = "Documenti in attesa"; ?>
<?php $this->breadcrumbs = array('links'=> array('Documenti'=>'/document/pending', 'In attesa')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<div id="pending-table-container" class="span9 pull-left" style="margin-left: 0">
    <?php endif; ?>
    <p class="help-block">
       Ultimi aggiornamenti: <?php echo date('d-m-Y H:i:s', Yii::app()->user->getState('last_update_check')); ?>
       <?php echo CHtml::link('<i class="icon-refresh icon-white"></i>', '#', array('id'=>'refresh_link', 'class'=>'btn btn-primary')); ?>
    </p>
    
    <table id="pending_documents_table" class="table table-striped">
        <thead>
            <tr><th>Nome</th><th>Dimensione</th><th>Ultima Modifica</th><th>&nbsp;</th></tr>
        </thead>
        <tbody>
            <?php foreach($documents as $g_id => $g_documents): ?>
                <tr class="searation-row"><td colspan="4"><?php echo $g_id=='user'?'Caricati a mano':$groups[$g_id]['name'] ?></td></tr>
                <?php foreach($g_documents as $document):?>
                    <tr>
                        <td><?php echo $document['name'] ?></td>
                        <td><?php echo $document['size'] ?></td>
                        <td><?php echo date('d-m-Y H:i:s', $document['ctime']) ?></td>                    
                        <td>
                            <?php echo CHtml::link('<i class="icon-eye-open icon-white"></i>', '#', array('id'=>  uniqid("thumb"), 'class'=>'btn btn-primary thumbnail_link', 'data-content'=>'<img src="'.Yii::app()->createUrl('/document/thumbnail', array('group_id'=>$g_id, 'document_name'=>$document['name'])).'" />')); ?>
                            <?php if($this->isAllowed('document', 'protocol')): ?>
                                <?php if($g_id=='user'): ?>
                                    <?php echo CHtml::link('Protocolla', array('/document/protocol', 'document_name'=>$document['name']), array('class'=>'btn btn-primary', 'data-content'=>'Protocolla il documento', 'rel'=>'tooltip')); ?>
                                <?php else: ?>
                                    <?php echo CHtml::link('Protocolla', array('/document/protocol', 'group_id'=>$g_id, 'document_name'=>$document['name']), array('class'=>'btn btn-primary', 'data-content'=>'Protocolla il documento', 'rel'=>'tooltip')); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if($this->isAllowed('document', 'archive')): ?>                            
                            <?php if($g_id=='user'): ?>
                                <?php echo CHtml::link('Archivia', array('/document/archive', 'document_name'=>$document['name']), array('class'=>'btn btn-primary', 'data-content'=>'Archivia il documento senza protocollarlo', 'rel'=>'tooltip')); ?>                            
                            <?php else:  ?> 
                                <?php echo CHtml::link('Archivia', array('/document/archive', 'group_id'=>$g_id, 'document_name'=>$document['name']), array('class'=>'btn btn-primary', 'data-content'=>'Archivia il documento senza protocollarlo', 'rel'=>'tooltip')); ?>
                            <?php endif; ?>
                            <?endif; ?>
                            <?php if($this->isAllowed('document', 'deletepending')): ?>
                                <?php echo CHtml::link('<i class="icon-trash icon-white"></i>', array('/document/deletepending', 'group_id'=>$g_id, 'document_name'=>$document['name']), array('class'=>'btn btn-primary delete', 'data-content'=>'Cancella il documento', 'rel'=>'tooltip')); ?>                            
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach;?>
        </tbody>
    </table>
    <?php if(!isset($is_ajax)):?>
</div>
<?php endif; ?>

<?php

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/jquery.periodic.js');
Yii::app()->clientScript->registerScript('pending-periodic', "
    $('a.thumbnail_link').popover({
        trigger: 'manual',
        html: true,
        title: 'Preview',
    });

    $('a.thumbnail_link').live('click', function(e){
        e.preventDefault();
        var id = $(e.target.parentNode).attr('id');        
        $('#'+id).popover('toggle');
    });    

    var updating = false;
    $.periodic({period: 15000, decay: 1.2, max_period: 90000}, function()
    {
        if(!updating)
            refreshTable(0);
    });

    $('#refresh_link').live('click', function(e){
        e.preventDefault();
        if(!updating)
            refreshTable(0);
    });
    
    $(document).on('click', 'a.delete', function(e){
        e.preventDefault();
        var delete_url = $(e.currentTarget).attr('href');

        if(!confirm('Sei sicuro di voler cancellare il documento?'))
            return false;

        $.ajax({
            timeout: 10000,
            cache: false,
            url: delete_url,
            dataType: 'json',
            success: function(data){
                if(data.success==1)
                    refreshTable(1);
                else
                    alert('Impossibile cancellare il file');
            },
            'error': function(xhr)
            {
                alert('Impossibile cancellare il file');
            }
            
        });
    });
    
    function refreshTable(force_reload)
    {
        updating: true;
        $('#refresh_link').addClass('disabled');
        $.ajax({
            url: '".Yii::app()->createUrl('/document/pending')."',
            data: { 'force_reload':force_reload },
            dataType: 'json',
            cache: false,
            complete: this.ajax_complete,
            success: function(data){
                if(data.has_update==1)
                    $('#pending-table-container').html(data.content);
            },
            complete: function()
            {
                $('#refresh_link').removeClass('disabled');            
                updating: false;
            }
        });    
    }
    ");
?>