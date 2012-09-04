<?php

class User extends CActiveRecord
{
    
    const DISABLED_STATUS = 0;
    const ACTIVE_STATUS = 1;
    const NEW_PASSWORD_STATUS = 2;

    public $old_email = NULL;
    public $new_password = NULL;
    public $plain_password = NULL;
    public $confirm_password = NULL;

    public $role_ids = array();
    public $group_ids = array();
    public $group_id = null;
    
    /**
    * Returns the static model of the specified AR class.
    * @return CActiveRecord the static model class
    */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function behaviors(){
        return array( 'CAdvancedArBehavior' => array(
            'class' => 'application.extensions.CAdvancedArBehavior')
        );
    }
          
    /**
    * @return string the associated database table name
    */
    public function tableName()
    {
        return 'users';
    }

    /**
    * @return array validation rules for model attributes.
    */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('email,firstname,lastname,telephone,status', 'safe', 'on'=>'search'),
            array('email,firstname,lastname', 'safe', 'on'=>'gsearch'),            
            array('email,firstname,lastname,telephone', 'required', 'on'=>'create,update'),
            array('firstname,lastname,telephone', 'required', 'on'=>'pupdate'),
            array('role_ids,group_ids', 'safe', 'on'=>'create,update'),
            array('email', 'email', 'on'=>'create,update'),
            array('email', 'unique', 'on'=>'create,update'),
            array('is_admin', 'boolean', 'on'=>'create,update'),
            array('email,firstname,lastname,telephone', 'length', 'max' => 128),
            array('confirm_password,new_password', 'required', 'on'=>'lupdate'),
            array('confirm_password', 'compare', 'compareAttribute'=>'new_password', 'on'=>'lupdate'),
            array('password, new_password, plain_password', 'length', 'max'=>128, 'on'=>'create,update,lupdate'),
            array('is_admin', 'default', 'setOnEmpty'=>true, 'value'=>false, 'on'=>'create,update'),
            array('role_ids', 'roleCheck', 'on'=>'create,update'),
            array('group_ids', 'groupCheck', 'on'=>'create,update')
        );
    }

    /**
    * @return array relational rules.
    */
    public function relations()
    {
        return array(
            'roles' => array(self::MANY_MANY, 'Role', 'users_roles(user_id, role_id)'),
            'groups' => array(self::MANY_MANY, 'Group', 'users_groups(user_id, group_id)'),
            'folders' => array(self::HAS_MANY, 'Folder', 'user_id'),
            'notifications' => array(self::HAS_MANY, 'Notification', 'user_id')
        );
    }
    
    /**
    * @return array customized attribute labels (name=>label)
    */
    public function attributeLabels()
    {
        return array(
            'id' => 'Id',
            'firstname' => 'Nome',
            'lastname' => 'Cognome',
            'telephone' => 'Telefono',
            'last_ip' => 'Ultimo IP',
            'last_login' => 'Ultimo Login',
            'is_admin' => 'Amministratore',
            'email' => 'Email',
            'new_password' => 'Nuova Password',
            'password' => 'Password',
            'confirm_password' => 'Conferma Password',
            'status' => 'Stato',
            'date_created' => 'Data di creazione',
            'last_updated' => 'Ultimo aggiornamento',
            'group_ids'=>'Gruppi',
            'role_ids'=>'Ruoli'
        );
    }

    /**
    * Checks if the given password is correct.
    * @param string the password to be validated
    * @return boolean whether the password is valid
    */
    public function validatePassword($password)
    {
        return $this->hashPassword($password,$this->salt)===$this->password;
    }

    /**
    * Generates the password hash.
    * @param string password
    * @param string salt
    * @return string hash
    */
    public function hashPassword($password,$salt)
    {
        return md5($salt.$password);
    }

    /**
    * Generates a salt that can be used to generate a password hash.
    * @return string the salt
    */
    protected function generateSalt()
    {
        return uniqid('',true);
    }

    protected function generateActivationKey()
    {
        return uniqid('',true);
    }

    private function createRandomPassword($len = 7) 
    { 
        $chars = "abcdefghijkmnopqrstuvwxyz023456789"; 
        srand((double)microtime()*1000000); 
        $i = 0; 
        $pass = '' ; 

        while ($i <= $len) { 
            $num = rand() % 33; 
            $tmp = substr($chars, $num, 1); 
            $pass = $pass . $tmp; 
            $i++; 
        } 

        return $pass; 
    } 

    public function loadGroupIds()
    {
        $query = "SELECT group_id FROM users_groups WHERE user_id = :user_id";
        $this->group_ids = Yii::app()->db->createCommand($query)->queryColumn(array(':user_id'=>$this->id));
    }
    
    public function loadRoleIds()
    {
        $query = "SELECT role_id FROM users_roles WHERE user_id = :user_id";
        $this->role_ids = Yii::app()->db->createCommand($query)->queryColumn(array(':user_id'=>$this->id));
    }
    
    public function beforeValidate()
    {
        if($this->scenario=='create' || $this->scenario=='update')
        {
            if($this->is_admin)
            {
                $this->group_ids = array();
                $this->role_ids = array();
            }
            $this->groups = $this->group_ids;
            $this->roles = $this->role_ids;
        }
        return parent::beforeValidate();
    }
    
    public function createAccount()
    {
        $this->salt = $this->generateSalt();
        if(Yii::app()->params['isDemo']===true)
            $this->plain_password = "testuser";
        else
            $this->plain_password = $this->createRandomPassword();
        $this->status = self::ACTIVE_STATUS;            
        $this->password = $this->hashPassword($this->plain_password, $this->salt);
        $ret = $this->save();
        
        if($ret)
        {
            if(Yii::app()->params['isDemo']===false)
                $this->sendNewAccountEmail();
            return TRUE;
        }
        return FALSE;
    }

    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('firstname',$this->firstname,true);
        $criteria->compare('lastname',$this->lastname,true);
        $criteria->compare('email',$this->email,true);
        $criteria->compare('telephone',$this->telephone,true);
        $criteria->addNotInCondition('email', Yii::app()->params['superadmins']);
        $criteria->compare('status',$this->status);
        
        if(!Yii::app()->user->isSuperadmin())
            $criteria->compare('is_admin', 0);

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

    // search by group
    public function gsearch()
    {
        $criteria=new CDbCriteria;

        $criteria->with = array('groups'=>array('select'=>'id', 'together'=>true, 'joinType'=>'INNER JOIN'));
        $criteria->compare('firstname',$this->firstname,true);
        $criteria->compare('lastname',$this->lastname,true);
        $criteria->compare('email',$this->email,true);
        $criteria->compare('t.status',self::ACTIVE_STATUS);
        $criteria->compare('groups.id', $this->group_id);
        
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
    
    public function newPasswordRequest()
    {
        $this->new_password_key = md5(uniqid().$this->salt);
        $this->new_password_requested = new CDbExpression('CURRENT_TIMESTAMP');
        $this->status = User::NEW_PASSWORD_STATUS;
        if($this->save()){
            $this->sendNewPasswordEmail();
            return true;
        }
        return false;	
    }

    public function changePassword()
    {
        $this->salt = $this->generateSalt();
        $this->password = $this->hashPassword($this->new_password, $this->salt);
        return $this->save();
    }
    
    public function resetPassword($new_plain_password)
    {
        $this->salt = $this->generateSalt();
        $this->password = $this->hashPassword($new_plain_password, $this->salt);
        $this->status = User::ACTIVE_STATUS;
        if($this->save())
            return true;
        return false;
    }

    public function updateLoginInfo()
    {
        if($this->new_password!==null && strlen($this->new_password)>0){
            $this->salt = $this->generateSalt();
            $this->password = $this->hashPassword($this->new_password, $this->salt);
        }
        if($this->save())
            return true;
        return false;		
    }

    public function updateLoginStats()
    {
        $this->last_login = new CDbExpression('CURRENT_TIMESTAMP');
        $this->last_ip = Yii::app()->request->getUserHostAddress();
        $this->num_logins = new CDbExpression('num_logins+1');	
        $this->save();
    }

    public function beforeSave()
    {
        if ($this->isNewRecord){
            if($this->status<=0)
                $this->status = self::ACTIVE_STATUS;
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
            self::NEW_PASSWORD_STATUS => 'Password non impostata'
        );
    }

    public function getStatusDesc()
    {
        $status_array = $this->getStatusArray();
        if(array_key_exists($this->status, $status_array))
            return $status_array[$this->status];
        return 'Non definito';
    }

    public function sendNewPasswordEmail()
    {
        $message = new YiiMailMessage;
        $message->view = 'forgotpassword';
        $message->subject = 'Reimposta Password';	
        $message->setBody(array('user'=>$this), 'text/html');
        $message->addTo($this->email);
        $message->from = Yii::app()->params['adminEmail'];
        try{
            Yii::app()->mail->send($message);
            return true;
        }
        catch(Exception $e)
        {
            Yii::log('Error Forgot Password E-mail to: '. $user->email, 'error');
            Yii::log($e->getMessage(), 'error');            
            return false;
        }
    }

    public function sendNewAccountEmail()
    {
        $message = new YiiMailMessage;
        $message->view = 'newaccount';
        $message->subject = 'Creazione Account DMS';	
        $message->setBody(array('user'=>$this), 'text/html');
        $message->addTo($this->email);
        $message->from = Yii::app()->params['adminEmail'];
        try{
            Yii::app()->mail->send($message);
            return true;
        }
        catch(Exception $e)
        {
            Yii::log('Error New Account E-mail to: '. $this->email, 'error');
            Yii::log($e->getMessage(), 'error');
            return false;
        }	
    }
    
    public function groupCheck($attribute, $params)
    {
        if(!$this->is_admin)
        {
            if(!is_array($this->$attribute) ||count($this->$attribute)<=0)
                $this->addError($attribute, "E' necessario selezionare almeno un gruppo.");
        }
    }
    
    public function roleCheck($attribute, $params)
    {
        if(!$this->is_admin)
        {
            if(!is_array($this->$attribute) ||count($this->$attribute)<=0)
                $this->addError($attribute, "E' necessario selezionare almeno un ruolo.");
        }
    }
    
    public function isDisabled()
    {
        return $this->status==self::DISABLED_STATUS;
    }
    
    public function isNewPasswordRequested()
    {
        return $this->status==self::NEW_PASSWORD_STATUS;
    }
	
    public function disable()
    {
        $this->status = self::DISABLED_STATUS;
        return $this->save();
    }
    
    public function enable()
    {
        $this->status = self::ACTIVE_STATUS;
        return $this->save();
    }
    
    public function autocomplete($term)
    {
        $sql = "SELECT u.id, CONCAT(u.firstname, CONCAT(' ', u.lastname)) as name FROM users u WHERE name LIKE :term LIMIT 20";
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
    
    public function getFullName()
    {
        return $this->firstname.' '.$this->lastname;
    }
}