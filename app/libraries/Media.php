<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Media {

    public function __construct()
    {
        $this->ci =& get_instance();
    }

	public function do_upload($path, $filename, $source)
	{
        $write_access = substr(sprintf('%o', fileperms($path)), -4);
        if($write_access == '0755' || $write_access == '0777') {
			$output_file 	= $path . $filename;
			$data 			= base64_decode($source);
			file_put_contents($output_file, $data);
			return $filename;    	
        } else {
            die("Cannot write to folder. Permission denied.");
        }

	}

}
