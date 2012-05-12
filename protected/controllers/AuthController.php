<?php

class AuthController extends Controller{

    public function __construct($id, $module = null) {
        $this->layout = "dms_full";
        parent::__construct($id, $module);
    }
    /**
    * This is the default 'index' action that is invoked
    * when an action is not explicitly requested by users.
    */
    public function actionIndex()
    {
        if(Yii::app()->user->getIsGuest())
        {
            $this->render('login');
        }
        else
        {
            $this->redirect(Yii::app()->homeUrl);
        }
    }

    /**
    * Displays the login page
    */
    public function actionLogin()
    {
        if(Yii::app()->user->getIsGuest() == FALSE)
        {
            $this->redirect(Yii::app()->homeUrl);
        }

        $model=new LoginForm;

        // if it is ajax validation request
        if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if(isset($_POST['LoginForm']))
        {
            $model->attributes=$_POST['LoginForm'];
            // validate user input and redirect to the previous page if valid
            if($model->validate() && $model->login())
                    $this->redirect(Yii::app()->user->returnUrl);
        }
        // display the login form
        $this->render('login',array('model'=>$model));
    }

    /**
        * Logs out the current user and redirect to homepage.
        */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }

    public function actionForgotpassword()
    {
        if(Yii::app()->user->getIsGuest() == FALSE)
        {
            $this->redirect(Yii::app()->homeUrl);
        }

        $model=new ForgotPasswordForm;

        // if it is ajax validation request
        if(isset($_POST['ajax']) && $_POST['ajax']==='forgot-password-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if(isset($_POST['ForgotPasswordForm']))
        {
            $model->attributes=$_POST['ForgotPasswordForm'];
            // validate user input and redirect to the previous page if valid
            if($model->validate())
            {
                $user = $model->user;
                if($user->newPasswordRequest(true))
                {
                    Yii::app()->user->setFlash('success', 'Le istruzioni per impostare una nuova password sono state inviate all\indirizzo impostato.');					
                }
                else
                {
                    Yii::app()->user->setFlash('error', 'Errore durante la procedura di recupero password.');
                }
            }
        }
        // display the login form
        $this->render('forgotpassword',array('model'=>$model));		
    }

    public function actionResetpassword()
    {
        if(Yii::app()->user->getIsGuest() == FALSE)
        {
            $this->redirect(Yii::app()->homeUrl);
        }

        if(!isset($_GET['email']) || !isset($_GET['new_password_key']))
        {
            Yii::app()->user->setFlash('error', 'Parametri mancanti');
            $this->redirect('forgotpassword');
        }

        $email = $_GET['email'];
        $new_password_key = $_GET['new_password_key'];

        $user = User::model()->findByAttributes(array('email'=>$email, 'new_password_key'=>$new_password_key, 'status'=>User::NEW_PASSWORD_STATUS));
        if($user===null)
        {
            Yii::app()->user->setFlash('error', 'La procedura si è conclusa con esito negativo. La preghiamo di verificare il link ricevuto via e-mail.');
            $this->redirect('forgotpassword');
        }

        $model=new NewPasswordForm;
        // if it is ajax validation request
        if(isset($_POST['ajax']) && $_POST['ajax']==='new-password-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if(isset($_POST['NewPasswordForm']))
        {
            $model->attributes=$_POST['NewPasswordForm'];
            if($model->validate())
            {
                if($user->resetPassword($model->password))
                {
                    Yii::app()->user->setFlash('success', 'La password è stata cambiata con successo.');
                    $this->redirect('auth/login');
                }
                else
                {
                    Yii::app()->user->setFlash('error', 'Errore durante la procedura di modifica password.');
                }
            }
        }

        // display the login form
        $this->render('newpassword',array('model'=>$model));		
    }
	
}