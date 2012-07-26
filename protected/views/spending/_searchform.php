<div class="search-form form">
<?php 
$form=$this->beginWidget('bootstrap.widgets.BootActiveForm', array(
		'id'=>'filter-spending-form',
                'type'=>'search',
                'method'=>'GET'
		)
	); 
?>
    <br/>
    
    <div class="row">
        <span class="span3">
        <?php echo $form->textFieldRow($model, 'title'); ?> 
        </span>
        
        <span class="span3">
        <?php echo $form->textFieldRow($model, 'receiver'); ?>
        </span>
    </div>
    
    <div class="row">
        <span class="span3">
        <?php echo $form->textFieldRow($model, 'amount_from'); ?> 
        </span>
        
        <span class="span3">
        <?php echo $form->textFieldRow($model, 'amount_to'); ?>
        </span>
    </div>
    
    <div class="row">
        <span class="span3">
            <?php echo $form->textFieldRow($model, 'spending_date_from', array('id'=>'from')); ?>
        </span>

        <span class="span3">
            <?php echo $form->textFieldRow($model, 'spending_date_to', array('id'=>'to')); ?>
        </span>
    </div>
    
    
<?php echo CHtml::submitButton('Filtra', array('class'=>'btn btn-primary btn-filter')); ?>

<?php 
$this->endWidget();
?>

</div>

<?php Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-i18n.min.js'); ?>
<?php Yii::app()->clientScript->registerScript('filter-spending-controls', "
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
    $.fn.yiiListView.update('spendingslistview', { 
        data: $(this).serialize()
    });
    return false;
});
		
");
?>