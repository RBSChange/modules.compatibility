<?php
abstract class website_phptal_EditAttribute extends PHPTAL_Php_Attribute
{
	/**
	 * @var Boolean
	 */
	private static $isActive;

	/**
	 * @see PHPTAL_Php_Attribute::start
	 */
	public function start()
	{
		if (self::isActive())
		{
			list($attrName, $attrValue) = $this->startTag();
			$this->tag->attributes['change:'.$attrName] = $attrValue;
		}
	}

	/**
	 * @see PHPTAL_Php_Attribute::end
	 */
	public function end()
	{
		// empty
	}

	abstract protected function startTag();
	
	static function isActive()
	{
		if (self::$isActive === null)
		{
			self::$isActive = RequestContext::getInstance()->getMode() != RequestContext::BACKOFFICE_MODE
				&& users_UserService::getInstance()->getCurrentBackEndUser() !== null; 
		}
		return self::$isActive;
	}
}

class PHPTAL_Php_Attribute_CHANGE_Docattr extends website_phptal_EditAttribute
{
	protected function startTag()
	{
		return array("document", "<?php echo ".$this->tag->generator->evaluateExpression($this->expression)." ?>");
	}
}

class PHPTAL_Php_Attribute_CHANGE_Propattr extends website_phptal_EditAttribute
{
	protected function startTag()
	{
		return array("property", $this->expression);
	}
}

/**
 * <a change:edit="document item" />
 */
class PHPTAL_Php_Attribute_CHANGE_Edit extends ChangeTalAttribute
{

	private static $called = false;
	/**
	 * @param array $params
	 * @return String
	 */
	public static function renderEdit($params)
	{
		if (website_phptal_EditAttribute::isActive() && 
			($params["document"] instanceof f_persistentdocument_PersistentDocument))
		{
			$currentURL = LinkHelper::getCurrentUrl();
			$document = $params["document"];
			$documentModel = $document->getPersistentModel();
			//Update
			$editPermission = "modules_".$documentModel->getModuleName().".Update.".$documentModel->getDocumentName();
			$user = users_UserService::getInstance()->getCurrentBackEndUser();
			$permissionService = change_PermissionService::getInstance();
			if ($permissionService->hasPermission($user, $editPermission, $document->getId()))
			{
				$html = "";
				if ($document->isCorrection())
				{
					$isCorrection = true;
					$document = DocumentHelper::getDocumentInstance($document->getCorrectionofid());
				}
				else
				{
					$isCorrection = false;
				}

				if ($document->hasCorrection())
				{
					if ($isCorrection)
					{
						$title = f_Locale::translate("&modules.website.frontoffice.this-is-the-correction-of;", array("documentLabel", $document->getLabel()));
						$src = LinkHelper::getRessourceLink("/changeicons/small/correction.png");
						$html .= "<img src=\"".$src->getUrl()."\" ".f_util_HtmlUtils::buildAttribute("alt", $title)." ".f_util_HtmlUtils::buildAttribute("title", $title)." />";
					}

					if (!$isCorrection)
					{
						$title = f_Locale::translate("&modules.website.frontoffice.there-is-a-correction;");
						$src = LinkHelper::getRessourceLink("/changeicons/small/correction.png");
						$html .= "<img src=\"".$src->getUrl()."\" ".f_util_HtmlUtils::buildAttribute("alt", $title)." ".f_util_HtmlUtils::buildAttribute("title", $title)." />";

						// TODO: something on generic/ViewDetail with corrections.
						$html .= "<a ".f_util_HtmlUtils::buildAttribute("href", LinkHelper::getDocumentUrl(DocumentHelper::getDocumentInstance($document->getCorrectionid())));
						$title = f_Locale::translate("&modules.website.frontoffice.viewCorrection-document;", array("label" => $document->getLabel()));
						$html .= " ".f_util_HtmlUtils::buildAttribute("title", $title);
						$src = LinkHelper::getRessourceLink("/changeicons/small/preview.png");
						$html .= "><img src=\"".$src->getUrl()."\" alt=\"\" />";
						$html .= "</a>";
					}
					$activateLink = self::buildActionLink($user, "activate", $document, "activate", $currentURL, true);
					$html .= $activateLink;
					$html .= self::buildActionLink($user, "deleteCorrection", $document, "delete", $currentURL, true);
					if ($activateLink === null)
					{
						$html .= self::buildActionLink($user, "createWorkflowInstance", $document, "activate", $currentURL);
					}

					$html .= " | ";
				}

				if ($isCorrection)
				{
					$html .= "<a ".f_util_HtmlUtils::buildAttribute("href", LinkHelper::getDocumentUrl($document));
					$title = f_Locale::translate("&modules.website.frontoffice.view-document;", array("label" => $document->getLabel()));
					$html .= " ".f_util_HtmlUtils::buildAttribute("title", $title);
					$src = LinkHelper::getRessourceLink("/changeicons/small/preview.png");
					$html .= "><img src=\"".$src->getUrl()."\" alt=\"\" />";
					$html .= "</a>";
				}

				if ($document->isPublished())
				{
					// de-activate
					$html .= self::buildActionLink($user, "deactivated", $document, "deactivated", $currentURL, true);
				}
				else
				{
					// re-activate
					if ($document->getPublicationStatus() == "DEACTIVATED")
					{
						$html .= self::buildActionLink($user, "reActivate", $document, "reactivate", $currentURL, true);
					}
					elseif ($document->getPublicationStatus() == "DRAFT")
					{
						$activateLink = self::buildActionLink($user, "activate", $document, "activate", $currentURL, true);
						if ($activateLink === null)
						{
							$html .= self::buildActionLink($user, "createWorkflowInstance", $document, "activate", $currentURL);
						}
						else
						{
							$html .= $activateLink;
						}
					}
				}

				// delete
				$html .= self::buildActionLink($user, "delete", $document, "delete", $currentURL, true);
				// edit
				$html .= self::buildActionLink($user, "edit", $document, "edit", $currentURL);

				return $html;
			}
			else
			{
				//return "No permission $permissionName";
				return "";
			}
		}
		return null;
	}

	private static function buildActionLink($user, $actionName, $document, $icon, $currentURL, $confirm = false)
	{
		$documentModel = $document->getPersistentModel();
		if ($actionName == "edit")
		{
			$permActionName = "Update";
		}
		else
		{
			$permActionName = ucfirst($actionName);
		}

		$title = f_Locale::translate("&modules.website.frontoffice.".$actionName."-document;", array("label" => $document->getLabel()));
		$permission = "modules_".$documentModel->getModuleName().".$permActionName.".$documentModel->getDocumentName();
		$permissionService = change_PermissionService::getInstance();
		if ($actionName == "edit" || $permissionService->hasPermission($user, $permission, $document->getId()))
		{
			$linkParams = array("block" => "website_edit",
				"beanId" => $document->getId(),
				"class" => "actionLink ".$actionName,
				"action" => ($actionName == "edit") ? null : $actionName,
				"title" => $title,
				"edit_from_url" => $currentURL);
			if ($confirm)
			{
				$linkParams["onclick"] = "return confirm('".f_Locale::translate("&modules.website.frontoffice.are-you-sure-you-want-js;", array("action" => "'+this.getAttribute('title')+'"))."');";
			}
			$html = PHPTAL_Php_Attribute_CHANGE_actionlink::renderActionlink($linkParams);
			$src = LinkHelper::getRessourceLink("/changeicons/small/".$icon.".png");
			$html .= "<img src=\"".$src->getUrl()."\" alt=\"\" /></a>";
			return $html;
		}
		return null;
	}

	/**
	 * @return Boolean
	 */
	protected function evaluateAll()
	{
		return true;
	}
}

/**
 * <p change:create="" model="myModule/myDocumentName" parentId="${parent/getId}">
 *   Create a new documentName in ${parent/getLabel}
 * </p>
 * <p change:create="" model="myModule/myDocumentName" parent="${parent}">
 *   Create a new documentName in ${parent/getLabel}
 * </p>
 */
class PHPTAL_Php_Attribute_CHANGE_create extends ChangeTalAttribute
{
	private $parametersString;
	public function start()
	{
		$this->tag->headFootDisabled = true;
		$parametersString = $this->initParams();
		$this->tag->generator->doIf('PHPTAL_Php_Attribute_CHANGE_create::checkPerm('.$parametersString . ', $ctx)');
		$this->tag->generator->doEchoRaw('PHPTAL_Php_Attribute_CHANGE_create::renderCreate('.$parametersString.')');
		$this->parametersString = $parametersString;
	}

	/**
	 * @see ChangeTalAttribute::end()
	 */
	public function end()
	{
		$this->tag->generator->pushCode('if ($ctx->hasCreatePerm) {
echo "</a></p>";
$ctx->hasCreatePerm = null;
echo PHPTAL_Php_Attribute_CHANGE_create::renderPendingDocumentList('.$this->parametersString.');
}');
		$this->tag->generator->doEnd();
	}

	/**
	 * @param array<String, mixed> $params
	 * @param PHPTAL_Context $ctx
	 * @return Boolean
	 */
	public static function checkPerm($params, $ctx)
	{
		if (!website_phptal_EditAttribute::isActive())
		{
			return false;
		}
		$user = users_UserService::getInstance()->getCurrentBackEndUser();
		if ($user === null)
		{
			return false;
		}
		if (isset($params["parentId"]))
		{
			$parentId = $params["parentId"];
		}
		elseif (isset($params["parent"]))
		{
			$parent = $params["parent"];
			$parentId = $parent->getId();
		}
		else
		{
			throw new Exception("change:create needs parent or parentId parameter");
		}
		if (!isset($params["model"]))
		{
			throw new Exception("change:create needs model parameter");
		}

		$permissionService = change_PermissionService::getInstance();
		$modelName = "modules_".$params["model"];
		$documentModel = f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName($modelName);
		$createPermission = "modules_".$documentModel->getModuleName().".Insert.".$documentModel->getDocumentName();
		$hasPermission = $permissionService->hasPermission($user, $createPermission, $parentId);
		$ctx->hasCreatePerm = $hasPermission;
		return $hasPermission;
	}

	/**
	 * @param array $params
	 * @return String
	 */
	public static function renderCreate($params)
	{
		if (!website_phptal_EditAttribute::isActive())
		{
			return null;
		}
		if (isset($params["parentId"]))
		{
			$parentId = $params["parentId"];
		}
		elseif (isset($params["parent"]))
		{
			$parent = $params["parent"];
			$parentId = $parent->getId();
		}

		$linkParams = array("block" => "website_edit",
				"class" => "actionLink insert link",
				"action" => "insertForm",
				"parentId" => $parentId,
				"documentModel" => $params["model"],
				"edit_from_url" => LinkHelper::getCurrentUrl());
		return "<p>".PHPTAL_Php_Attribute_CHANGE_actionlink::renderActionlink($linkParams);
	}

	/**
	 * @param array $params
	 * @return String
	 */
	public static function renderPendingDocumentList($params)
	{
		if (isset($params["parentId"]))
		{
			$parentId = $params["parentId"];
		}
		elseif (isset($params["parent"]))
		{
			$parent = $params["parent"];
			$parentId = $parent->getId();
		}

		$query = f_persistentdocument_PersistentProvider::getInstance()->createQuery("modules_".$params["model"])
		->add(Restrictions::childOf($parentId))
		->add(Restrictions::in("publicationstatus", array("DRAFT", "ACTIVE", "DEACTIVATED")))
		->addOrder(Order::desc("document_creationdate"));
		$documents = $query->find();
		$html = "";
		if (f_util_ArrayUtils::isNotEmpty($documents))
		{
			$html .= "<table class=\"normal\">";
			$html .= "<caption>".f_Locale::translate("&modules.website.frontoffice.Pending-document-list;")."</caption>";
			$html .= "<tr><th>".f_Locale::translate("&modules.website.frontoffice.Pending-document-list-label;")."</th><th>".f_Locale::translate("&modules.website.frontoffice.Pending-document-list-creationdate;")."</th><th>".f_Locale::translate("&modules.website.frontoffice.Pending-document-list-actions;")."</th></tr>";

			foreach ($documents as $document)
			{
				$html .= "<tr>";
				$html .= "<td>".$document->getLabelAsHtml()."</td>";
				$html .= "<td>".date_DateFormat::format($document->getUICreationdate())."</td>";
				$html .= "<td>".PHPTAL_Php_Attribute_CHANGE_edit::renderEdit(array("document" => $document))."</td>";
				$html .= "</tr>";
			}
			$html .= "</table>";
		}
		return $html;
	}

	/**
	 * @return Boolean
	 */
	protected function evaluateAll()
	{
		return true;
	}
}