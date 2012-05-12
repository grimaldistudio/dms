<div class="search-form form">
<?php 
$form=$this->beginWidget('bootstrap.widgets.BootActiveForm', array(
		'id'=>'filter-document-form',
                'type'=>'search',
                'method'=>'GET'
		)
	); 
?>
    <div class="row">
        <span class="span3">
        <?php echo $form->textFieldRow($model, 'identifier'); ?> 
        </span>
        
        <span class="span3">
        <?php echo $form->textFieldRow($model, 'name'); ?>
        </span>
    </div>
    
    <div class="row">
        <span class="span3">
            <?php echo $form->textFieldRow($model, 'date_received_from', array('id'=>'from')); ?>
        </span>
        
        <span class="span3">
            <?php echo $form->textFieldRow($model, 'date_received_to', array('id'=>'to')); ?>
        </span>
    </div>
    
    

<?php echo CHtml::submitButton('Filtra', array('class'=>'btn btn-primary btn-filter')); ?>

<?php 
$this->endWidget();
?>

</div>

<?php Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-i18n.min.js'); ?>
<?php Yii::app()->clientScript->registerScript('filter-document-controls', "
$.datepicker.setDefaults( $.datepicker.regional['it'] );    
var dates = $( '#from, #to' ).datepicker({
			defaultDate: '+1w',
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

$('#filter-document-form').submit(function(){
    $.fn.yiiListView.update('documentslistview', { 
        data: $(this).serialize()
    });
    return false;
});
		
");
?>