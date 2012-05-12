<?php $this->beginWidget('bootstrap.widgets.BootModal', array(
    'id'=>'document_rights_dialog',
    'htmlOptions'=>array('class'=>'hide'),
)); ?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Controlla Accesso al Documento</h3>
</div>
<div class="modal-body">
<p>Proprietario: <b><?php echo $model->creator->firstname.' '.$model->creator->lastname; ?></b></p>

<h3>Utenti</h3>
<table class="table table-striped table-condensed" id="user_table">
</table>
<label>Aggiungi Utente:</label>
<input type="text" name="user_autocomplete" id ="user_autocomplete" val="" />

<h3>Gruppi</h3>
<table class="table table-striped table-condensed" id="group_table">
</table>
<label>Aggiungi Gruppo:</label>
<input type="text" name="group_autocomplete" id ="group_autocomplete" val="" />
</div>
<div class="modal-footer">
    <?php echo CHtml::link('Chiudi', '#', array('class'=>'btn', 'data-dismiss'=>'modal')); ?>
    <?php echo CHtml::link('Salva', '#', array('class'=>'btn btn-primary save-rights')); ?>
</div>
<?php $this->endWidget(); ?>



<?php Yii::app()->clientScript->registerScript('document-rights-form', "
    var group_ids = [];
    var user_ids = [];
    var persisted_group_ids = [];
    var persisted_user_ids = [];
    
    var group_table = $('#group_table');
    var user_table = $('#user_table');
    var rights_options = ".CJSON::encode(DocumentRight::model()->getPrivilegeArray()).";
    var delete_template = '".CHtml::link('<i class="icon-trash"></i>', '#', array('class'=>'btn delete_link', 'style'=>'margin-bottom: 9px'))."';

    function addUser(item)
    {
        row = {};
        row.user_id = item.id;
        row.label = item.label;
        row.right = 1;
        if(addUserId(item.id))
            renderUserRow(row);
    }
    
    function removeUser(user_id)
    {
        var u_id = user_id;

        index = isPersistedUserId(u_id);
        if(index<0)
        {
            removeUserId(u_id);        
            removeUserRow(u_id);
            return false;
        }
        
        $.ajax({
           url: '".Yii::app()->createUrl('/document/removeUser', array('id'=>$model->id))."',
           cache: false,
           data: { user_id: u_id },
           success: function(data)
           {
               removePersistedUserId(u_id);
               removeUserId(u_id);
               removeUserRow(u_id);
           },
           error: function(xhr)
           {
                alert('Impossibile rimuovere il peremsso');
           }
        });
    }
    
    function removeUserRow(user_id)
    {
        user_table.find('tr#tr_'+user_id).remove();
    }
    
    function removeGroupRow(group_id)
    {
        group_table.find('tr#tr_'+group_id).remove();
    }
    
    function removeGroup(group_id)
    {
        var g_id = group_id;
        index = isPersistedGroupId(g_id);
        if(index<0)
        {
            removeGroupId(g_id);        
            removeGroupRow(g_id);
            return false;
        }
        
        $.ajax({
           url: '".Yii::app()->createUrl('/document/removeGroup', array('id'=>$model->id))."',
           cache: false,
           data: { group_id: g_id },
           success: function(data)
           {
               removePersistedGroupId(g_id);
               removeGroupId(g_id);
               removeGroupRow(g_id);
           },
           error: function(xhr)
           {
                alert('Impossibile rimuovere il permesso');
           }
        });
    }
    
    function addGroup(item)
    {
        row = {};
        row.group_id = item.id;
        row.label = item.label;
        row.right = 1;
        if(addGroupId(item.id))
            renderGroupRow(row);
    }
    
    $('a.reload').live('click', function(e){
        e.preventDefault();
        reloadRights();
    });
    
    $('.open_document_rights').live('click', function(e){
        e.preventDefault();
        reloadRights();
    });
    
    $('a.save-rights').live('click', function(e)
    {
        e.preventDefault();
        $(e.currentTarget).addClass('disabled');
        save();
    });
    
    function updateInternalData(data)
    {
        new_user_ids = [];
        new_group_ids = [];
        
        for(var i=0; i<data.length; i++)
        {
            if(data[i].user_id>0)
            {
                new_user_ids.push(data[i].user_id);
            }
            else
            {
                new_group_ids.push(data[i].group_id);
            }
        }
        user_ids = new_user_ids;
        persisted_user_ids = new_user_ids.slice(0);
        group_ids = new_group_ids;
        persisted_group_ids = new_group_ids.slice(0);
    }

    function hasUserId(user_id)
    {
        return jQuery.inArray(user_id, user_ids);
    }
    
    function hasGroupId(group_id)
    {
        return jQuery.inArray(group_id, group_ids);    
    }
    
    function isPersistedUserId(user_id)
    {
        return jQuery.inArray(user_id, persisted_user_ids);
    }
    
    function isPersistedGroupId(group_id)
    {
        return jQuery.inArray(group_id, persisted_group_ids);    
    }
    
    function removeUserId(user_id)
    {
        index = hasUserId(user_id);
        if(index>=0)
        {
            user_ids.splice(index, 1);
            return true;
        }
        return false;
    }
    
    function removeGroupId(group_id)
    {
        index = hasGroupId(group_id);
        if(index>=0)
        {
            group_ids.splice(index, 1);
            return true;
        }
        return false;
    }
    
    function removePersistedUserId(user_id)
    {
        index = isPersistedUserId(user_id);
        if(index>=0)
        {
            persisted_user_ids.splice(index, 1);
            return true;
        }
        return false;
    }
    
    function removePersistedGroupId(group_id)
    {
        index = isPersistedGroupId(group_id);
        if(index>=0)
        {
            persisted_group_ids.splice(index, 1);
            return true;
        }
        return false;
    }
    
    function addGroupId(group_id)
    {
        index = hasGroupId(group_id);
        if(index<0)
        {
            group_ids.push(group_id);    
            return true;
        }
        return false;
    }
    
    function addUserId(user_id)
    {
        index = hasUserId(user_id);
        if(index<0)
        {
            user_ids.push(user_id);        
            return true;
        }
        return false;
    }

    function reloadRights()
    {
        $.ajax({
            url: '".Yii::app()->createUrl('/document/rights', array('id'=>$model->id))."',
            dataType: 'json',
            cache: false,
            success: function(data)
            {
                updateInternalData(data);
                renderGroupTable(data);
                renderUserTable(data);
                $('#document_rights_dialog').modal('show');
            }
        });
    }
    
    function renderGroupTable(data)
    {
        group_table.empty();
        for(var i=0; i<data.length; i++)
        {
            if(data[i].group_id>0)
            {
                renderGroupRow(data[i]);
            }
        }
    }
    
    function renderUserRow(row)
    {
        var urow = $('<tr></tr>');
        urow.attr('id', 'tr_'+row.user_id);
        var name_td = $('<td style=\"width:70%\"></td>');
        if(row.label)
            name_td.text(row.label);            
        else
            name_td.text(row.firstname + ' ' + row.lastname + ' ('+row.u_email+')');
        urow.append(name_td);
        var options_td = $('<td></td>');
        var options_select = $('<select></select>');
        options_select.attr('id', 'u_'+row.user_id+'_right');
        options_select.attr('name', 'u_'+row.user_id+'_right');        
        for(var key in rights_options)
        {
            var option = $('<option></option>');
            option.attr('value', key);
            if(key==row.right)
                option.attr('selected', 'selected');
            option.text(rights_options[key]);
            options_select.append(option);
        }
        options_td.append(options_select);
        var delete_btn = $(delete_template);
        delete_btn.attr('id', 'ud_'+row.user_id);
        delete_btn.addClass('user_delete_link');
        options_td.append(delete_btn);        
        urow.append(options_td);
        user_table.append(urow);    
    }
    
    function renderGroupRow(row)
    {
        var grow = $('<tr></tr>');
        grow.attr('id', 'tr_'+row.group_id);
        var name_td = $('<td style=\"width:70%\"></td>');
        if(row.label)
            name_td.text(row.label);            
        else        
            name_td.text(row.name + '('+row.g_email+')');
        grow.append(name_td);
        var options_td = $('<td></td>');
        var options_select = $('<select></select>');
        options_select.attr('id', 'g_'+row.group_id+'_right');        
        options_select.attr('name', 'g_'+row.group_id+'_right');
        for(var key in rights_options)
        {
            var option = $('<option></option>');
            option.attr('value', key);
            if(key==row.right)
                option.attr('selected', 'selected');
            option.text(rights_options[key]);
            options_select.append(option);
        }
        options_td.append(options_select);
        
        var delete_btn = $(delete_template);
        delete_btn.attr('id', 'gd_'+row.group_id);
        delete_btn.addClass('group_delete_link');        
        options_td.append(delete_btn);
        grow.append(options_td);
        group_table.append(grow);
    }
    
    function renderUserTable(data)
    {
        user_table.empty();    
        for(var i=0; i<data.length; i++)
        {
            if(data[i].user_id>0)
            {
                renderUserRow(data[i]);
            }
        }        
    }

    function serializeDocumentRightsData()
    {
        var post_data = {};
        for(var i=0; i<user_ids.length; i++)
        {
            user_id = user_ids[i];
            post_data['u_'+user_id] = $('#u_'+user_id+'_right').val();
        }
        post_data['user_ids'] = user_ids.join(',');
        
        for(var j=0; j<group_ids.length; j++)
        {
            group_id = group_ids[j];
            post_data['g_'+group_id] = $('#g_'+group_id+'_right').val();
        }
        post_data['group_ids'] = group_ids.join(',');
        
        return post_data;
    }
    
    function save()
    {
        $.ajax({
            url: '".Yii::app()->createUrl('/document/updaterights', array('id'=>$model->id))."',
            type: 'POST',
            data: serializeDocumentRightsData(),
            dataType: 'json',
            success: function(data)
            {
                updateInternalData(data);
                renderGroupTable(data);
                renderUserTable(data);
            },
            error: function(xhr)
            {
                alert('Si è verificato un errore durante l\'aggiornamento dei permessi del documento');
            },
            complete: function()
            {
                $('a.save-rights').removeClass('disabled');
            }
        });
    }
    
    $('a.user_delete_link').live('click', function(e){
        e.preventDefault();
        if(!confirm('Sei sicuro di voler procedere?'))
            return false;
        var link = $(e.currentTarget);
        var user_id = link.attr('id').replace('ud_', '');
        removeUser(user_id);
    });
    
    $('a.group_delete_link').live('click', function(e){
        e.preventDefault();
        if(!confirm('Sei sicuro di voler procedere?'))
            return false;        
        var link = $(e.currentTarget);
        var group_id = link.attr('id').replace('gd_', '');
        removeGroup(group_id);
    });

    $('#user_autocomplete').autocomplete(
    {
        minLength: 2,
        source: '".Yii::app()->createUrl('/user/autocompleter')."',
        select: function( event, ui ) {
            // add to model
            addUser(ui.item);
            $('#user_autocomplete').val('');
            return false;
        },
    });
    
    $('#group_autocomplete').autocomplete(
        {
        minLength: 2,
        source: '".Yii::app()->createUrl('/group/autocomplete')."',
        select: function( event, ui ) {
            addGroup(ui.item);
            $('#group_autocomplete').val('');
            return false;
        },
    });
"); ?>