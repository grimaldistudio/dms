<?php $this->pageTitle = "Nuovo Gruppo"; ?>
<?php $this->breadcrumbs = array('links'=> array('Gruppi'=>'/group/', 'Nuovo')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model));