<?php $this->pageTitle = "Nuovo Account"; ?>
<?php $this->breadcrumbs = array('links'=> array('Utenti'=>'/user/', 'Nuovo')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model));