<?php

class Ticket extends CActiveRecord
{
    
    const OPEN_STATUS = 0;
    const APPROVED_STATUS = 1;
    const REJECTED_STATUS = 2;

    public $user_email;
    public $replier_email;
    public $document_title;
    
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
        return 'users_document_requests';
    }

    /**
    * @return array validation rules for model attributes.
    */
    public function rules()
    {
        return array(
            array('request,access_level', 'required', 'on'=>'create'),
            array('access_level', 'numerical', 'on'=>'create'),
            array('request', 'length', 'max'=>2048, 'on'=>'create'),
            array('reply,granted_access_level', 'required', 'on'=>'update'),
            array('access_level', 'numerical', 'on'=>'update'),            
            array('reply', 'length', 'max'=>2048, 'on'=>'update'),
        );
    }

    /**
    * @return array relational rules.
    */
    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id' ),
            'document' => array(self::BELONGS_TO, 'Document', 'document_id'),
            'replier' => array(self::BELONGS_TO, 'User', 'replier_id')
        );
    }

    /**
    * @return array customized attribute labels (name=>label)
    */
    public function attributeLabels()
    {
        return array(
            'id' => 'Id',
            'request' => 'Richiesta',
            'reply' => 'Risposta',
            'document_id' => 'Documento',
            'user_id' => 'Richiedente',
            'replier_id' => 'Risposto Da',
            'access_level' => 'Livello di Accesso Richiesto',
            'granted_access_level' => 'Livello di Accesso Concesso',
            'status' => 'Stato Richiesta',
            'date_created' => 'Data di creazione',
            'last_updated' => 'Ultimo aggiornamento',
            'user_email' => 'Creato da',
            'replier_email' => 'Risposto da',
            'document_title' => 'Documento'
        );
    }

    public function beforeSave()
    {
        if ($this->isNewRecord){
            $this->status = self::OPEN_STATUS;
            $this->date_created = new CDbExpression('CURRENT_TIMESTAMP');
        }
        $this->last_updated = new CDbExpression('CURRENT_TIMESTAMP');
        return parent::beforeSave();
    }

    public function closeTicket()
    {
        if($this->granted_access_level==0)
        {
            $this->status = self::REJECTED_STATUS;
        }
        else
        {
            $this->status = self::APPROVED_STATUS;
        }
        
        $this->replier_id = Yii::app()->user->id;
        
        $t = Yii::app()->db->beginTransaction();
        
        if($this->save() && DocumentRight::model()->addPermission($this->user_id, $this->document_id, $this->granted_access_level))
        {
            $t->commit();
            return true;
        }
        $t->rollback();
        return false;
    }
    
    public function createTicket()
    {
        if($this->save())
        {
           try {
               $n = new Notification('create');
               $n->user_id = $this->document->creator_id;
               $n->document_id = $this->document_id;
               $n->link = Yii::app()->createUrl('/ticket/update', array('id'=>$this->id));
               $n->date_created = new CDbExpression('CURRENT_TIMESTAMP');
               $n->last_updated = new CDbExpression('CURRENT_TIMESTAMP');               
               $n->status = Notification::UNREAD_STATUS;
               $n->description = "Richiesta di accesso da gestire";
               $n->save();
           }
           catch(Exception $e)
           {
               
           }
           return true;
        }
    }
    
    public function getStatusArray()
    {
        return array(
            '' => 'Seleziona',		
            self::OPEN_STATUS => 'Richiesta Aperta',
            self::APPROVED_STATUS => 'Richiesta Approvata',
            self::REJECTED_STATUS => 'Richiesta Respinta'
        );
    }

    public function getStatusDesc()
    {
        $status_array = $this->getStatusArray();
        if(array_key_exists($this->status, $status_array))
            return $status_array[$this->status];
        return 'Non definito';
    }

    public function getAccessLevelDesc($value = null)
    {
        if($value!=null)
            $this->access_level = $value;
        $access_level_array = DocumentRight::model()->getPrivilegeArray();
        if(array_key_exists($this->access_level, $access_level_array))
                return $access_level_array[$this->access_level];
        return 'Non definito';
    }
    
    public function getGrantedAccessLevelArray()
    {
        $arr = array(
            0 => 'Accesso negato',
        );
        $access_level_array = DocumentRight::model()->getPrivilegeArray();
        return array_merge($arr, $access_level_array);
        
    }
    public function getGrantedAccessLevelDesc($value = null)
    {
        if($value!=null)
            $this->granted_access_level = $value;        
        $granted_access_level_array = $this->getGrantedAccessLevelArray();
        if(array_key_exists($this->granted_access_level, $granted_access_level_array))
                return $granted_access_level_array[$this->granted_access_level];
        return 'Non definito';
    }    
    
    public function getTitle()
    {
        return 'Ticket #'.$this->id;
    }
    
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->with = array('document','user', 'replier');
        $criteria->together = true;
        $criteria->compare('t.id', $this->id);
        $criteria->compare('t.access_level', $this->access_level);
        $criteria->compare('t.granted_access_level', $this->access_level);        
        $criteria->compare('user.email', $this->user_email, true);
        $criteria->compare('replier.email', $this->replier_email, true);
        $criteria->compare('t.status',$this->status);
        if($this->document_id<=0)
        {
            $criteria->compare('document.identifier', $this->document_title, true);
            $criteria->compare('document.name', $this->document_title, true, 'OR');
        }
        else {
            $criteria->compare('document.id', $this->document_id);
        }
        
        if(!Yii::app()->user->isAdmin())
            $criteria->compare('document.creator_id', Yii::app()->user->id); // solo i creatori del documento possono gestirne i tickets
        
        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
            'sort'=>array(
                'defaultOrder'=>'t.date_created DESC',
            ),
            'pagination'=>array(
                'pageSize'=>10
            ),
        ));			                
    }
    
    public function my()
    {
        $criteria=new CDbCriteria;

        $criteria->with = array('document','replier');
        $criteria->together = true;
        $criteria->compare('t.id', $this->id);
        $criteria->compare('t.access_level', $this->access_level);
        $criteria->compare('t.granted_access_level', $this->access_level);        
        $criteria->compare('replier.email', $this->replier_email, true);
        $criteria->compare('t.status',$this->status);
        
        if($this->document_id<=0)
        {
            $criteria->compare('document.identifier', $this->document_title, true);
            $criteria->compare('document.name', $this->document_title, true, 'OR');
        }
        else {
            $criteria->compare('document.id', $this->document_id);
        }

        $criteria->compare('t.user_id', Yii::app()->user->id);
        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
            'sort'=>array(
                'defaultOrder'=>'t.date_created DESC',
            ),
            'pagination'=>array(
                'pageSize'=>10
            ),
        ));			                			                
    }
    
    public function getLastByDocument($document_id, $user_id, $limit = 5)
    {
        $sql = "SELECT t.*, u.email FROM users_document_requests t 
            LEFT JOIN users u ON t.replier_id = u.id
            WHERE t.document_id = :document_id 
                AND t.user_id = :user_id 
            ORDER BY t.date_created DESC 
            LIMIT ".$limit;
        $rows = Yii::app()->db->createCommand($sql)->queryAll(true, array(':document_id'=>$document_id, ':user_id'=>$user_id));
        return $rows;
    }
    
    public function isOpen()
    {
        return $this->status==self::OPEN_STATUS;        
    }

    public function canDelete()
    {
        return $this->isOpen();
    }
    
}