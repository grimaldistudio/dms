<?php $this->pageTitle = "Modifica: ".$model->name; ?>
<?php $this->breadcrumbs = array('links'=> array('Gruppi'=>'/group/index', 'Nuovo')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model));