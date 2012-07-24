<?php if(!isset($isAjax)): ?>
<?php $this->pageTitle = $model->name; ?>
<?php $this->breadcrumbs = array('links' => array('Ruoli'=>'/role/index', 'Vedi')); ?>
<h1><?php echo $this->pageTitle; ?></h1>
<?php endif; ?>
<?php
$this->widget('bootstrap.widgets.BootDetailView', array(
    'data'=>$model,
    'attributes'=>array(
                        'name', 
                        'description', 
                        'right_ids'=>array(
                            'label'=>Role::model()->getAttributeLabel('right_ids'),
                            'type'=>'raw',
                            'value'=>ESHtml::unorderedList($model->rights)
                        )
        ),
    'nullDisplay'=>false
));
?>