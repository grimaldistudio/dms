<?php $this->pageTitle = $document->name.": History"; ?>
<?php $this->breadcrumbs = array('links'=> array('Documenti'=>'/document/my', 'History')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->widget('bootstrap.widgets.BootGridView', array(
    'id'=>'history_gridview',
    'dataProvider'=>$model->search(),
    'template'=>"{items}\n{pager}",
    'itemsCssClass'=>'table table-striped table-bordered table-condensed',
    'columns'=>array(
        array('name'=>'useremail'),
        array('name'=>'revision'),
        array('name'=>'description'),
        array('name'=>'date_created', 'type'=>'date', 'value'=>  'strtotime($data->date_created)', 'filter'=>false),
    ),
    'filter'=>$model
)); ?>