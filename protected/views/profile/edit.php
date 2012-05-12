<?php $this->pageTitle = "Modifica Profilo"; ?>
<?php $this->breadcrumbs = array('links'=> array('Modifica Profilo')); ?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php $login_form = $this->renderPartial('_login', array('model'=>$pmodel), true); ?>
<?php $personal_info_form = $this->renderPartial('_personal', array('model'=>$model), true); ?>

<?php $this->widget('bootstrap.widgets.BootTabbed', array('type'=>'tabs', 'tabs'=>array(
        array('label'=>'Informazioni Personali', 'content'=>$personal_info_form),
        array('label'=>'Modifica Password', 'content'=>$login_form)    
    ))); ?>

<?php
Yii::app()->clientScript->registerScriptFile('/js/jquery.form.js');
Yii::app()->clientScript->registerScript('yii-active-jqform', "
   function jqFormYii(form, data, hasError)
   {
        var form_id =$(form).attr('id');
        var summaryID = $(form).yiiactiveform.getSettings(form).summaryID;
        if(!hasError)
        {
            $(form).ajaxSubmit({
                cache: false,
                timeout: 10000,
                type: 'POST',
                dataType: 'json',
                beforeSubmit: function()
                {
                    $('#'+form_id+'-success-content').html('');
                    $('#'+form_id+'-success').hide();                                           
                    $('#'+summaryID).html('');
                    $('#'+summaryID).hide();
                },
                success: function(data)
                {
                    if(data.success==1)
                    {
                        $('#'+form_id+'-success-content').html(data.message);
                        $('#'+form_id+'-success').show();                        
                    }
                    else
                    {
                        listErrors(summaryID, data.errors);
                        $('#'+summaryID).show();
                    }
                },
                error: function(xhr)
                {
                    $('#'+summaryID).html('Impossibile completare l\'operazione');
                    $('#'+summaryID).show();
                },
                complete: function()
                {
                    $('#'+form_id+' input[type=\'password\']').clearFields();
                }
            });
        }
        return false;
   }
");
?>