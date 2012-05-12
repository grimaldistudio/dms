<?php if(!isset($isAjax)): ?>
<?php $this->pageTitle = $model->name; ?>
<?php $this->breadcrumbs = array('links' => array('Gruppi'=>'/group/index', 'Vedi')); ?>
<h1><?php echo $this->pageTitle; ?></h1>
<?php endif; ?>
<?php
$this->widget('bootstrap.widgets.BootDetailView', array(
    'data'=>$model,
    'attributes'=>array(
                        'name', 
                        'email', 
                        'telephone', 
                        'fax',
                        'folder_name',
                        'description' => array('type'=>'html', 'value'=>$model->description, 'label'=>Group::model()->getAttributeLabel('description')),
                        'date_created' => array('label'=>Group::model()->getAttributeLabel('date_created'), 'type'=>'datetime', 'value' => strtotime($model->date_created)),
                        'last_updated' => array('label'=>Group::model()->getAttributeLabel('last_updated'), 'type'=>'datetime', 'value' => strtotime($model->last_updated))
        ),
    'nullDisplay'=>false
));
?>