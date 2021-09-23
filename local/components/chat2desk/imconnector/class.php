<?php
use \Bitrix\Main\Loader,
	\Bitrix\Main\Web\Uri,
	\Bitrix\Main\Data\Cache,
	\Bitrix\Main\LoaderException,
	\Bitrix\Main\Localization\Loc;
use \Bitrix\ImConnector\Status,
	\Bitrix\ImConnector\Library,
	\Bitrix\ImConnector\Connector;

class ImConnectorYBWSPactIM extends CBitrixComponent
{
	private $cacheId;

	protected $connector = 'chat2desk_imconnector';
	protected $module = "chat2desk.imconnector";
	protected $error = array();
	protected $messages = array();
	/**@var \Bitrix\ImConnector\Status */
	protected $status;

	protected $pageId = 'page_chat2desk_imconnector';

	/**
	 * Check the connection of the necessary modules.
	 * @return bool
	 * @throws LoaderException
	 */
	protected function checkModules()
	{
		if (Loader::includeModule('imconnector'))
		{
			return true;
		}
		else
		{
			ShowError(Loc::getMessage('IMCONNECTOR_COMPONENT_BASECONNECTOR_MODULE_NOT_INSTALLED'));
			return false;
		}
	}

	protected function initialization()
	{
		$this->status = Status::getInstance($this->connector, $this->arParams['LINE']);

		$this->arResult["STATUS"] = $this->status->isStatus();
		$this->arResult["ACTIVE_STATUS"] = $this->status->getActive();
		$this->arResult["CONNECTION_STATUS"] = $this->status->getConnection();
		$this->arResult["REGISTER_STATUS"] = $this->status->getRegister();
		$this->arResult["ERROR_STATUS"] = $this->status->getError();

		$this->cacheId = serialize(array($this->connector, $this->arParams['LINE']));

		$this->arResult["PAGE"] = $this->request[$this->pageId];
	}

	protected function setStatus($status)
	{
		$this->arResult["STATUS"] = $status;

		$this->status->setConnection($status);
		$this->arResult["CONNECTION_STATUS"] = $status;
		$this->status->setRegister($status);
		$this->arResult["REGISTER_STATUS"] = $status;

		$this->status->setError(false);
		$this->arResult["ERROR_STATUS"] = false;
	}

	/**
	 * Reset cache
	 */
	protected function cleanCache()
	{
		$cache = Cache::createInstance();
		$cache->clean($this->cacheId, Library::CACHE_DIR_COMPONENT);
		$cache->clean($this->arParams['LINE'], Library::CACHE_DIR_INFO_CONNECTORS_LINE);
	}

	public function saveForm()
	{
		//If been sent the current form
		if ($this->request->isPost() && !empty($this->request[$this->connector. '_form']))
		{
			//If the session actual
			if(check_bitrix_sessid())
			{
				//Activation
				if($this->request[$this->connector. '_active'] && empty($this->arResult["ACTIVE_STATUS"]))
				{
					$this->status->setActive(true);
					$this->arResult["ACTIVE_STATUS"] = true;

					$this->arResult["CONNECTION_STATUS"] = true;
					$this->status->setConnection(true);
					$this->arResult["REGISTER_STATUS"] = true;
					$this->status->setRegister(true);

					//Reset cache
					$this->cleanCache();
				}

				if(!empty($this->arResult["ACTIVE_STATUS"]))
				{
					//saving config
					if($this->request[$this->connector. '_save'])
					{
						$this->arResult['FORM']['SERVICE_TOKEN'] = $this->request->get('SERVICE_TOKEN');
						COption::SetOptionString($this->module,"service_token_".$this->arParams['LINE'],$this->arResult['FORM']['SERVICE_TOKEN']);

						CModule::IncludeModule($this->module);
						Chat2DeskIMConnector::SetWebhook($this->arParams['LINE']);

						$this->cleanCache();	
					}

					if($this->request[$this->connector. '_del'])
					{
						Status::delete($this->connector, $this->arParams['LINE']);
						$this->arResult["STATUS"] = false;
						$this->arResult["ACTIVE_STATUS"] = false;
						$this->arResult["CONNECTION_STATUS"] = false;
						$this->arResult["REGISTER_STATUS"] = false;
						$this->arResult["ERROR_STATUS"] = false;
						$this->arResult["PAGE"] = '';

						//Reset cache
						$this->cleanCache();
					}
				}
			}
			else
			{
				$this->error[] = Loc::getMessage("IMCONNECTOR_COMPONENT_BASECONNECTOR_SESSION_HAS_EXPIRED");
			}
		}
	}

	public function constructionForm()
	{
		global $APPLICATION;

		$this->arResult["NAME"] = Connector::getNameConnectorReal($this->connector);

		$this->arResult["URL"]["DELETE"] = $APPLICATION->GetCurPageParam("", array($this->pageId));
		$this->arResult["URL"]["SIMPLE_FORM"] = $APPLICATION->GetCurPageParam($this->pageId . "=simple_form", array($this->pageId));

		$this->arResult["FORM"]["STEP"] = 1;

		if($this->arResult["ACTIVE_STATUS"])
		{
			$this->arResult["FORM"]['SERVICE_TOKEN'] = COption::GetOptionString($this->module, "service_token_".$this->arParams['LINE'], "");
		}

		$this->arResult["CONNECTOR"] = $this->connector;
	}

	public function executeComponent()
	{
		$this->includeComponentLang('class.php');

		if($this->checkModules())
		{
			if(Connector::isConnector($this->connector))
			{
				$this->initialization();

				$this->saveForm();

				$this->constructionForm();

				//var_dump($this->arResult['FORM']);

				if(!empty($this->error))
					$this->arResult['error'] = $this->error;

				if(!empty($this->messages))
					$this->arResult['messages'] = $this->messages;

				$this->includeComponentTemplate();
			}
			else
			{
				ShowError(Loc::getMessage("IMCONNECTOR_COMPONENT_BASECONNECTOR_NO_ACTIVE_CONNECTOR"));

				return false;
			}
		}
	}
};