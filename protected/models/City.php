<?php

class City extends CActiveRecord
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
        return 'cities';
    }

    /**
    * @return array validation rules for model attributes.
    */
    public function rules()
    {
        return array(
        );
    }

    /**
    * @return array relational rules.
    */
    public function relations()
    {
        return array(
        );
    }

    /**
    * @return array customized attribute labels (name=>label)
    */
    public function attributeLabels()
    {
        return array(
            'id' => 'Id',
            'region' => 'Regione',
            'province' => 'Provincia',
            'istat_code' => 'Codice Istat',
            'region' => 'Regione',
            'name' => 'Nome',
            'postal_code' => 'CAP'
        );
    }

    function __toString()
    {
        return $this->name;
    }

    function getFullname()
    {
        return $this->name." (".$this->province.")";
    }

}