<?php

class NewPasswordForm extends CFormModel
{
    public $password;
    public $confirm_password;

    public $user;

    /**
        * Declares the validation rules.
        * The rules state that username and password are required,
        * and password needs to be authenticated.
        */
    public function rules()
    {
        return array(
            // username and password are required
            array('password,confirm_password', 'required'),
            // username and password are required
            array('confirm_password', 'compare', 'compareAttribute'=>'password')		
        );
    }

    /**
        * Declares attribute labels.
        */
    public function attributeLabels()
    {
        return array(
            'password'	=> 'Password',
            'confirm_password' => 'Conferma Password'
        );
    }

}