<?php $this->pageTitle = "Cerca Documenti"; ?>
<?php $this->breadcrumbs = array('links'=> array('Documenti'=>'/document/search', 'Cerca')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_searchform', array('idmodel'=>$idmodel, 'tagsmodel'=>$tagsmodel, 'datemodel'=>$datemodel)); ?>