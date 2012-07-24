<?php if(!isset($isAjax)): ?>
<?php $this->pageTitle = $model->getTitle(); ?>
<?php $this->breadcrumbs = array('links' => array('Ticket'=>'/ticket/index', 'Vedi')); ?>
<h1><?php echo $this->pageTitle; ?></h1>
<?php endif; ?>
<?php
$this->widget('bootstrap.widgets.BootDetailView', array(
    'data'=>$model,
    'attributes'=>array(
                        array(
                            'type'=>'raw',
                            'value'=>$model->user->getFullName(),
                            'label'=>$model->getAttributeLabel('user_name')
                        ),
                        array(
                            'type'=>'raw',
                            'value' => $model->document->getTitle(),
                            'label' => $model->getAttributeLabel('document_id')
                        ),
                        array(
                            'type'=>'raw',
                            'value'=>$model->getAccessLevelDesc(),
                            'label'=>$model->getAttributeLabel('access_level')
                        ),    
                        'request',
                        array(
                            'type'=>'raw',
                            'value'=>$model->replier_id>0?$model->replier->getFullName():'n/d',
                            'label'=>$model->getAttributeLabel('replier_name')
                        ),    
                        array(
                            'type'=>'raw',
                            'value'=>$model->getGrantedAccessLevelDesc(),
                            'label'=>$model->getAttributeLabel('granted_access_level')
                        ),            
                        'reply',
                        'date_created',
                        'last_updated'
        ),
    'nullDisplay'=>false
));
?>