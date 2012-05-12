<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
    private $_id;
    
    const ERROR_EMAIL_INVALID=3;
    const ERROR_STATUS_DISABLED=4;
    const ERROR_STATUS_BAN=5;
    const ERROR_PASSWORD_REQUEST = 6;
    
    /**
    * Authenticates a user.
    * @return boolean whether authentication succeeds.
    */
    public function authenticate()
    {
        $user=User::model()->find('LOWER(email)=?',array(strtolower($this->username)));
        
        if($user===null)
            $this->errorCode=self::ERROR_EMAIL_INVALID;
        else if(!$user->validatePassword($this->password))
            $this->errorCode=self::ERROR_PASSWORD_INVALID;
        else if($user->isDisabled())
            $this->errorCode=self::ERROR_STATUS_DISABLED;
        else if($user->isNewPasswordRequested())
            $this->errorCode=self::ERROR_PASSWORD_REQUEST;
        else {
            $this->_id=$user->id;
            $user->updateLoginStats();
            $this->setState('firstname', $user->firstname);
            $this->setState('lastname', $user->lastname);
            $this->setState('telephone', $user->telephone);
            $this->setState('is_admin', $user->is_admin);
            $this->setState('last_login', $user->last_login);
            if(!$user->is_admin)
            {
                // get groups
                $groups_data = array();
                $groups = Group::model()->findAllByUserId($user->id);
                foreach($groups as $group)
                {
                    $group_data_item = array();
                    $group_data_item['group_id'] = $group->id;
                    $group_data_item['group_name'] = $group->name;
                    $group_data_item['group_folder_name'] = $group->folder_name;
                    $groups_data[] = $group_data_item;
                }
                $this->setState('groups', json_encode($groups_data));
                
                // get roles
                $roles_data = array();
                $roles = Role::model()->findAllByUserId($user->id);
                foreach($roles as $role)
                {
                    $role_data_item = array();
                    $role_data_item['role_id'] = $role->id;
                    $role_data_item['role_name'] = $role->name;
                    $roles_data[] = $role_data_item;
                }
                $this->setState('roles', json_encode($roles_data));
            }
            $this->errorCode=self::ERROR_NONE;

        }
        return !$this->errorCode;
    }
    
    /**
    * @return integer the ID of the user record
    */
    public function getId()
    {
            return $this->_id;
    }
    
    public function getError()
    {
        $msg = "Impossibile effettuare il login. ";
        switch($this->errorCode)	
        {
            case self::ERROR_STATUS_DISABLED:
                $msg .= "L'account è stato disabilitato. Contattare l'amministratore di sistema.";
                break;
            case self::ERROR_PASSWORD_REQUEST:
                $msg .= "E' in corso la procedura di recupero della password per questo account.";
                break;
            case self::ERROR_USERNAME_INVALID:
            case self::ERROR_PASSWORD_INVALID:
                $msg .= "E-mail o password non corretti.";
        }
        return $msg;        
    }
}