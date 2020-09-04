<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Exceptions;


class fetching extends Command 
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:fetching {--gameProvider=} {--func=}';
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
		$error="";
		try {  
			$gameProvider = $options['gameProvider'];
			$func = $options['func'];
			
			if($gameProvider=="")
			{
				$error=sprintf(Exceptions\MyException::ParameterIsRequired,'gameProvider');
				throw new  Exceptions\MyException($error);
			}
			
			if($func=="")
			{
				$error=sprintf(Exceptions\MyException::ParameterIsRequired,'func');
				throw new  Exceptions\MyException($error);
			}
			
			$fetchingClass =  'App\\Helper\\'.ucfirst($gameProvider)."Api";
			if(!class_exists($fetchingClass))
			{
				$error=Exceptions\MyException::GPFetchingClassDoesNotExist;
				throw new  Exceptions\MyException($error);
			}
			
			$fetchingClass = new $fetchingClass();
			$fetchingClass->$func($options);
			
			
		} catch (Exceptions\MyException $e) {		
			$output['error'] = $error;
		} finally{
			print_r($output);
		}
    }
}
