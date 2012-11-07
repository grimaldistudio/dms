<?php $this->pageTitle = "Cerca in: Documenti pubblici"; ?>
<?php $this->breadcrumbs = array('links'=> array('Documenti'=>array('/document/search', 'doc_type'=>Document::OUTGOING), 'Cerca')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_publishsearchform', array('idmodel'=>$idmodel, 'tagsmodel'=>$tagsmodel, 'datemodel'=>$datemodel)); ?>