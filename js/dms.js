function listErrors(container_id, errors)
{
    var error_container = $('#'+container_id);
    var content = '';
    content += '<b>Si sono verificati i seguenti errori:</b><br/>';
    content += '<ul>';
    $.each(errors, function(i, elem)
    {
        $.each(elem, function(j, message)
        {
            content += '<li>'+message+'</li>';
        });
    });
    content += '</ul>';
    error_container.html(content);
}

function addNotification(index, notification)
{
    var notifications_container = $('#notification_content');
    var notification_link = $('<a></a>');
    notification_link.attr('href', notification.link);
    notification_link.text(notification.description);
    notification_link.addClass('notification_item');
    notifications_container.append(notification_link);
    notifications_container.append('<br/>');
}

function updateNotifications(notifications_count, notifications, full_notifications_url)
{
    var notifications_count_container = $('span.notifications_container');
    notifications_count_container.text(notifications_count);
    
    var notifications_container = $('#notification_content');
    notifications_container.empty();

    if(notifications.length>0)
    {
        notifications_count_container.addClass('notifications_highlight');        
        jQuery.each(notifications, addNotification);
        var read_more_link = $('<a></a>');
        read_more_link.attr('href', full_notifications_url);
        read_more_link.addClass('read_more');
        read_more_link.text('Leggi tutte');
        notifications_container.append(read_more_link);
    }
    else
    {
        notifications_count_container.removeClass('notifications_highlight');
        notifications_container.text('Nessuna notifica da leggere');
    }
}

function loadNotifications(notification_url, full_notifications_url)
{
    $.ajax({
        url: notification_url,
        type: 'GET',
        cache: false,
        dataType: 'json',
        success: function(data)
        {
            updateNotifications(data.count, data.notifications, full_notifications_url);
        }
    });
}