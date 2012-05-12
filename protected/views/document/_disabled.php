<table class="table table-striped table-condensed detail-view">
    
    <tr>
        <th style="width: 20%"><label><?php echo Document::model()->getAttributeLabel('identifier'); ?></label></th>
        <td style="width: 30%"><p><?php echo CHtml::encode($data['identifier']!==null?$data['identifier']:'n/d'); ?></p></td>
        <td rowspan="6"><div class="richtext limited"><?php echo $data['description']; ?></div></td>
    </tr>    
    
    <tr>
        <th><label><?php echo Document::model()->getAttributeLabel('name'); ?></label></th>
        <td><p><?php echo CHtml::encode($data['name']); ?></p></td>

    </tr>
    
    <tr>
        <th><label><?php echo Document::model()->getAttributeLabel('subject'); ?></label></th>
        <td><p><?php echo CHtml::encode($data['subject']); ?></p></td>

    </tr>

    <tr>
        <th><label><?php echo Document::model()->getAttributeLabel('date_received'); ?></label></th>
        <td><p><?php echo date('d-m-Y', strtotime($data['date_received'])); ?></p></td>
    </tr>
    
    <tr>
        <th><label><?php echo Document::model()->getAttributeLabel('last_updated'); ?></label></th>
        <td><p><?php echo date('d-m-Y H:i:s', strtotime($data['last_updated'])); ?></p></td>
    </tr>

    <tr>
        <th colspan="2">
            <?php echo CHtml::link(CHtml::image(Yii::app()->baseUrl.'/images/misc/pdficon_large.png'), array('/document/viewpdf', 'id'=>$data['id']), array('class'=>'btn', 'target'=>'_blank')); ?>                                
            <?php echo CHtml::link('Vedi', array('/document/view', 'id'=>$data['id']), array('class'=>'btn btn-primary')); ?>
            <?php echo CHtml::link('Modifica', array('/document/update', 'id'=>$data['id']), array('class'=>'btn btn-primary')); ?>    
            <?php echo CHtml::link('Ripubblica su elenco', array('/document/enable', 'id'=>$data['id']), array('class'=>'btn btn-primary btn-enable')); ?>    
            <?php echo CHtml::link('Rimuovi definitivamente', array('/document/delete', 'id'=>$data['id']), array('class'=>'btn btn-primary btn-delete')); ?>                
        </th>
    </tr>
</table>