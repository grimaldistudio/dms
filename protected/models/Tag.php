<?php

class Tag extends CActiveRecord
{

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
        return 'tags';
    }

    /**
    * @return array validation rules for model attributes.
    */
    public function rules()
    {
        return array(
            array('name', 'required', 'on'=>'create,update'),
            array('name', 'length', 'max'=>50, 'on'=>'create,update'),
            array('name', 'unique', 'on'=>'create,update')
        );
    }

    /**
    * @return array relational rules.
    */
    public function relations()
    {
        return array(
            'documents' => array(self::MANY_MANY, 'Document', 'tags_documents(tag_id, document_id)')
        );
    }

    /**
    * @return array customized attribute labels (name=>label)
    */
    public function attributeLabels()
    {
        return array(
            'id' => 'Id',
            'name' => 'Tag',
        );
    }

    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('name',$this->name,true);
        
        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
            'sort'=>array(
                'defaultOrder'=>'id DESC',
            ),
            'pagination'=>array(
                'pageSize'=>10
            ),
        ));			
    }
    
    public function autocomplete($term)
    {
        $sql = "SELECT name FROM tags WHERE name LIKE :term LIMIT 10";
        $cmd = Yii::app()->db->createCommand($sql);
        $tags = $cmd->queryAll(true, array(':term'=>'%'.$term.'%'));
        $ret = array();
        foreach($tags as $tag)
        {
            $ret[] = $tag['name'];
        }
        return $ret;
    }
    
    public function autocompletetoken($term)
    {
        $sql = "SELECT id, name FROM tags WHERE name LIKE :term LIMIT 10";
        $cmd = Yii::app()->db->createCommand($sql);
        $tags = $cmd->queryAll(true, array(':term'=>'%'.$term.'%'));
        return $tags;
    }
    
    function __toString()
    {
        return $this->name;
    }
}