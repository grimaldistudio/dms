<?php

class Role extends CActiveRecord
{

    public $right_ids = array();
    
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
        return 'roles';
    }

    public function behaviors(){
        return array( 'CAdvancedArBehavior' => array(
            'class' => 'application.extensions.CAdvancedArBehavior')
        );
    }
    
    /**
    * @return array validation rules for model attributes.
    */
    public function rules()
    {
        return array(
            array('name,description,right_ids', 'required', 'on'=>'create,update'),
            array('name', 'length', 'max'=>128, 'on'=>'create,update'),
            array('description', 'length', 'max'=>2048, 'on'=>'create,update'),
            array('name', 'unique', 'on'=>'create,update'),
            array('name,description', 'safe', 'on'=>'search')
        );
    }

    /**
    * @return array relational rules.
    */
    public function relations()
    {
        return array(
            'rights' => array(self::MANY_MANY, 'Right', 'roles_rights(role_id, right_id)'),
            'users' => array(self::MANY_MANY, 'User', 'users_roles(role_id, user_id)' )
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
            'right_ids' => 'Permessi'
        );
    }

    public function loadRightIds()
    {
        if($this->scenario=='update')
        {
            $query = "SELECT right_id FROM roles_rights WHERE role_id = :role_id";
            $this->right_ids = Yii::app()->db->createCommand($query)->queryColumn(array(':role_id'=>$this->id));
        }
    }
    
    public function beforeValidate()
    {
        if($this->scenario=='create' || $this->scenario=='update')
        {
            $this->rights = $this->right_ids;
        }
        return parent::beforeValidate();
    }
    
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('name',$this->name,true);
        $criteria->compare('description',$this->description,true);
        
        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
            'sort'=>array(
                'defaultOrder'=>'name DESC',
            ),
            'pagination'=>array(
                'pageSize'=>10
            ),
        ));			
    }

    
    public function findAllByUserId($user_id)
    {
        $sql = "SELECT r.* FROM users_roles ur 
                           JOIN roles r ON r.id=ur.role_id
                           WHERE ur.user_id = :user_id
            ";
        $db = Yii::app()->db;
        $command = $db->createCommand($sql);
        $rows = $command->queryAll(true, array(':user_id'=>$user_id));
        $object_rows = array();
        foreach($rows as $row)
        {
            $obj = new Role();
            $obj->setAttributes($row, false);
            $object_rows[] = $obj;
        }
        return $object_rows;
    }
    
    public function findRole($role_id)
    {
            $sql = "SELECT r.* FROM users_roles ur 
                           JOIN roles r ON r.id=ur.role_id
                           WHERE ur.user_id = :user_id AND ur.role_id = :role_id
            ";
            $db = Yii::app()->db;
            $command = $db->createCommand($sql);
            $rows = $command->queryAll(true, array(':user_id'=> Yii::app()->user->id,'role_id'=>$role_id));
            
            
            if($rows != null) return true;
            else return false;
    }
    
    function __toString()
    {
        return $this->name;
    }
}