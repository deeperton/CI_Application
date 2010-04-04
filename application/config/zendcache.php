<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');


$config['frontend'] = 'Core';
$config['backend']  = 'File';

$config['frontendOption']  = array(
    'caching'                   => true,
   	'lifetime'                  => 2800 ,
   	'automatic_cleaning_factor' => 20,
    'automatic_serialization'	=> true  //not change
);
        
$config['backendOption']  = array(
    'cache_dir'              => FCPATH.'/cache/data_cache/' ,
    'read_control_type'      => 'strlen' ,
    'file_locking'           => false ,
    'hashed_directory_level' => 0 ,
    'file_name_prefix'		 => 'mi_'
);

?>
