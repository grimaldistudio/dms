<?php $this->pageTitle = "Cerca in: Archivio personale"; ?>
<?php $this->breadcrumbs = array('links'=> array('Documenti'=>array('/document/search', 'doc_type'=>Document::INTERNAL_USE_TYPE), 'Cerca')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_archivesearchform', array('tagsmodel'=>$tagsmodel, 'datemodel'=>$datemodel)); ?>