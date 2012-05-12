<?php
class GeoController extends Controller{
	

    public function actionCities($term)
    {
        $cities_p = City::model()->findAll('name LIKE ? ORDER BY name ASC', array($term.'%'));
        $cities = array();
        foreach ($cities_p as $city) {
            $cities[] = array('label' => $city->__toString(), 'value' => $city->id);
        }
        echo json_encode($cities);
        Yii::app()->end();
    }	
	
}