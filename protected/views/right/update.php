<?php $this->pageTitle = "Modifica: ".$model->name.' ('.$model->key.')'; ?>
<?php $this->breadcrumbs = array('links'=> array('Permessi'=>'/right/index', 'Nuovo')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model));