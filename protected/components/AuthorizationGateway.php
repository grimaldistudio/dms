<?php

class AuthorizationGateway{
    
    public $loggedInUrls = array();
    public $publicUrls = array();
    public $superadminUrls = array();
    
    public function init()
    {
        
    }
    
    public function isAllowed($controller = null, $action = null)
    {
        if($controller===null)
        {         
            $controller = Yii::app()->controller->id;
            $action = Yii::app()->controller->action->id;
        }
        elseif($action===null)
        {
            $action = Yii::app()->controller->action->id;
        }

        // not logged in
        if(in_array($controller.'/'.$action, $this->publicUrls) || in_array($controller.'/*', $this->publicUrls))
            return true;

        if(Yii::app()->user->isLoggedIn())
        {
            if(in_array($controller.'/'.$action, $this->superadminUrls) || in_array($controller.'/*', $this->superadminUrls))
            {
                return Yii::app()->user->isSuperadmin();
            }

            if(Yii::app()->user->isAdmin())
                return true;

            if(in_array($controller.'/'.$action, $this->loggedInUrls) || in_array($controller.'/*', $this->loggedInUrls))
                return true;

            $rights = Yii::app()->user->getRights();
            $rights_keys = array_keys($rights);
            if(in_array($controller.'/'.$action, $rights_keys) || in_array($controller.'/*', $rights_keys))
                return true;
        }
        return false;
    }
    
}
?>
