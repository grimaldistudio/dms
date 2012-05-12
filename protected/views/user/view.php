<?php if(!isset($isAjax)): ?>
<?php $this->pageTitle = $model->firstname.' '.$model->lastname; ?>
<?php $this->breadcrumbs = array('links' => array('Utenti'=>'/user/index', 'Vedi')); ?>
<h1><?php echo $this->pageTitle; ?></h1>
<?php endif; ?>
<?php
$this->widget('bootstrap.widgets.BootDetailView', array(
    'data'=>$model,
    'attributes'=>array(
                        'firstname', 
                        'lastname', 
                        'telephone', 
                        'email', 
                        'group_ids'=>array(
                            'label'=>User::model()->getAttributeLabel('group_ids'),
                            'type'=>'raw',
                            'visible'=>!$model->is_admin,
                            'value'=>ESHtml::unorderedList($model->groups)
                        ), 
                        'role_ids'=>array(
                            'label'=>User::model()->getAttributeLabel('role_ids'),
                            'type'=>'raw',
                            'visible'=>!$model->is_admin,
                            'value'=>ESHtml::unorderedList($model->roles)
                        ),
                        'date_created' => array('label'=>User::model()->getAttributeLabel('date_created'), 'type'=>'datetime', 'value' => strtotime($model->date_created)),
                        'last_updated' => array('label'=>User::model()->getAttributeLabel('last_updated'), 'type'=>'datetime', 'value' => strtotime($model->last_updated))
        ),
    'nullDisplay'=>false
));
?>