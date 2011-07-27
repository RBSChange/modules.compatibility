<?php
class compatibility_PHPTAL_CHANGE
{
	/**
	 * @param PHPTAL_Namespace_CHANGE $namespaceCHANGE
	 */
	public static function addAttributes($namespaceCHANGE)
	{		
        $namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeSurround('icon', 31));
        $namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeSurround('image', 32));
        $namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeSurround('webappimage', 32));
        
		$namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeSurround('id', 10));
		
        $namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeReplace('date', 13));
		$namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeReplace('datetime', 14));
		
		$namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeSurround('i18nattr', 7));
		$namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeContent('translate', 8));
		
        $namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeReplace('price', 10));
        
		$namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeSurround('currentlink', 30));
	}
}