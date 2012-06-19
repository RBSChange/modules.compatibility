<?php
/**
 * @deprecated
 */
class PHPTAL_Php_Attribute_CHANGE_Id extends PHPTAL_Php_Attribute
{
	/**
	 * @deprecated
	 */
    public function before(PHPTAL_Php_CodeWriter $codewriter)
    {
    	//convert namespace PHPTAL_Namespace_CHANGE::NAMESPACE_URI => ''
        $this->phpelement->setAttributeNS('', 'change:id', $this->expression);
    }

	/**
	 * @deprecated
	 */
    public function after(PHPTAL_Php_CodeWriter $codewriter)
    {
    }
}