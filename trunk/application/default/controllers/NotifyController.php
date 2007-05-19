<?php

class NotifyController extends Sigma_Controller_Action
{	
	
	/**
	 * Il Sigma_Flow_Token per la gestione dei Token
	 * @var Sigma_Flow_Token
	 */
	private $flow_token;
	
	public function init(){
		$this->flow_token = Sigma_Flow_Token::getInstance();
	}
	
	public function indexAction()
	{
		
		//devo prendere come prima cosa il token, se nn c'è allora genero un errore generico!
		
		$token_id = $this->_getParam('id');
		
		if ( !is_null($token_id) ) {
			
			try {
				
				$content = $this->flow_token->getTokenContent($token_id); //array oppure null
		
				if ( !is_null($content)) {				
					
					$this->view->before_page = $content['url'];
					
					switch ($content['info']['type']) {
						case 'errore':
							$this->view->title = 'Errore';
							$this->view->notify_type = 'warnMacro';
							$this->view->notify_link_text = 'Clicca qui per ritornare alla pagina di partenza';
							break;
						case 'complete':
							$this->view->title = 'Completato';
							$this->view->notify_type = 'infoMacro';
							$this->view->notify_link_text = 'Clicca qui per ritornare alla pagina di partenza';
							break;
						case 'conferma':
							$this->view->title = 'Conferma';
							$this->view->notify_link_text = 'Clicca qui per ritornare alla pagina di partenza';
							$this->view->notify_link2_text = 'Clicca qui per confermare';
							$this->view->next_page = $content['info']['next'];
							break;
						default:
							$this->view->title = 'Notifica';
							$this->view->notify_type = 'checkMacro';
							$this->view->notify_link_text = 'Clicca qui per ritornare alla pagina di partenza';
							break;
					}
					
					$this->view->notify_text = $content['info']['text'];

					$this->flow_token->delete($token_id); // ho finito di utilizzarlo
					
				} else {
					$this->view->title = 'Errore';
					$this->view->before_page = '/home/';
					$this->view->notify_type = 'warnMacro';
					$this->view->notify_text = array('spiacente questa notifica non &egrave; presente');
					$this->view->notify_link_text = 'Clicca qui per ritornare alla pagina di partenza';
				}

			} catch (Exception $e) {
				echo $e->getMessage();
			}
			
			$this->view->actionTemplate = 'contents/notify.tpl';
			
			
		} else {
			
			$this->view->title = 'Errore';
			$this->view->notify_type = 'warnMacro';
			$this->view->before_page = '/home/';
			$this->view->actionTemplate = 'contents/notify.tpl';
			$this->view->notify_text = array('Generic problem');
			$this->view->notify_link_text = 'Clicca qui per ritornare alla pagina di partenza';
			
		}
		
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	
}

?>