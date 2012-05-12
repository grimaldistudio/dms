<?php

class Sender extends CActiveRecord
{

    const DISABLED_STATUS = 0;
    const ACTIVE_STATUS = 1;
    
    private $city_autocomplete;
    
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
        return 'senders';
    }

    /**
    * @return array validation rules for model attributes.
    */
    public function rules()
    {
        return array(
            array('name,country_id,address', 'required', 'on'=>'create,update'),
            array('city_id', 'default', 'value'=>null, 'setOnEmpty'=>true, 'on'=>'create,update'),
            array('name', 'length', 'max'=>200, 'on'=>'create,update'),
            array('city', 'length', 'max'=>100, 'on'=>'create,update'),
            array('address', 'length', 'max'=>300, 'on'=>'create,update'),
            array('country_id', 'exist', 'className'=>'Country', 'attributeName'=>'id', 'on'=>'create,update'),
            array('city_id', 'exist', 'className'=>'City', 'attributeName'=>'id', 'on'=>'create,update'),
            array('province', 'length', 'max'=>5, 'on'=>'create,update'),
            array('postal_code', 'numerical', 'on'=>'create,update'),            
            array('postal_code', 'length', 'max'=>10, 'on'=>'create,update'),
	    array('city_id', 'application.components.validators.EConditionalValidator', 'conditionalRules'=>array('country_id', 'compare', 'compareValue'=>110), 'rule'=>array('required'), 'on'=>'create,update'),
            array('city,postal_code,province', 'application.components.validators.EConditionalValidator', 'conditionalRules'=>array('country_id', 'compare', 'operator'=>"!=", 'compareValue'=>110), 'rule'=>array('required'), 'on'=>'create,update'),			
            array('name,city,country_id,postal_code', 'safe', 'on'=>'search')
        );
    }

/*    public function beforeValidate()
    {
        var_dump($this->getAttributes());
    }*/
    
    /**
    * @return array relational rules.
    */
    public function relations()
    {
        return array(
            'documents' => array(self::HAS_MANY, 'Document', 'sender_id'),
            'citye' => array(self::BELONGS_TO, 'City', 'city_id'),
            'country' => array(self::BELONGS_TO, 'Country', 'country_id')
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
            'date_crated' => 'Data di creazione',
            'last_updated' => 'Ultimo aggiornamento',
            'status' => 'Stato',
            'address' => 'Indirizzo',
            'city' => 'Città',
            'province' => 'Provincia',
            'country_id' => 'Nazione',
            'city_id' => 'Città',
            'postal_code'=>'CAP'
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
                $this->status = Sender::ACTIVE_STATUS;
            $this->date_created = new CDbExpression('CURRENT_TIMESTAMP');
        }
        $this->last_updated = new CDbExpression('CURRENT_TIMESTAMP');
        return parent::beforeSave();
    }

    public function getStatusArray()
    {
        return array(
            '' => 'Seleziona',		
            self::DISABLED_STATUS => 'Disabilitato',
            self::ACTIVE_STATUS => 'Attivo',
        );
    }

    public function getStatusDesc()
    {
        $status_array = $this->getStatusArray();
        if(array_key_exists($this->status, $status_array))
            return $status_array[$this->status];
        return 'Non definito';
    }
    
    public function enable()
    {
        $this->status = self::ACTIVE_STATUS;
        return $this->save();
    }
    
    public function disable()
    {
        $this->status = self::DISABLED_STATUS;
        return $this->save();
    }
    
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->with = array('citye','country');
        $criteria->together = true;
        $criteria->compare('t.name',$this->name,true);
        $criteria->compare('t.status',$this->status);
        
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
    
    public function autocomplete($term)
    {
        $sql = "SELECT s.*, c.name as city_name, c.province as city_province, c.postal_code as city_postal_code, co.name as country_name FROM senders s
                         JOIN countries co ON s.country_id=co.id
                         LEFT JOIN cities c ON s.city_id=c.id
                         WHERE s.status = 1
                         AND s.name LIKE :term
                         ORDER BY s.name ASC
                         LIMIT 10";
        
        $cmd = Yii::app()->db->createCommand($sql);
        $results = $cmd->queryAll(true, array(':term'=>'%'.$term.'%'));
        $ret = array();
        foreach($results as $result)
        {
            $item = array();
            $item['value'] = $result['id'];
            $item['label'] = $result['name'];
            $item['address'] = $result['city_id']>0?$result['address'].' '.$result['city_postal_code'].' '.$result['city_name'].' ('.$result['city_province'].') '.$result['country_name']:$result['address'].' '.$result['postal_code']. ' '.$result['city'].' ('.$result['province'].') '.$result['country_name'];
            $ret[] = $item;
        }
        return $ret;
    }
    
    public function getFullAddress()
    {
        $ret = '';
        $ret .= $this->address.' ';
        if($this->city_id>0)
        {
            $ret .= $this->citye->postal_code.' ';
            $ret .= $this->citye->name.' ('.$this->citye->province.') ';
        }
        else
        {
            $ret .= $this->postal_code.' ';
            $ret .= $this->city.' ('.$this->province.') ';

        }
        $ret .= $this->country->name;
        return $ret;
    }
}