<?php
namespace App\Controller;
use App\Controller\ControllerInterface;
use App\Controller\BasicController;
use App\Exceptions;
class OgplusController extends BasicController implements ControllerInterface {
   
	protected  $client;
	protected  $config;
	protected  $gameProvider ="ogplus";
	protected  $gameCoderArray =[
        'life' =>[
            "baccarat",
            "dragontiger",
            "moneywheel",
            "roulette",
            "sicbo",
            "fantan",
            "niuniu",
            "3cards",
        ],
        'cardgame'=>[
            "tw_niuniu",
            "tw_three_cards",
            "tw_landlord",
            "tw_multi_niuniu",
            "tw_banker_niuniu",
            "tw_multi_three_cards",
            "tw_multi_texas",
            "tw_dragon_tiger",
            "tw_quick_three_cards",
            "tw_double_doudizhu",
            "tw_jewel_roulette",
            "tw_three_facecard",
            "tw_two_eight_bar",
        ]
    ];
   
	public function __construct()
    {
        $this->config = [
            'url' =>env('OGPLUS_API_URL', 'https://ichips-test.oriental-game.com'),
            'username' =>env('OGPLUS_API_USERNAME', 'marsbo_ogplus'),
            'password' =>env('OGPLUS_API_PASSWORD', 'password12223!'),
        ];
        $this->client = new \GuzzleHttp\Client(['base_uri' => $this->config['url']]);
    }

	protected  function  getToken()
	{
        $output =[];
        $responseArray =[];
        $requestStr  ='v1/auth/token?username=%s&password=%s';
        $requestStr =sprintf($requestStr,$this->config['username'] , $this->config['password'] );
        $responseStr = $this->client->request('PUT', $requestStr);
        $responseArray =  json_decode($responseStr->getBody() ,true);
        $token  =$responseArray['data']['token'];
        return $token;
	}
	
	public function getTransaction(array $argv)
	{
        $output =[];
        $token =  $this->getToken();
        if($token=="")
        {
            return false;
        }
        $headers=[
            'headers' => [
                'token' => $token
        ]];
        
        $lengthAllow=[
            25,
            50,
            100,
            1000,
            2000,
            5000
        ];
        $length  = (in_array($argv['length'], $lengthAllow))?$argv['length']:5000;
            
        if($argv['deleteMarker'] == "delete")
        {
            $this->deleteMarker($this->sweeperMarkerFile);
        }
                       
        $sweeperMarker = $this->readMarker($this->sweeperMarkerFile);
        $sweeperMarker = json_decode($sweeperMarker,true);
        
        $type = ($argv['extra']=="")?'life':$argv['extra'];
        $type = (isset($this->gameCoderArray[$type]))?$type:'life';
        $gameCodeArray = $this->gameCoderArray[$type];
        
        if(in_array($argv['gamecode'], $gameCodeArray))
        {
           $gameCodeArray = [ $argv['gamecode']];
        }
        
        
        foreach($gameCodeArray as  $value)
        {
            $_requestAry =[];
            if( $argv['startTime'] !="" && $argv['endTime'])
            {
                $start  = 1;
                $_requestAry = [
                    'start_date' =>$argv['startTime'],
                    'end_date' =>$argv['endTime'],
                ];
                if(isset($sweeperMarker[$value]))
                {
                    $start  = $sweeperMarker[$value]; 
                }
            }
            else if($argv['unique'] !="")
            {
                $start  = 1;
                $_requestAry['bet_code'] = $argv['unique'];
            }
            else if(isset($sweeperMarker[$value]))
            {
                $start  = $sweeperMarker[$value];
            }else
            {
                $start  = 1;
            }
            $requestAry  =[
                'start'      =>$start ,
                'length'     =>$length,
                'game_code'   =>$value
            ];
            
            
            $requestAry = array_merge($requestAry , $_requestAry);
            $url = $this->bindRequestUrl('transaction', $requestAry,  $argv['username']);
            $requestUrl[$value] = $url ;
            $lastRecordId[$value] = $start;
            $promises[$value] =$this->client->getAsync($url, $headers);
        }
        $results = \GuzzleHttp\Promise\unwrap($promises);
        
        foreach ($results as $key => $result) {
            $responseArray =  json_decode($result->getBody() ,true);
            $response[$key]=[
                'count_records'=>$responseArray['count_records'],
                'count_results'=>$responseArray['count_results'],
                'last_record_id'=>$responseArray['last_record_id'],
            ];
            
            if($responseArray['last_record_id'] !=0)
            {
                $saveMarker[$key] = $responseArray['last_record_id'];
            }else
            {
                $saveMarker[$key] = $lastRecordId[$key];
            }
            
           
        }
              
        $output= [
            'url' => $this->config['url'],
            'request' =>$requestUrl,
            'response'=>$response,
            'saveMarker' =>$saveMarker
        ];

        $this->writeMarker($this->sweeperMarkerFile, json_encode($saveMarker));
        return $output;
	}
    
    
    private function bindRequestUrl($func, array $ary, $username="")
    {
        foreach($ary as $key =>$value)
        {
            $_ary[] = sprintf('%s=%s', $key, $value);
        }
        $requestUrl  ="v1/history/{$func}/{$username}?user_type=MARS&".join('&',$_ary);
        return $requestUrl;
    }
}
