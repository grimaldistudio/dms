<?php $this->pageTitle = "Aggiorna Spesa"; ?>
<?php $this->breadcrumbs = array('links'=> array('Spese'=>'/spending/search', 'Aggiorna')); ?>
<h1><?php echo $this->pageTitle; ?></h1>
<div class="btn-toolbar">
    <?php 
    if($model->isActive())
        echo CHtml::link('Rimuovi dall\'elenco', array('/spending/disable', 'id'=>$model->id), array('class'=>'btn btn-primary', 'confirm'=>'Sei sicuro di voler rimuovere la spesa dall\'elenco?'));
    else{
        echo CHtml::link('Ripristina nell\'elenco', array('/spending/enable', 'id'=>$model->id), array('class'=>'btn btn-primary', 'confirm'=>'Sei sicuro di voler ripristinare la spesa nell\'elenco?'));        
        echo CHtml::link('Rimuovi definitivamente', array('/spending/delete', 'id'=>$model->id), array('class'=>'btn btn-primary', 'confirm'=>'Sei sicuro di voler rimuovere definitivamente la spesa?'));
    }    
    ?>
</div>
<?php $this->renderPartial('_form', array('model'=>$model, 'tmp'=>false)); ?>