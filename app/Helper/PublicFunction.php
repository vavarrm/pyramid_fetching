<?php
namespace App\Helper;
class PublicFunction  {
	public function __construct()
    {
    }
    
    static public  function  outputFormat($status, $result, $info)
    {
        $output  =[
            'status'    =>$status,
            'result'    =>$result,
            'info'      =>$info
        ];
        
        print_r($output);
        die();
    }
}
