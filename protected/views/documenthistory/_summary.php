<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'document_revisions_dialog',
    'htmlOptions'=>array('class'=>'hide'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Storico Revisioni</h3>
</div>
<div class="modal-body">
    <div class="revisions-summary">

        <?php if(count($revisions)>0): ?>
        <table class="table table-striped table-condensed">
            <tr>
                <th>N. Revisione</th>
                <th>Autore</th>
                <th>E-mail</th>
                <th>Descrizione</th>
                <th>Data</th>
            </tr>
            <?php foreach($revisions as $revision): ?> 
            <tr>
                <td><?php echo $revision['revision']; ?></td>
                <td><?php echo $revision['author']; ?></td>
                <td><?php echo $revision['email']; ?></td>
                <td><?php echo $revision['description']; ?></td>
                <td><?php echo date('Y-m-d H:i:s', strtotime($revision['date_created'])); ?></td>
            </tr>            
            <?php endforeach; ?>
        </table>
            <?php if($revisions_count>count($revisions)): ?>
                <?php echo CHtml::link('Vedi tutte', array('/document/history', 'id'=>$model->id)); ?>
            <?php endif; ?> 
        <?php else: ?>
        Nessuna revisione presente.
        <?php endif; ?>
    </div>
</div>
<div class="modal-footer">
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
</div>    
<?php $this->endWidget(); ?>

<?php Yii::app()->clientScript->registerScript('historycontrols', "
   $('.open-history').live('click', function(e)
   {
        e.preventDefault();
        $('#document_revisions_dialog').modal('show');
   });
");