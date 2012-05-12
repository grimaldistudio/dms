<?php $this->pageTitle = "Modifica Tag: ".$model->name; ?>
<?php $this->breadcrumbs = array('links'=> array('Tags'=>'/tag/index', 'Modifica')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model));