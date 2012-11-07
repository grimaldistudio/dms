<?php $this->pageTitle = "Cerca in: Posta in entrata/uscita"; ?>
<?php $this->breadcrumbs = array('links'=> array('Documenti'=>'/document/search', 'Cerca')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_protocolsearchform', array('idmodel'=>$idmodel, 'tagsmodel'=>$tagsmodel, 'datemodel'=>$datemodel)); ?>