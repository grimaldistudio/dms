<?php if(!isset($isAjax)): ?>
<?php $this->pageTitle = $model->name.' ('.$model->key.')'; ?>
<?php $this->breadcrumbs = array('links' => array('Permessi'=>'/right/index', 'Vedi')); ?>
<h1><?php echo $this->pageTitle; ?></h1>
<?php endif; ?>
<?php
$this->widget('bootstrap.widgets.BootDetailView', array(
    'data'=>$model,
    'attributes'=>array(
                        'name', 
                        'key', 
                        'role_ids'=>array(
                            'label'=>User::model()->getAttributeLabel('role_ids'),
                            'type'=>'raw',
                            'value'=>ESHtml::unorderedList($model->roles)
                        ),
        ),
    'nullDisplay'=>false
));
?>