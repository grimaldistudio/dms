<?php $this->pageTitle = "Nuovo Mittent"; ?>
<?php $this->breadcrumbs = array('links'=> array('Mittenti'=>'/sender/index', 'Nuovo')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model));
