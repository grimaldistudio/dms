<?php $this->pageTitle = "Aggiorna Ticket: ".$model->getTitle(); ?>
<?php $this->breadcrumbs = array('links'=> array('Tickets'=>'/ticket/index', 'Aggiorna')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('//ticket/_updateform', array('model'=>$model));