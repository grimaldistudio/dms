<?php $this->pageTitle = "Cerca in: Archivio"; ?>
<?php $this->breadcrumbs = array('links'=> array('Documenti'=>array('/document/search', 'doc_type'=>Document::INTERNAL_USE_TYPE), 'Cerca')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<div class="btn-toolbar">
    <?php echo CHtml::link('Cerca in posta in entrata', array('/document/search', 'doc_type'=>Document::INBOX), array('class'=>'btn')); ?>
    <?php echo CHtml::link('Cerca in documenti pubblici', array('/document/search', 'doc_type'=>Document::OUTGOING), array('class'=>'btn')); ?>    
</div>

<?php $this->renderPartial('_archivesearchform', array('tagsmodel'=>$tagsmodel, 'datemodel'=>$datemodel)); ?>