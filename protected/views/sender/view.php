<?php if(!isset($isAjax)): ?>
<?php $this->pageTitle = $model->name; ?>
<?php $this->breadcrumbs = array('links' => array('Mittenti'=>'/sender/index', 'Vedi')); ?>
<h1><?php echo $this->pageTitle; ?></h1>
<?php endif; ?>
<?php
$this->widget('bootstrap.widgets.BootDetailView', array(
    'data'=>$model,
    'attributes'=>array(
                        'name', 
                        array(
                            'type'=>'raw',
                            'value'=>$model->getFullAddress(),
                            'label'=>Sender::model()->getAttributeLabel('address')
                        ),
                        'date_created',
                        'last_updated'
        ),
    'nullDisplay'=>false
));
?>