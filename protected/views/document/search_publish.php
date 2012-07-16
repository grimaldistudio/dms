<?php $this->pageTitle = "Cerca in: Documenti pubblici"; ?>
<?php $this->breadcrumbs = array('links'=> array('Documenti'=>array('/document/search', 'doc_type'=>Document::OUTGOING), 'Cerca')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<div class="btn-toolbar">
    <?php echo CHtml::link('Cerca in posta in entrata', array('/document/search', 'doc_type'=>Document::INBOX), array('class'=>'btn')); ?>
    <?php echo CHtml::link('Cerca in archivio', array('/document/search', 'doc_type'=>Document::INTERNAL_USE_TYPE), array('class'=>'btn')); ?>    
</div>

<?php $this->renderPartial('_publishsearchform', array('idmodel'=>$idmodel, 'tagsmodel'=>$tagsmodel, 'datemodel'=>$datemodel)); ?>