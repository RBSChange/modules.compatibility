<?php
/**
 * @package phptal.php.attribute.change
 */
class PHPTAL_Php_Attribute_CHANGE_Id extends PHPTAL_Php_Attribute
{
	/**
     * Called before element printing.
     */
    public function before(PHPTAL_Php_CodeWriter $codewriter)
    {
    	//convert namespace PHPTAL_Namespace_CHANGE::NAMESPACE_URI => ''
        $this->phpelement->setAttributeNS('', 'change:id', $this->expression);
    }

    /**
     * Called after element printing.
     */
    public function after(PHPTAL_Php_CodeWriter $codewriter)
    {
    }
}