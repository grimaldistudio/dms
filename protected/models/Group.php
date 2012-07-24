<?php

class Group extends CActiveRecord
{

    const ACTIVE_STATUS = 1;
    const DISABLED_STATUS = 0;
    
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
        return 'groups';
    }

    /**
    * @return array validation rules for model attributes.
    */
    public function rules()
    {
        return array(
            array('folder_name', 'unique', 'on'=>'create'),
            array('name,description,email,telephone', 'required', 'on'=>'create,update'),
            array('email', 'length', 'max'=>128, 'on'=>'create,update'),
            array('name,telephone,fax', 'length', 'max'=>100, 'on'=>'create,update'),
            array('description', 'length', 'max'=>2048, 'on'=>'create,update'),
            array('email', 'unique', 'on'=>'create,update'),
//            array('description','filter','filter'=>array($obj=new CHtmlPurifier(),'purify'), 'on'=>'create,update'),
            array('fax', 'safe', 'on'=>'create,update'),
            array('name,email,telephone,fax', 'safe', 'on'=>'search')
        );
    }

    /**
    * @return array relational rules.
    */
    public function relations()
    {
        return array(
            'users' => array(self::MANY_MANY, 'User', 'users_groups(group_id, user_id)' )
        );
    }

    /**
    * @return array customized attribute labels (name=>label)
    */
    public function attributeLabels()
    {
        return array(
            'id' => 'Id',
            'name' => 'Nome',
            'description' => 'Descrizione',
            'email' => 'E-mail',
            'telephone' => 'Telefono',
            'fax' => 'Fax',
            'date_created' => 'Data di creazione',
            'last_updated' => 'Ultimo aggiornamento',
            'status' => 'Stato',
            'folder_name' => 'Nome directory'
        );
    }

    function __toString()
    {
        return $this->name;
    }
    
    public function beforeSave()
    {
        if ($this->isNewRecord){
            if($this->status<=0)
                $this->status = self::ACTIVE_STATUS;
            $this->date_created = new CDbExpression('CURRENT_TIMESTAMP');
        }
        if($this->folder_name===null)
        {
            $this->generateFolderName();
        }
        $this->last_updated = new CDbExpression('CURRENT_TIMESTAMP');
        return parent::beforeSave();
    }

    public function generateFolderName()
    {
        $folder_name = str_replace(' ', '_', strtolower($this->name));
        if(preg_match('/[a-z0-9_]*/', $folder_name, $matches)>0)
        {
            if(strlen($matches[0])>2)
            {
                $this->folder_name = $matches[0];
                return true;
            }
        }
        $this->folder_name = "cartella_gruppo_".time();
    }
    
    public function getStatusArray()
    {
        return array(
            '' => 'Seleziona',		
            self::DISABLED_STATUS => 'Disabilitato',
            self::ACTIVE_STATUS => 'Attivo',
        );
    }

    public function createNew()
    {
        $transaction = Yii::app()->db->beginTransaction();
        if($this->save() && $this->createFolders())
        {
            $transaction->commit();
            return true;
        }
        $transaction->rollback();
        return false;
    }
    
    protected function createFolders()
    {
        $uploads_dir = Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'groups';
        $group_folder_name = $this->folder_name;
        $pending_folder = $uploads_dir.DIRECTORY_SEPARATOR.$group_folder_name.DIRECTORY_SEPARATOR."pending";
        $discarded_folder = $uploads_dir.DIRECTORY_SEPARATOR.$group_folder_name.DIRECTORY_SEPARATOR."discarded";
        $cache_folder = $uploads_dir.DIRECTORY_SEPARATOR.$group_folder_name.DIRECTORY_SEPARATOR."cache";        
        if(mkdir($pending_folder, 0777, true) && mkdir($discarded_folder, 0777, true) && mkdir($cache_folder, 0777, true))
            return true;
        return false;
    }
    
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('name',$this->name,true);
        $criteria->compare('email',$this->email,true);
        $criteria->compare('telephone',$this->telephone,true);
        $criteria->compare('fax',$this->fax,true);        
        $criteria->compare('status',$this->status);
        
        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
            'sort'=>array(
                'defaultOrder'=>'date_created DESC',
            ),
            'pagination'=>array(
                'pageSize'=>10
            ),
        ));			
    }

    
    public function findAllByUserId($user_id)
    {
        $sql = "SELECT g.* FROM users_groups ug 
                           JOIN groups g ON g.id=ug.group_id
                           WHERE ug.user_id = :user_id
                           AND g.status = :status
            ";
        $db = Yii::app()->db;
        $command = $db->createCommand($sql);
        $rows = $command->queryAll(true, array(':user_id'=>$user_id, ':status' => self::ACTIVE_STATUS));
        $object_rows = array();
        foreach($rows as $row)
        {
            $obj = new Group();
            $obj->setAttributes($row, false);
            $object_rows[] = $obj;
        }
        return $object_rows;
    }
    
    public function getStatusDesc()
    {
        $status_array = $this->getStatusArray();
        if(array_key_exists($this->status, $status_array))
            return $status_array[$this->status];
        return 'Non definito';
    }
    
    public function removeUser($user_id)
    {
        Yii::app()->db->createCommand("DELETE FROM users_groups WHERE group_id=:group_id AND user_id=:user_id")->execute(array(':user_id'=>$user_id, ':group_id'=>$this->id));
        return true;
    }
    
    public function addUsers($user_ids)
    {
        if(!is_array($user_ids))
        {
            $user_ids = explode(",", $user_ids);
        }
        $cmd = Yii::app()->db->createCommand("INSERT IGNORE INTO users_groups(user_id,group_id) VALUES(:user_id, :group_id)");
        foreach($user_ids as $user_id)
            $cmd->execute(array(':user_id'=>$user_id, ':group_id'=>$this->id));
        return true;        
    }
    
    public function autocomplete($term)
    {
        $sql = "SELECT g.id, g.name FROM groups g WHERE name LIKE :term LIMIT 20";
        $cmd = Yii::app()->createCommand($sql);
        $rows = $cmd->queryAll(true, array(':term'=>'%'.$term.'%'));
        $res = array();
        foreach($rows as $row)
        {
            $item = array();
            $item['id'] = $row['id'];
            $item['label'] = $row['name'];
            $res[] = $item;
        }
        return $res;
            
    }
}