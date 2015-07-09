<?php $this->pageTitle = "Dashboard"; ?>
<h1><?php echo $this->pageTitle; ?></h1>
<h3>Documenti caricati nel sistema</h3>
<?php $this->widget('bootstrap.widgets.BootGridView', array(
    'id'=>'dashboard_gridview',
    'dataProvider'=>$model->dashboard(),
    'template'=>"{items}\n{pager}",
    'enableSorting' => true,
    'enablePagination' => true,   
    'itemsCssClass'=>'table table-striped table-bordered table-condensed',
    'nullDisplay' => 'n/d',
    'columns'=>array(
        array('name'=>'identifier'),
        array('name'=>'act_number'),
        array('name'=>'name'),
        array('name'=>'publication_status',
            'header'=>'Pubblicato',
            'htmlOptions'=>array('style'=>'width:1%;'),
            'type'=>'raw',
              'value'=>'($data->publication_status == 1) ? "<img src=\"".Yii::app()->baseUrl."/images/misc/green-spotlight.png\" />" : "<img src=\"".Yii::app()->baseUrl."/images/misc/red-spotlight.png\" />" ',
            'filter'=>false,
            ),
	/*array('filter' =>false,
            'name' => 'main_document_type',
            'value' => '$data->getMainTypeDesc()'),
         * 
         */
		array(
            'class'=>'bootstrap.widgets.BootButtonColumn',
            'htmlOptions'=>array('style'=>'width: 50px'),
            'template'=>'{view} {update} {deletemy} {lock}',
            'buttons'=>array(
                'view'=>array(
                    'label'=>'Vedi',
                    'icon'=>'icon-eye-open',
                    'visible'=>'Yii::app()->user->hasDocumentPrivilege($data->id, AclManager::PERMISSION_READ)'
                ),
                'update'=>array(
                    'label'=>'Modifica',
                    'icon'=>'icon-edit',
                    'visible'=>'Yii::app()->user->hasDocumentPrivilege($data->id, AclManager::PERMISSION_WRITE) || Role::model()->findRole(4)'
                ),
                'deletemy'=>array(
                    'label'=>'Cancella',
                    'icon'=>'icon-trash',
                      'url'=>'Yii::app()->createUrl("/document/deletepending", array("document_name"=>$data->name))',
                    'visible'=>'Yii::app()->user->hasDocumentPrivilege($data->id, AclManager::PERMISSION_WRITE) || Role::model()->findRole(4)',
                    'htmlOptions'=>array('class'=>'btn-delete'),
                ),
                'lock'=>array(
                    'label'=>'Permessi',
                    'icon'=>'icon-ban-circle',
                    'visible'=>'!Yii::app()->user->hasDocumentPrivilege($data->id, AclManager::PERMISSION_READ)',
                    'url'=>'Yii::app()->createUrl("/document/view", array("id"=>$data[\'id\']))'
                ),                
            )
        )

    ),
    'filter'=>$model
)); ?>