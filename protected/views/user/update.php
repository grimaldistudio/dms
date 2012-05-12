<?php $this->pageTitle = "Modifica: ".$model->firstname.' '.$model->lastname; ?>
<?php $this->breadcrumbs = array('links'=> array('Utenti'=>'/user/index', 'Nuovo')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model));