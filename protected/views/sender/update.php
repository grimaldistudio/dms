<?php $this->pageTitle = "Modifica Mittent: ".$model->name; ?>
<?php $this->breadcrumbs = array('links'=> array('Mittenti'=>'/sender/index', 'Modifica')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model));