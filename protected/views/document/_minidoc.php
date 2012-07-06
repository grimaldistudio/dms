<table class="table table-striped table-condensed detail-view">
    
    <tr>
        <th style="width: 20%"><label><?php echo Document::model()->getAttributeLabel('identifier'); ?></label></th>
        <td style="width: 30%"><p><?php echo CHtml::encode($data['identifier']!==null?$data['identifier']:'n/d'); ?></p></td>
        <td rowspan="8"><div class="richtext limited"><?php echo $data['description']; ?></div></td>
    </tr>    
    
    <tr>
        <th><label><?php echo Document::model()->getAttributeLabel('name'); ?></label></th>
        <td><p><?php echo CHtml::encode($data['name']); ?></p></td>

    </tr>
    
    <?php if(isset($data['firstname']) && isset($data['lastname'])): ?>
    <tr>
        <th><label><?php echo Document::model()->getAttributeLabel('creator_id'); ?></label></th>
        <td><p><?php echo CHtml::encode($data['firstname'].' '.$data['lastname']); ?></p></td>
    </tr>
    <?php endif; ?>
        
    <tr>
        <th><label>Destinatari</label></th>
        <td>
            <p>
            <?php $targets = AclManager::getDocumentTargets($data['id']); ?>
                <?php foreach($targets as $target): ?>
                    <?php if($target['firstname'] && $target['lastname']): ?>
                        <span class="document_target"><?php echo $target['firstname'].' '.$target['lastname']; ?></span>
                    <?php else: ?>
                        <span class="document_target"><?php echo $target['name']; ?></span>                        
                    <?php endif; ?>
                <?php endforeach; ?>
            </p>
        </td>
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
            <?php if(!isset($data['max_right']) || in_array($data['max_right'], array(DocumentRight::WRITE, DocumentRight::ADMIN))): ?>
            <?php if($this->isAllowed('document', 'update')) echo CHtml::link('Modifica', array('/document/update', 'id'=>$data['id']), array('class'=>'btn btn-primary')); ?>    
            <?php endif; ?>
            <?php if(!isset($data['max_right']) || $data['max_right']==DocumentRight::ADMIN): ?>
            <?php if($this->isAllowed('document', 'disable')) echo CHtml::link('Rimuovi da elenco', array('/document/disable', 'id'=>$data['id']), array('class'=>'btn btn-primary btn-disable')); ?>    
            <?php endif; ?>            
        </th>
    </tr>
</table>