<?php if(!isset($isAjax)): ?>
<?php $this->pageTitle = $model->title; ?>
<?php $this->breadcrumbs = array('links' => array('Spese'=>'/spending/index', 'Vedi')); ?>
<h1><?php echo $this->pageTitle; ?></h1>
<?php endif; ?>
<?php
$is_owner = $this->checkAuth($model);
$other_files = $model->listOtherDocuments();
$other_html = "";
foreach($other_files as $other_file)
{
    $other_html .= $other_file.' ('.$model->getOtherSize($other_file).' KB) <a href="'.Yii::app()->createUrl('/spending/downloadother', array('id'=>$model->id, 'filename'=>$other_file)).'" class="icon-download">   </a><br/>';
}
$this->widget('bootstrap.widgets.BootDetailView', array(
    'data'=>$model,
    'attributes'=>array(
                        'title', 
                        'amount' => array('type'=>'raw', 'value'=>$model->getDisplayAmount(), 'label'=>$model->getAttributeLabel('amount')), 
                        'receiver', 
                        'attribution_norm',
                        'attribution_mod',
                        'office',
                        'employee',
                        'description' => array('type'=>'html', 'value'=>$model->description, 'label'=>$model->getAttributeLabel('description')),
                        'spending_date' => array('label'=>$model->getAttributeLabel('spending_date'), 'type'=>'datetime', 'value' => strtotime($model->spending_date)),
                        'cv_name' => array('type'=>'html', 'label'=>$model->getAttributeLabel('cv_name'), 'value' => $model->hasCV()?$model->getCVName().' ('.$model->getCVSize().' KB) <a href="'.Yii::app()->createUrl('/spending/downloadcv', array('id'=>$model->id)).'" class="icon-download">   </a>':'n/d'),
                        'project_name' => array('type'=>'html', 'label'=>$model->getAttributeLabel('project_name'), 'value' => $model->hasProject()?$model->getProjectName().' ('.$model->getProjectSize().' KB) <a href="'.Yii::app()->createUrl('/spending/downloadproject', array('id'=>$model->id)).'" class="icon-download">   </a>':'n/d'),
                        'contract_name' => array('type'=>'html', 'label'=>$model->getAttributeLabel('contract_name'), 'value' => $model->hasContract()?$model->getContractName().' ('.$model->getContractSize().' KB) <a href="'.Yii::app()->createUrl('/spending/downloadcontract', array('id'=>$model->id)).'" class="icon-download">   </a>':'n/d'),        
                        'capitulate_name' => array('type'=>'html', 'label'=>$model->getAttributeLabel('capitulate_name'), 'value' => $model->hasCapitulate()?$model->getCapitulateName().' ('.$model->getCapitulateSize().' KB) <a href="'.Yii::app()->createUrl('/spending/downloadcapitulate', array('id'=>$model->id)).'" class="icon-download">   </a>':'n/d'),
                        'other' => array('type'=>'html', 'label'=>$model->getAttributeLabel('other'), 'value'=>$other_html),
                        'publication_requested'=>array('type'=>'raw', 'label'=>$model->getAttributeLabel('publication_requested'), 'value'=>$model->getPublicationRequestedDesc(), 'visible'=>$is_owner),
                        'publication_status' => array('type'=>'raw', 'label'=>$model->getAttributeLabel('publication_status'), 'value'=>$model->getPublicationStatusDesc(), 'visible'=>$is_owner),
                        'status' => array('type'=>'raw', 'label'=>$model->getAttributeLabel('status'), 'value'=>$model->getStatusDesc(), 'visible'=>$is_owner),
                        'date_created' => array('label'=>$model->getAttributeLabel('date_created'), 'type'=>'datetime', 'value' => strtotime($model->date_created), 'visible'=>$is_owner),
                        'last_updated' => array('label'=>$model->getAttributeLabel('last_updated'), 'type'=>'datetime', 'value' => strtotime($model->last_updated), 'visible'=>$is_owner)
        ),
    'nullDisplay'=>'n/d'
    )
);
?>
<?php if($is_owner): ?>
<?php echo CHtml::link('Aggiorna', array('/spending/update', 'id'=>$model->id), array('class'=>'btn btn-primary')); ?>
<?php endif; ?>
