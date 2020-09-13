<?php 
namespace App\Controller;
class BasicController{
    
    public  function writeMarker($file,$str)
    {
        $fileResource  = fopen($file,"w+");
        fwrite($fileResource,$str);
        fclose($fileResource);
    }
    
    public  function readMarker($file,$default="")
    {
        $fileResource  = fopen($file,"r"); 
        $str = fgets($fileResource);
        fclose($fileResource);
        if($str==""  && $default!="")
        {
            return $default;
        }
        return $str;
    }
    
    public  function deleteMarker($file)
    {
       $fileResource  = fopen($file,"w+");
        fwrite($fileResource,'');
        fclose($fileResource);
    }
    
}