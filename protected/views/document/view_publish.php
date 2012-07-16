<?php $this->pageTitle = "Documento ".$model->getTitle(); ?>
<?php $this->breadcrumbs = array('links'=> array('Documenti'=>array('/document/search', 'doc_type'=>$model->main_document_type), 'Vedi')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php
if(Yii::app()->user->hasDocumentPrivilege($model->id, AclManager::PERMISSION_ADMIN))
    $this->renderPartial('/documentright/_form', array('model'=>$model));
?>

<div class="row">
    
    <span class="span6 pull-left">
        
        <table class="table table-striped table-condensed detail-view">
            <tbody>

            <tr>
                <th><?php echo $model->getAttributeLabel('main_document_type'); ?></th>
                <td><?php echo $model->getMainTypeDesc(); ?></td>
            </tr>                          

            <tr>
                <th width="30%"><?php echo $model->getAttributeLabel('identifier'); ?></th>
                <td><?php echo CHtml::encode($model->identifier?'#'.$model->identifier:'n/d'); ?></td>
            </tr>
            
            <tr>
                <th><?php echo $model->getAttributeLabel('name'); ?></th>
                <td><?php echo CHtml::encode($model->name); ?></td>
            </tr>

            <tr>
                <th><?php echo $model->getAttributeLabel('description'); ?></th>
                <td><?php echo $model->description; ?></td>
            </tr>

            <tr>
                <th><?php echo $model->getAttributeLabel('entity'); ?></th>
                <td><?php echo $model->entity?CHtml::encode($model->entity):Yii::app()->params['entity']; ?></td>
            </tr>
            
            <tr>
                <th><?php echo $model->getAttributeLabel('proposer_service'); ?></th>
                <td><?php echo $model->proposer_service?CHtml::encode($model->proposer_service):'n/d'; ?></td>
            </tr>
            
            <tr>
                <th><?php echo $model->getAttributeLabel('publication_date_from'); ?></th>
                <td><?php echo $model->publication_date_from?date('d/m/Y', strtotime($model->publication_date_from)):'n/d'; ?></td>
            </tr>
            
            <tr>
                <th><?php echo $model->getAttributeLabel('publication_date_to'); ?></th>
                <td><?php echo $model->publication_date_to?date('d/m/Y', strtotime($model->publication_date_to)):'n/d'; ?></td>
            </tr>
            
            <tr>
                <th><?php echo $model->getAttributeLabel('act_number'); ?></th>
                <td><?php echo $model->act_number?CHtml::encode($model->act_number):'n/d'; ?></td>
            </tr>
            
            <tr>
                <th><?php echo $model->getAttributeLabel('act_date'); ?></th>
                <td><?php echo $model->act_date?date('d/m/Y', strtotime($model->act_date)):'n/d'; ?></td>
            </tr>
            
            <tr>
                <th><?php echo $model->getAttributeLabel('tagsname'); ?></th>
                <td>
                    <?php foreach($model->tags as $tag): ?>
                        <?php 
                            echo $tag->name." ";
                        ?>
                    <?php endforeach; ?>
                </td>
            </tr>
            
            <tr>
                <th><?php echo $model->getAttributeLabel('document_type'); ?></th>
                <td><?php echo $model->getTypeDesc(); ?></td>
            </tr>          
            
            <tr>
                <th><?php echo $model->getAttributeLabel('last_updated'); ?></th>
                <td><?php echo date('d-m-Y H:i:s', strtotime($model->last_updated)); ?></td>
            </tr>          
            
            <tr>
                <th>Pubblicazione su albo richiesta</th>
                <td><?php echo $model->publication_requested?'Si':'No'; ?></td>
            </tr>
            
            <tr>
                <th><?php echo $model->getAttributeLabel('publication_status'); ?></th>
                <td><?php echo $model->getPublicationStatusDesc(); ?></td>
            </tr>
            
            <?php if($model->last_updater_id>0): ?>
            <tr>
                <th><?php echo $model->getAttributeLabel('last_updater_id'); ?></th>
                <td><?php echo $model->last_updater->getFullName(); ?></td>
            </tr>
            <?php endif; ?>

            <tr>
                <th><?php echo $model->getAttributeLabel('revision'); ?></th>
                <td><?php echo $model->revision; ?> (<?php echo CHtml::link('Storico revisioni', array('/document/history'), array('class'=>'open-history')) ?>)</td>
            </tr>          
            <tr>
                <td colspan="2">
                    <?php echo CHtml::link(CHtml::image(Yii::app()->baseUrl.'/images/misc/pdficon_large.png'), array('/document/viewpdf', 'id'=>$model->id), array('class'=>'btn', 'target'=>'_blank')); ?>                    
                    <?php if(Yii::app()->user->hasDocumentPrivilege($model->id, AclManager::PERMISSION_WRITE)): ?>
                        <?php if($this->isAllowed('document', 'update'))  echo CHtml::link('<i class="icon-edit icon-white"></i> Modifica', array('/document/update', 'id'=>$model->id), array('class'=>'btn btn-primary')); ?>
                    <?php endif; ?>
                    <?php if(Yii::app()->user->isAdmin() || $model->creator_id == Yii::app()->user->id): ?>
                        <?php if($this->isAllowed('document', 'updaterights')) echo CHtml::htmlButton('<i class="icon-share icon-white"></i> Condividi', array('class'=>'btn btn-primary open_document_rights')); ?>
                    <?php endif; ?>
                </td>
            </tr>          
            </tbody>
        </table>
        
    </span>

        <?php $this->renderPartial('_preview', array(
                                            'total_pages'=>$model->num_pages, 
                                            'full_size_url'=> array('/document/viewpdf', 'id'=>$model->id), 
                                            'preview_url'=>Yii::app()->createUrl('/document/previewdoc', array('id'=>$model->id,'t'=>time()))
                                    )); ?>

</div>

<?php
$this->renderPartial('//documenthistory/_summary', array('revisions'=>$revisions, 'revisions_count'=>$revisions_count));
?>