<?php $this->pageTitle = "Documenti visibili a tutti"; ?>
<?php $this->breadcrumbs = array('links'=> array('Documenti'=>'/document/public', 'Visibili a tutti')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php 
$this->renderPartial('_filterform', array('model'=>$model));

$this->widget('bootstrap.widgets.BootListView', array(
		'dataProvider'=> $model->publicd(),
		'itemView'=>'_minidoc',   
        'template'=>"{items}\n{pager}",    
		'id' => 'documentslistview',
		'sortableAttributes'=>array(
				'identifier' => 'Numero di Protocollo',
				'name' => 'Nome',
                'date_created' => 'Data di creazione'
		),
));
?>