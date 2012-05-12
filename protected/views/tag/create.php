<?php $this->pageTitle = "Nuovo Tag"; ?>
<?php $this->breadcrumbs = array('links'=> array('Tags'=>'/tag/', 'Nuovo')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model));
