<?php
class ProfileController extends SecureController{
    
    private $_model = null;
    
    public function actionEdit()
    {
        $model = $this->loadModel();
        $model->setScenario('pupdate');
        
        $pmodel = clone $model;
        $pmodel->setScenario('lupdate');
        
        if(isset($_POST['User']))
        {
            if(isset($_POST['login']))
            {
                $pmodel->setAttributes($_POST['User']);
                if($pmodel->changePassword())
                {
                    // ajax success
                    $this->ajaxSuccess("La password è stata aggiornata");
                }
                else
                {
                    // ajax error
                    if($pmodel->hasErrors())
                        $this->ajaxError($pmodel->getErrors());
                    else
                        $this->ajaxError(array('Impossibile aggiornare il profilo'));
                }
            }
            else
            {
                $model->setAttributes($_POST['User']);                
                if($model->save())
                {
                    // ajax success
                    $this->ajaxSuccess("Profilo aggiornato con successo");
                }
                else
                {
                    // ajax error
                    if($model->hasErrors())
                        $this->ajaxError($model->getErrors());
                    else
                        $this->ajaxError(array('Impossibile aggiornare il profilo'));
                }
            }
        }
        $this->render('edit', array('model'=>$model, 'pmodel'=>$model));
    }
    
    protected function loadModel()
    {
        if($this->_model===null)
        {
            $this->_model=Yii::app()->user->getModel();

            if($this->_model===null)
                throw new CHttpException(404,'La pagina richiesta non esiste');

        }

        return $this->_model;
    }
}