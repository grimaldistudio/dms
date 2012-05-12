<?php

class Folder extends CActiveRecord
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
        return 'user_gui_folders';
    }

    /**
    * @return array validation rules for model attributes.
    */
    public function rules()
    {
        return array(
            array('folder_name', 'required'),
            array('folder_name', 'length', 'max'=>128)
        );
    }

    /**
    * @return array relational rules.
    */
    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id' ),
            'documents' => array(self::MANY_MANY, 'Document', 'documents_folders(folder_id, document_id)')
        );
    }

    /**
    * @return array customized attribute labels (name=>label)
    */
    public function attributeLabels()
    {
        return array(
            'id' => 'Id',
            'folder_name' => 'Nome',
            'folder_key' => 'Chiave',
            'date_crated' => 'Data di creazione',
            'last_updated' => 'Ultimo aggiornamento',
            'user_id' => 'Utente'
        );
    }

    function __toString()
    {
        return $this->name;
    }
    
    public function beforeSave()
    {
        if ($this->isNewRecord){
            $this->date_created = new CDbExpression('CURRENT_TIMESTAMP');
        }
        $this->last_updated = new CDbExpression('CURRENT_TIMESTAMP');
        // TODO: Set key 
        return parent::beforeSave();
    }

    protected function getFolderKey()
    {
        return $this->name; // TODO: extract just a-bA-b0-9
    }
}