<?php $this->pageTitle = "Nuovo Ticket"; ?>
<?php $this->breadcrumbs = array('links'=> array('Ticket'=>'/ticket/index', 'Nuovo')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->renderPartial('//ticket/_form', array('document'=>$document, 'model'=>$model));
