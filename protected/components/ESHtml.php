<?php

/**
 * Description of ESHtml
 * HTML utilities
 * 
 * @author fabrizio
 */
class ESHtml {

    static function unorderedList($data, $ul_options = array(), $li_options = array())
    {
        $ret = CHtml::openTag("ul", $ul_options);
        foreach($data as $item)
        {
            $ret .= CHtml::openTag('li', $li_options);
            $ret .= CHtml::encode($item->name);
            $ret .= CHtml::closeTag('li');
        }
        $ret .= CHtml::closeTag('ul');
        return $ret;
    }
    
    static function orderedList($data, $ul_options = array(), $li_options = array())
    {
        $ret = CHtml::openTag("ul", $ul_options);
        foreach($data as $item)
        {
            $ret .= CHtml::openTag('li', $li_options);
            $ret .= CHtml::encode($item->name);
            $ret .= CHtml::closeTag('li');
        }
        $ret .= CHtml::closeTag('ul');        
        return $ret;
    }
}

?>
