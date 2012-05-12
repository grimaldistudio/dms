<?php

class Comment extends CActiveRecord
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
        return 'comments';
    }

    /**
    * @return array validation rules for model attributes.
    */
    public function rules()
    {
        return array(
            array('title,description', 'required'),
            array('title', 'length', 'max' => 128),
            array('description', 'length', 'max' => 2048),
            array('description','filter','filter'=>array($obj=new CHtmlPurifier(),'purify'))
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
        );
    }

    /**
    * @return array customized attribute labels (name=>label)
    */
    public function attributeLabels()
    {
        return array(
            'id' => 'Id',
            'document_id' => 'Documento',
            'user_id' => 'Utente',
            'title' => 'Titolo',
            'description' => 'Descrizione',
            'date_created' => 'Data di creazione',
            'last_updated' => 'Ultimo aggiornamento'
        );
    }

    public function beforeSave()
    {
        if ($this->isNewRecord){
            $this->date_created = new CDbExpression('CURRENT_TIMESTAMP');
        }
        $this->last_updated = new CDbExpression('CURRENT_TIMESTAMP');
        return parent::beforeSave();
    }

    
}