<table class="table table-striped table-condensed detail-view">
    
    
    <tr>
        <th style="width: 20%"><label><?php echo Document::model()->getAttributeLabel('identifier'); ?></label></th>
        <td style="width: 30%"><p><?php echo CHtml::encode($data['identifier']!==null?$data['identifier']:'n/d'); ?></p></td>
        <td rowspan="8"><div class="richtext limited"><?php echo $data['description']; ?></div></td>
    </tr>    

    <tr>
        <th><label><?php echo Document::model()->getAttributeLabel('publication_number'); ?></label></th>
        <td><p><?php echo CHtml::encode($data['publication_number']!==null?$data['publication_number']:'n/d'); ?></p></td>
    </tr>        
    
    <tr>
        <th><label><?php echo Document::model()->getAttributeLabel('name'); ?></label></th>
        <td><p><?php echo CHtml::encode($data['name']); ?></p></td>
    </tr>
    
    <tr>
        <th><label><?php echo Document::model()->getAttributeLabel('document_type'); ?></label></th>
        <td><p><?php echo Document::model()->getTypeDesc($data['document_type']); ?></p></td>
    </tr>

    <tr>
        <th><label><?php echo Document::model()->getAttributeLabel('publication_date_from'); ?></label></th>
        <td><p><?php echo $data['publication_date_from']?date('d-m-Y', strtotime($data['publication_date_from'])):'n/d'; ?></p></td>
    </tr>
    
    <tr>
        <th><label><?php echo Document::model()->getAttributeLabel('publication_date_to'); ?></label></th>
        <td><p><?php echo $data['publication_date_to']?date('d-m-Y', strtotime($data['publication_date_to'])):'n/d'; ?></p></td>
    </tr>
    
    <tr>
        <th><label><?php echo Document::model()->getAttributeLabel('publication_requested'); ?></label></th>
        <td><p><?php echo $data['publication_requested']==1?'Si':'No'; ?></p></td>
    </tr>
    
    <tr>
        <th><label><?php echo Document::model()->getAttributeLabel('publication_status'); ?></label></th>
        <td><p><?php echo Document::model()->getPublicationStatusDesc($data['publication_status']); ?></p></td>
    </tr>
    
    <tr>
        <th colspan="2">
            <?php if(Yii::app()->user->hasDocumentPrivilege($data['id'], AclManager::PERMISSION_READ)): ?>
                <?php echo CHtml::link(CHtml::image(Yii::app()->baseUrl.'/images/misc/pdficon_large.png'), array('/document/viewpdf', 'id'=>$data['id']), array('class'=>'btn', 'target'=>'_blank')); ?>                                
                <?php echo CHtml::link('Vedi', array('/document/view', 'id'=>$data['id']), array('class'=>'btn btn-primary')); ?>
                <?php if(Yii::app()->user->hasDocumentPrivilege($data['id'], AclManager::PERMISSION_WRITE)): ?>
                    <?php if($this->isAllowed('document', 'update')) echo CHtml::link('Modifica', array('/document/update', 'id'=>$data['id']), array('class'=>'btn btn-primary')); ?>    
                <?php endif; ?>

            <?php if(Yii::app()->user->hasDocumentPrivilege($data['id'], AclManager::PERMISSION_ADMIN)): ?>
                    <?php if($this->isAllowed('document', 'disable')) echo CHtml::link('Rimuovi da elenco', array('/document/disable', 'id'=>$data['id']), array('class'=>'btn btn-primary btn-disable')); ?>    
            <?php endif; ?>

            <?php else: ?>
                <?php echo CHtml::link('Richiedi Accesso', array('/document/newticket', 'id'=>$data['id']), array('class'=>'btn btn-primary new-ticket-btn')); ?>            
            <?php endif; ?>
        </th>
    </tr>
</table>