<?php

class DocumentHistory extends CActiveRecord
{

    public $username;
    public $useremail;
    
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
        return 'document_history';
    }

    /**
    * @return array validation rules for model attributes.
    */
    public function rules()
    {
        return array(
            array('username,useremail,description', 'safe', 'on'=>'search')
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
            'name' => 'Nome',
            'description' => 'Descrizione',
            'date_crated' => 'Data di creazione',
            'document_id' => 'Documento',
            'user_id' => 'Utente',
            'revision' => 'Numero di Revision',
            'username' => 'Nome Autore',
            'useremail' => 'E-mail Autore'
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
        return parent::beforeSave();
    }

    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->with = array('user'=>array('select'=>array(new CDbExpression('CONCAT(firstname, CONCAT(\' \', lastname)) as name'), 'email')));
        $criteria->compare('description',$this->description,true);
        $criteria->compare('revision',$this->revision);
        $criteria->compare('user.name', $this->username, true);
        $criteria->compare('user.email', $this->useremail, true);
        $criteria->compare('document_id', $this->document_id);
        
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
    
    public function findLatest($document_id, $limit = 10)
    {
        $limit = intval($limit);
        if($limit<=0)
            $limit = 10;
        $sql = "SELECT SQL_CALC_FOUND_ROWS dh.document_id, dh.description, dh.revision, dh.date_created, CONCAT(u.firstname, CONCAT(' ', u.lastname)) as author, u.email FROM document_history dh JOIN users u ON u.id=dh.user_id WHERE document_id = :document_id ORDER BY revision DESC LIMIT ".$limit;
        $rows = Yii::app()->db->createCommand($sql)->queryAll(true, array(':document_id'=>$document_id));
        $count_rows = Yii::app()->db->createCommand("SELECT FOUND_ROWS()")->queryScalar();
        
        $data['rows'] = $rows;
        $data['count'] = $count_rows;
        return $data;
    }
}