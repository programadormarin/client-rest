<?php
namespace app {
	require 'ClientException.php';
	use app\ClientException;
	class Client {
		private $url;
		private $metodo = null;
		private $params = null;
		private $httpStatus = null;
		private $response = null;
		private $id = null;
		private $handler = null;
		
		public function __construct($url) {
			$this->url = $url;
		}
		
		private function initCurl ($id = null) {
			if ($id) $this->handler = curl_init($this->url .'/'. $id);
			else $this->handler = curl_init($this->url);
			curl_setopt($this->handler, CURLOPT_HEADER, false);
			curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->handler, CURLOPT_TIMEOUT, 10);
		}
		
		private function closeCurl () {
			$this->response = curl_exec($this->handler);
			$this->httpStatus = curl_getinfo($this->handler, CURLINFO_HTTP_CODE);
			curl_close($this->handler);
		}
		
		private function getResponse() {
			$this->closeCurl();
			try {
				$this->validStatus();
				return $this->response;
			} catch (ClientException $e) {
				echo $e->getMessage();
			}
		}
		
		/**
		 * Acessa o rest por GET e retorna um ou vários 
		 * @param int $id Id a ser buscado
		 */
		public function get($id = null, array $params = null) {
			$this->initCurl($id);
			if (!is_null($params)) {
				$this->url .=  "?";
				foreach ($params as $nome => $valor) {
					$valor = str_replace(' ', '', $valor);
					$this->url .=  "$nome=$valor&";
				}
				$this->url = substr($this->url, 0, -1);
			}
			$this->getResponse();
		}
		
		/**
		 * Faz a inclusão via post
		 * @param array $params Array nomeado com parâmetros para a novo insersão
		 */
		public function post(array $params) {
			$this->initCurl();
			curl_setopt($this->handler, CURLOPT_POST , true);
			curl_setopt($this->handler, CURLOPT_POSTFIELDS , http_build_query($params));
			$this->getResponse();
		}
		
		/**
		 * Faz a alteração via put
		 * @param array $params Array nomeado com parâmetros a serem alterados
		 */
		public function put($id, array $params) {
			$this->initCurl($id);
			curl_setopt($this->handler, CURLOPT_CUSTOMREQUEST , 'PUT');
			curl_setopt($this->handler, CURLOPT_POSTFIELDS , http_build_query($params));
			$this->getResponse();
		}
		
		/**
		 * Delete via rest
		 * @param int $id Id do que deve ser deletado
		 */
		public function delete($id = null) {
			$this->initCurl($id);
			curl_setopt($this->handler, CURLOPT_CUSTOMREQUEST , 'DELETE');
			$this->getResponse();
		}
		
		private function validStatus () {
			switch ($this->httpStatus) {
				case "400" :
					throw new ClientException("Erro 400 - Bad request");
				break;
				case "401" :
					throw new ClientException("Erro 401 - Não autorizado");
				break;
				case "403" :
					throw new ClientException("Erro 403 - Acesso proibido");
				break;
				case "404" :
					throw new ClientException("Erro 404 - Não Encontrado");
				break;
				case "500" :
					throw new ClientException("Erro 500 - Erro interno");
				break;
				case "501" :
					throw new ClientException("Erro 501 - Não interpretado");
				break;
			}
		}
		
	}
}