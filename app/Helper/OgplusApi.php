<?php
namespace App\Helper;
use App\Helper\FetchingInterface;


class OgplusApi  implements FetchingInterface {
   
	protected  $client;
	protected  $config;
	protected  $gameProvider ="ogplus";
   
	public function __construct()
    {
		$this->client = new \GuzzleHttp\Client();
		$environment = \App::environment();
		$this->config =  \Config::get($environment.".".$this->gameProvider);
		
    }

	protected  function  getToken()
	{
		echo $this->config['url'];
		
		$res = $this->client->request('PUT', $this->config['url'], [
			'auth' => ['user', 'pass']
		]);
		
		// echo $res->getBody();
		
		// $response  = $this->client->put($this->config['url'], [
			// 'headers'         => ['X-Foo' => 'Bar'],
			// 'body'            => [
				// 'field' => 'abc',
				// 'other_field' => '123'
			// ],
			// 'allow_redirects' => false,
			// 'timeout'         => 5
		// ]);
		
		// $body = $response->getBody();
		// echo $body;
	}
	
	public function getTransaction(array $argv)
	{
		// var_dump($this->config);
		echo $this->getToken();
	}
}
