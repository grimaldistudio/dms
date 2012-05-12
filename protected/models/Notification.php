<?php

class Notification extends CActiveRecord
{

    const UNREAD_STATUS = 0;
    const READ_STATUS = 1;
    
    /**
    * Returns the static model of the specified AR class.
    * @return CActiveRecord the static model class
    */
    public static function model($className=__CLASS__)
    {
        return CActiveRecord::model($className);
    }

    /**
    * @return string the associated database table name
    */
    public function tableName()
    {
        return 'user_notifications';
    }

    /**
    * @return array validation rules for model attributes.
    */
    public function rules()
    {
        return array(
            array('description,status', 'safe', 'on'=>'search')
        );
    }

    /**
    * @return array relational rules.
    */
    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id' ),
            'document' => array(self::BELONGS_TO, 'Document', 'document_id')
        );
    }

    /**
    * @return array customized attribute labels (name=>label)
    */
    public function attributeLabels()
    {
        return array(
            'id' => 'Id',
            'description' => 'Descrizione',
            'link' => 'Link',
            'date_crated' => 'Data di creazione',
            'last_updated' => 'Ultimo aggiornamento',
            'status' => 'Stato',
            'document_id' => 'Documento',
            'user_id' => 'Utente'
        );
    }

    function __toString()
    {
        return $this->description;
    }
    
    public function beforeSave()
    {
        if ($this->isNewRecord){
            $this->date_created = new CDbExpression('CURRENT_TIMESTAMP');
            $this->status = self::UNREAD_STATUS;
        }
        $this->last_updated = new CDbExpression('CURRENT_TIMESTAMP');
        return parent::beforeSave();
    }

    public function getStatusArray()
    {
        return array(
            '' => 'Seleziona',		
            self::UNREAD_STATUS => 'Da leggere',
            self::READ_STATUS => 'Letta',
        );
    }

    public function getStatusDesc()
    {
        $status_array = $this->getStatusArray();
        if(array_key_exists($this->status, $status_array))
            return $status_array[$this->status];
        return 'Non definito';
    }
    
    public function markAsRead()
    {
        $this->status = self::READ_STATUS;
        return $this->save();
    }
    
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('description',$this->description,true);
        $criteria->compare('status',$this->status);
        $criteria->compare('user_id',Yii::app()->user->id);
        
        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
            'sort'=>array(
                'defaultOrder'=>'date_created DESC',
            ),
            'pagination'=>array(
                'pageSize'=>20
            ),
        ));	        
    }
    
    public function countUnread($user_id)
    {
        $sql = "SELECT count(id) FROM user_notifications WHERE user_id = :user_id AND status = :unread_status";
        return Yii::app()->db->createCommand($sql)->queryScalar(array(':user_id'=>$user_id, ':unread_status'=>self::UNREAD_STATUS));
    }
    
    public function findAllUnread($user_id, $limit = 5)
    {
        $sql = "SELECT id, description, link, document_id FROM user_notifications WHERE user_id = :user_id AND status = :unread_status ORDER BY date_created DESC LIMIT ".$limit;
        return Yii::app()->db->createCommand($sql)->queryAll(true, array(':user_id'=>$user_id, ':unread_status'=>self::UNREAD_STATUS));
    }
    
    public function markRead()
    {
        $this->last_updated = new CDbExpression('CURRENT_TIMESTAMP');
        $this->status = self::READ_STATUS;
        return $this->save();
    }
    
    public function markAll($user_id)
    {
        $sql = "UPDATE user_notifications SET status = :read_status WHERE user_id = :user_id";
        if(Yii::app()->db->createCommand($sql)->execute(array(':user_id'=>$user_id, ':read_status'=>self::READ_STATUS)))
            return true;
        else
            return false;
    }

    public function isUnread()
    {
        return $this->status==self::UNREAD_STATUS;
    }
}