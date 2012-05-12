<?php
class NotificationController extends SecureController{

    private $_model;
    
    public function actionIndex()
    {
        $model = new Notification('search');
        if(isset($_GET['Notification']))
            $model->attributes = $_GET['Notification'];

        $params =array(
            'model'=>$model,
        );

        if(!isset($_GET['ajax']))
            $this->render('list', $params);
        else
            $this->renderPartial('list', $params);                  
    }
    
    public function actionMarkread()
    {
        $model = $this->loadModel();
        if($model->markRead())
        {
            echo "OK";
            Yii::app()->end();
        }
        throw new CHttpException(500, 'Impossibile vedere la notifica');
    }
    
    public function actionView()
    {
        $model = $this->loadModel();
        if($model->markRead())
            $this->redirect($model->link);
        else
            throw new CHttpException(500, 'Impossibile vedere la notifica');
    }
    
    public function actionNew()
    {
        $notifications_count = Notification::model()->countUnread(Yii::app()->user->id);
        $notifications = Notification::model()->findAllUnread(Yii::app()->user->id, 5);
        $notifications_data = array();
        foreach($notifications as $notification)
        {
            $notification['link'] = Yii::app()->createAbsoluteUrl('/notification/view', array('id'=>$notification['id']));
            $notifications_data[] = $notification;
        }
        echo CJSON::encode(array('count'=>$notifications_count, 'notifications'=>$notifications_data));
        Yii::app()->end();
    }
    
    public function actionMarkall()
    {
        $n = new Notification();
        if($n->markAll(Yii::app()->user->id))
        {
            echo "OK";
            Yii::app()->end();
        }
        throw new CHttpException(500, 'Impossibile segnare come lette tutte le notifiche');
    }
    
    protected function loadModel()
    {
        if($this->_model===null)
        {
            if(isset($_GET['id']))
            {
                $this->_model=Notification::model()->findByPk($_GET['id']);
            }

            if($this->_model===null || $this->_model->user_id != Yii::app()->user->id)
                throw new CHttpException(404,'La pagina richiesta non esiste.');

        }

        return $this->_model;        
    }
    
}

?>
