<?php
/**
 * EMultiSelect class file.
 *
 * PHP Version 5.1
 * 
 * @category Vencidi
 * @package  Widget
 * @author   Loren <wiseloren@yiiframework.com>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @link     http://www.vencidi.com/ Vencidi
 * @since    3.0
 */
Yii::import('zii.widgets.jui.CJuiWidget');
/**
 * EMultiSelect Creates Multiple Select Boxes
 *
 * @category Vencidi
 * @package  Widget
 * @author   Loren <wiseloren@yiiframework.com>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @version  Release: 1.0
 * @link     http://www.vencidi.com/ Vencidi
 * @since    3.0
 */
class EMultiSelect extends CJuiWidget
{
    public $options = array();

    /**
     * Run not used...
     *
     * @return void
     */
    function run()
    {
        
    }

    /**
     * Initializes everything
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->registerScripts();
    }

    /**
     * Registers the JS and CSS Files
     *
     * @return void
     */
    protected function registerScripts()
    {
        parent::registerCoreScripts();
        $basePath=Yii::getPathOfAlias('application.extensions.emultiselect.assets');
        $baseUrl = Yii::app()->getAssetManager()->publish($basePath);

        $cs=Yii::app()->getClientScript();
        $cs->registerCssFile($baseUrl . '/' . 'ui.multiselect.css');

        $this->scriptUrl=$baseUrl;
        $this->registerScriptFile('ui.multiselect.js');

        $params = CJavaScript::encode($this->options);
        Yii::app()->clientScript->registerScript(
            'EMultiSelect',
            '$(".multiselect").multiselect('. $params .');',
            CClientScript::POS_READY
        );

    }
}
?>