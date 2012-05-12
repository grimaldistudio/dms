<?php $this->pageTitle = "Nuovo Permesso"; ?>
<?php $this->breadcrumbs = array('links'=> array('Permessi'=>'/right/', 'Nuovo')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model));