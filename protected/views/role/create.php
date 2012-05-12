<?php $this->pageTitle = "Nuovo Ruolo"; ?>
<?php $this->breadcrumbs = array('links'=> array('Ruoli'=>'/role/', 'Nuovo')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model));
