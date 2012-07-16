<table class="table table-striped table-condensed detail-view">
    
    
    <tr>
        <th style="width: 20%"><label><?php echo Document::model()->getAttributeLabel('name'); ?></label></th>
        <td style="width: 30%"><p><?php echo CHtml::encode($data['name']!==null?$data['name']:'n/d'); ?></p></td>
        <td rowspan="4"><div class="richtext limited"><?php echo $data['description']; ?></div></td>
    </tr>    
    
    <tr>
        <th><label>Data di archiviazione</label></th>
        <td><p><?php echo date('d-m-Y', strtotime($data['date_created'])); ?></p></td>
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