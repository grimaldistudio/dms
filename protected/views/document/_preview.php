<div class="preview_container pull-left span5" >
    <div style="height:700px; width: 100%" id="preview_loader">
        <img src="<?php echo Yii::app()->baseUrl?>/images/ajax-loader.gif" />
    </div>
    <div id="preview_inner_container" style="display:none">
        <img src="" alt="preview"  id="preview_img"/>
        <div id="preview-toolbar buttons-bar" style="text-align: center; width: 100%">
            <?php echo CHtml::link(CHtml::image(Yii::app()->baseUrl.'/images/misc/pdficon_large.png'), $full_size_url, array('class'=>'btn', 'target'=>'_blank')); ?>                                
            <?php echo CHtml::link('<i class="icon-backward"></i>', '#', array('class'=>'btn', 'id'=>'preview_back')); ?>
            <span id="current_page">1</span> di <span id="total_pages"><?php echo $total_pages; ?></span>
            <?php echo CHtml::link('<i class="icon-forward"></i>', '#', array('class'=>'btn', 'id'=>'preview_next')); ?>                        
        </div>
    </div>
</div>

<?php

Yii::app()->clientScript->registerScript("previewer-controls", "
   
   var document_pages = ".$total_pages.";
   var preview_url = '".$preview_url."';
       
    function loadDocumentPreview(preview_url, page)
    {
        $('#preview_inner_container').hide();
        $('#preview_loader').show();
        $('#preview_img').attr('src', preview_url+'&page='+(page-1));
        $('#current_page').text(page);
        if(page>=parseInt($('#total_pages').text()))
            $('#preview_next').addClass('disabled');
        else
            $('#preview_next').removeClass('disabled');            
        if(page<=1)
            $('#preview_back').addClass('disabled');            
        else
            $('#preview_back').removeClass('disabled');                        
    }
    
    $('#preview_img').load( function(){
        $('#preview_loader').hide();
        $('#preview_inner_container').show();
    });
    
    $('#preview_back').live('click', function (e){
        e.preventDefault();
        var current_page = parseInt($('#current_page').text());
        if(current_page<=1)
            return;
        loadDocumentPreview(preview_url, current_page-1);    
        return false;
    });
    
    $('#preview_next').live('click', function (e){
        e.preventDefault();
        var current_page = parseInt($('#current_page').text());
        if(current_page>=parseInt($('#total_pages').text()))
            return;
        loadDocumentPreview(preview_url, current_page+1);
        return false;
    });
    
    loadDocumentPreview(preview_url, 1);
", CClientScript::POS_READY);