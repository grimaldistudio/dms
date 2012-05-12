<?php

class ForgotPasswordForm extends CFormModel
{
    public $username;
    public $user_id;

    public $user;

    /**
    * Declares the validation rules.
    * The rules state that username and password are required,
    * and password needs to be authenticated.
    */
    public function rules()
    {
        return array(
            array('username', 'required'),
            array('username', 'email'),				
            array('username', 'checkExists'),			
        );
    }

    /**
    * Declares attribute labels.
    */
    public function attributeLabels()
    {
        return array(
            'username'	=> 'E-mail',
        );
    }

    public function checkExists($attribute,$params) {
        if(!$this->hasErrors())  
        {
            $user=User::model()->findByAttributes(array('email'=>$this->username));
            if ($user){
                $this->user = $user;
                $this->user_id=$user->id;
            }
            if($user===null)
                $this->addError("username", "L'indirizzo e-mail non è corretto.");
            elseif($user->status==User::NEW_PASSWORD_STATUS)
                $this->addError("username", "E' stata già effettuata una richiesta di recupero password per l'indirizzo e-mail inserito.");			
            elseif($user->status!=User::ACTIVE_STATUS)
                $this->addError("username", "L'account non è abilitato per effettuare il recupero della password. Lo stato attuale è ".$user->getStatusDesc());
        }
    }

}