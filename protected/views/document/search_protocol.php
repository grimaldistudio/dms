<?php $this->pageTitle = "Cerca in: Posta in entrata"; ?>
<?php $this->breadcrumbs = array('links'=> array('Documenti'=>'/document/search', 'Cerca')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<div class="btn-toolbar">
    <?php echo CHtml::link('Cerca in archivio', array('/document/search', 'doc_type'=>Document::INTERNAL_USE_TYPE), array('class'=>'btn')); ?>
    <?php echo CHtml::link('Cerca in documenti pubblici', array('/document/search', 'doc_type'=>Document::OUTGOING), array('class'=>'btn')); ?>    
</div>

<?php $this->renderPartial('_protocolsearchform', array('idmodel'=>$idmodel, 'tagsmodel'=>$tagsmodel, 'datemodel'=>$datemodel)); ?>