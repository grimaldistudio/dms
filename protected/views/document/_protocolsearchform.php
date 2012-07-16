<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'document-identifier-search-form',
    'type'=>'search',
    'method'=>'GET',
    'action' => array('/document/search'),
    'htmlOptions'=>array('class'=>'well document-search-form'),
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'validateOnChange'=>false,
        'inputContainer'=>'div.control-group',
    )
)); ?>

<?php $form->errorSummary($idmodel); ?> 

<?php echo CHtml::hiddenField('s_type', 'identifier'); ?>

<div class="control-group">
<label class="big">N. Protocollo</label>
<?php echo $form->textField($idmodel, 'identifier', array('class'=>'span6 big-input')); ?> 
<?php echo CHtml::htmlButton('Cerca', array('class' => 'btn btn-primary', 'type'=>'submit')); ?>
<?php echo $form->error($idmodel, 'identifier'); ?>
</div>

    
<?php 
$this->endWidget();
?>

<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'document-tags-search-form',
    'type' => 'search',
    'method'=>'GET',
    'action' => array('/document/search'),
    'htmlOptions'=>array('class'=>'well document-search-form'),
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'validateOnChange'=>false,
        'inputContainer'=>'div.control-group',
    )
)); ?>

<?php $form->errorSummary($tagsmodel); ?> 

<?php echo CHtml::hiddenField('s_type', 'tags'); ?>

<div class="control-group">
<label class="big">Tags</label> 
<?php echo $form->textField($tagsmodel, 'tags', array('class'=>'span6 big-input', 'id'=>'tags')); ?> 
<?php echo CHtml::htmlButton('Cerca', array('class' => 'btn btn-primary', 'type'=>'submit')); ?>
<?php echo $form->error($tagsmodel, 'tags'); ?>
<span class="help-block">Digita uno o più tag (max. 3) in base a cui cercare i documenti. Ad es. "anagrafe", "certificato", "nascita".</span>

</div>

    
<?php 
$this->endWidget();
?>

<?php $form = $this->beginWidget('bootstrap.widgets.BootActiveForm', array(
    'id'=>'document-date-search-form',
    'type'=>'search',
    'method'=>'GET',
    'action' => array('/document/search'),
    'htmlOptions'=>array('class'=>'well document-search-form'),
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'validateOnChange'=>false,
        'inputContainer'=>'div.control-group',
    )
)); ?>

<?php $form->errorSummary($datemodel); ?> 

<?php echo CHtml::hiddenField('s_type', 'date'); ?>

<div class="control-group">
<label class="big">Data di ricezione</label>
<?php echo $form->textField($datemodel, 'date_from', array('class'=>'span3 big-input', 'id'=>'from')); ?> 
<?php echo $form->textField($datemodel, 'date_to', array('class'=>'span3 big-input', 'id'=>'to')); ?> 
<?php echo CHtml::htmlButton('Cerca', array('class' => 'btn btn-primary', 'type'=>'submit')); ?>
<?php echo $form->error($datemodel, 'date_from'); ?>
<?php echo $form->error($datemodel, 'date_to'); ?>
</div>

    
<?php 
$this->endWidget();
?>

<?php Yii::app()->clientScript->registerPackage('tokenInput'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-i18n.min.js'); ?>
<?php Yii::app()->clientScript->registerScript('document-search-controls', "
$.datepicker.setDefaults( $.datepicker.regional['it'] );

var dates = $( '#from, #to' ).datepicker({
            defaultDate: new Date(),
            changeMonth: true,
            numberOfMonths: 2,
            dateFormat: 'dd/mm/yy',
            onSelect: function( selectedDate ) {
                    var option = this.id == 'from' ? 'minDate' : 'maxDate',
                            instance = $( this ).data( 'datepicker' ),
                            date = $.datepicker.parseDate(
                                    instance.settings.dateFormat ||
                                    $.datepicker._defaults.dateFormat,
                                    selectedDate, instance.settings );
                    dates.not( this ).datepicker( 'option', option, date );
            }
    });  


    $('#tags').tokenInput(
        '".Yii::app()->createUrl('/tag/autocompletetoken')."',
        {
            queryParam: 'term',
            minChars: 2,
            theme: 'facebook',
            tokenLimit: 3,
            preventDuplicates: true,
            hintText: 'Digita un tag',
            noResultsText: 'Nessun tag trovato',
            searchingText: 'Ricerca in corso...',
            prePopulate: ".CJSON::encode($tagsmodel->getSelectedTags())."
        }
    );
    
    $('#filter-document-form').submit(function(){
        $.fn.yiiListView.update('documentslistview', { 
            data: $(this).serialize()
        });
        return false;
    });
"); ?>