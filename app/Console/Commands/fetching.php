<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Exceptions;
use App\Helper\PublicFunction;

class fetching extends Command 
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:fetching {--gameProvider=} {--func=} {--deleteIsProcessing=} {--sweeperMarker=default} {--deleteMarker=} {--length=} {--startTime=} {--endTime=} {--username=} {--unique=} {--gamecode=} {--extra=}';
	protected $apiObj ;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetching api data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        
        
        
		$options = $this->option();
		$output =[];
        $info=[];
		$error="";
        $status="fail";
        $runStartTime = time();
		try {  
			$gameProvider = $options['gameProvider'];
			$func = $options['func'];
			$deleteIsProcessing = $options['deleteIsProcessing'];
			$sweeperMarker = $options['sweeperMarker'];
			$options['startTime'] = str_replace("T"," ",$options['startTime']);
			$options['endTime'] = str_replace("T"," ", $options['endTime']);
			
			if($gameProvider=="")
			{
				$error=sprintf(Exceptions\MyException::ParameterIsRequired,'gameProvider');
				throw new  Exceptions\MyException($error);
			}
			
			if($func=="")
			{
				$error=sprintf(Exceptions\MyException::ParameterIsRequired,'func');
				throw new  \Exception($error);
			}
			
			$fetchingClass =  'App\\Controller\\'.ucfirst($gameProvider)."Controller";
			if(!class_exists($fetchingClass))
			{
				$error=Exceptions\MyException::GPFetchingClassDoesNotExist;
				throw new  \Exception($error);
			}
			
			$fetchingClass = new $fetchingClass();
            if(!method_exists( $fetchingClass , $func))
            {
                $error=Exceptions\MyException::GPFetchingClassFunctionDoesNotExist;
                $error = sprintf($error,$func);
                throw new  \Exception($error);
            }
            
            $isProcessingDir = storage_path().DIRECTORY_SEPARATOR."markers".DIRECTORY_SEPARATOR.ucfirst($gameProvider).DIRECTORY_SEPARATOR.'isProcessing';
            if(!is_dir($isProcessingDir)){
                mkdir($isProcessingDir, 0755, true);
            }
            $isProcessingFile=$isProcessingDir.DIRECTORY_SEPARATOR.$func.'_'.$sweeperMarker.'_isProcessing'.'.txt';
            
            if($deleteIsProcessing =="delete")
            {
                unlink($isProcessingFile);
            }
            
            
            if(file_exists($isProcessingFile))
            {
               $isProcessing = $fetchingClass->readMarker($isProcessingFile);
               $isProcessing  = explode('|',$isProcessing);
               $isProcessing = $isProcessing[0];
            }else{
                $isProcessing =0;
            }
            
            if($isProcessing==1)
            {
                $error='isProcessing';
                throw new  \Exception($error);
            }
            
            $fetchingClass->writeMarker($isProcessingFile,'1|'.$runStartTime);
            
            $sweeperMarkerDir = storage_path().DIRECTORY_SEPARATOR."markers".DIRECTORY_SEPARATOR.ucfirst($gameProvider).DIRECTORY_SEPARATOR.'marker';
            if(!is_dir($sweeperMarkerDir)){
                mkdir($sweeperMarkerDir, 0755, true);
            }
            $sweeperMarkerFile=$sweeperMarkerDir.DIRECTORY_SEPARATOR.$sweeperMarker.'_'.$func.'.txt';
            $fetchingClass->sweeperMarkerFile = $sweeperMarkerFile;
            
			$result = $fetchingClass->$func($options);
            $status ="success";
            $info = [
                'executionTime'=>intval(time())-intval($runStartTime),
                'isProcessingFile'=> $isProcessingFile,
                'sweeperMarkerFile'=>$sweeperMarkerFile
            ];
            $output = array_merge($output,$result);
			$fetchingClass->writeMarker($isProcessingFile,'0|'.$runStartTime);
		} 
        catch (\Exception $e) {
            $output['error']=  $e->getMessage();
            if($output['error']!="isProcessing")
            {
                $fetchingClass->writeMarker($isProcessingFile,'0|'.$runStartTime);
            }
        }
        finally{           
            PublicFunction::outputFormat($status,$output,$info);
			print_r($output);
		}
    }
}
