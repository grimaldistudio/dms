<?php $this->pageTitle = "Nuova Spesa"; ?>
<?php $this->breadcrumbs = array('links'=> array('Spese'=>'/spending/index', 'Nuova')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model, 'tmp'=>true)); ?>