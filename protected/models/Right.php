<?php

class Right extends CActiveRecord
{

    public $role_ids = array();
    
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
        return 'rights';
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
            array('key,name', 'required', 'on'=>'create,update'),
            array('key', 'match', 'pattern'=>'/^([a-z0-9]*)\/([a-z0-9\*]*)$/', 'on'=>'create,update'),
            array('role_ids', 'safe', 'on'=>'create,update'),
            array('key', 'length', 'max'=>50, 'on'=>'create,update'),
            array('name', 'length', 'max'=>255, 'on'=>'create,update'),
            array('key', 'unique', 'on'=>'create,update'),
            array('key,name', 'safe', 'on'=>'search')
        );
    }

    /**
    * @return array relational rules.
    */
    public function relations()
    {
        return array(
            'roles' => array(self::MANY_MANY, 'Role', 'roles_rights(right_id, role_id)'),
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
            'key' => 'Chiave',
            'role_ids' => 'Ruoli'
        );
    }

    public function loadRoleIds()
    {
        if($this->scenario=='update')
        {
            $query = "SELECT role_id FROM roles_rights WHERE right_id = :right_id";
            $this->role_ids = Yii::app()->db->createCommand($query)->queryColumn(array(':right_id'=>$this->id));
        }
    }
    
    public function beforeValidate()
    {
        if($this->scenario=='create' || $this->scenario=='update')
        {
            $this->roles = $this->role_ids;
        }
        return parent::beforeValidate();
    }
    
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('name',$this->name,true);
        $criteria->compare('`key`',$this->key,true);
        
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

    
    public function existsByRoleIds($right_id, $role_ids = array())
    {
        if(empty($role_ids))
            return false;
        
        if(is_numeric($right_id))
        {
            $query = "SELECT count(1) FROM roles_rights ur
                                      WHERE ur.role_id IN(".implode(",", $role_ids).")
                                      AND ur.right_id = :right_id
            ";
            $cmd = Yii::app()->db->createCommand($query);
            $count = $cmd->queryScalar(array(':right_id'=>$right_id));
            if($count>0)
                return true;
            return false;
        }
        else
        {
            $query = "SELECT count(1) FROM roles_rights ur
                                      JOIN rights r ON r.id=ur.right_id
                                      WHERE ur.role_id IN(".implode(",", $role_ids).")
                                      AND r.key = :right_id
            ";
            $cmd = Yii::app()->db->createCommand($query);
            $count = $cmd->queryScalar(array(':right_id'=>$right_id));
            if($count>0)
                return true;
            return false;
        }
    }
    
    public function findAllByRoleIds($role_ids)
    {
        $query = "SELECT r.* FROM roles_rights rr 
                             JOIN rights r ON r.id=rr.right_id
                             WHERE rr.role_id IN(".implode(",", $role_ids).")
                             GROUP BY rr.right_id
            ";
        $cmd = Yii::app()->db->createCommand($query);
        $rows = $cmd->queryAll(true);
        $object_rows = array();
        foreach($rows as $row)
        {
            $object = new Right();
            $object->setAttributes($row, false);
            $object_rows[] = $object;
        }
        return $object_rows;
    }
    
    public function findAllByRoleId($role_id)
    {
        $query = "SELECT r.* FROM roles_rights rr 
                             JOIN rights r ON r.id=rr.right_id
                             WHERE rr.role_id=:role_id
                             GROUP by rr.right_id
                ";
        $cmd = Yii::app()->db->createCommand($query);
        $rows = $cmd->queryAll(true, array(':role_id'=>$role_id));
        $object_rows = array();
        foreach($rows as $row)
        {
            $object = new Right();
            $object->setAttributes($row, false);
            $object_rows[] = $object;
        }
        return $object_rows;
    }
    
    function __toString()
    {
        return $this->name;
    }
}