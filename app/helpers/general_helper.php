<?php

function debug($string)
{
	echo '<pre>';
	print_r($string);
	echo '</pre>';
	exit;
}
function no_hari($hari){
	switch($hari){
		case 'Sun':
			$hari_ini = 7;
		break;
		case 'Mon':			
			$hari_ini = 1;
		break;
		case 'Tue':
			$hari_ini = 2;
		break;
		case 'Wed':
			$hari_ini = 3;
		break;
		case 'Thu':
			$hari_ini = 4;
		break;
		case 'Fri':
			$hari_ini = 5;
		break;
		case 'Sat':
			$hari_ini = 6;
		break;
		default:
			$hari_ini = null;		
		break;
	}
 
	return $hari_ini;
 
}

function noBPJS($value, $length = 13) {

    $jml    = strlen($value);
    $inv    = $length-$jml;
    $zero   = "";

    for ($i=1; $i <= $inv; $i++) { 
        $zero .= "0";
    }

    $no = $zero.$value;

    return $no;
}
 

function norm($value)
{
    $no = '';
    $jml = strlen($value);
    if ($jml == 1)
        $no = "00000" . $value;
    if ($jml == 2)
        $no = "0000" . $value;
    if ($jml == 3)
        $no = "000" . $value;
    if ($jml == 4)
        $no = "00" . $value;
    if ($jml == 5)
        $no = "0" . $value;
    if ($jml >= 6)
        $no = $value;
    if ($jml == 0)
        $no = "000001";
    return $no;
}


function validateFormatDate($date, $format = 'Y-m-d')
{
	$d = DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) === $date;
}

function validateBackDate($date, $format = 'Y-m-d')
{
	$now 	= date('Y-m-d');
	$dnow 	= DateTime::createFromFormat($format, $now);
	$dget	= DateTime::createFromFormat($format, $date);
	
	if($dnow>$dget){
		return true;
		// return 0;
	} else {
		return false;
		// return 1;
	}
}

function validateRangeDate($date_awal, $date_akhir, $format = 'Y-m-d')
{

	$date_awal 	= DateTime::createFromFormat($format, $date_awal);
	$date_akhir	= DateTime::createFromFormat($format, $date_akhir);
	
	if($date_akhir<$date_awal){
		return true;
		// return 0;
	} else {
		return false;
		// return 1;
	}
}

function validateInRangeDate($date_search, $range = 90, $format = 'Y-m-d')
{
	$date_search 	= DateTime::createFromFormat($format, $date_search);
	$date_akhir		= DateTime::createFromFormat($format, date('Y-m-d', strtotime(date('Y-m-d'). ' + '.(int)$range.' days')));

	if($date_search>$date_akhir){
		// JIKA TANGGAL LEBIH DARI HARI INI + RANGE
		return true;
		// return 0;
	} else {
		return false;
		// return 1;
	}
}

function keybpjs($time)
{
    date_default_timezone_set('UTC');   
    // $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
    $tStamp = $time;
    $key = '231246dA1995F61' . $tStamp;
    return $key;
}

function stringDecrypt($key, $string){
            
      
	$encrypt_method = 'AES-256-CBC';

	// hash
	$key_hash = hex2bin(hash('sha256', $key));

	// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
	$iv = substr(hex2bin(hash('sha256', $key)), 0, 16);

	$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

	return $output;
}

function decompress($string){
  
    return \LZCompressor\LZString::decompressFromEncodedURIComponent($string);

}
function fullDecompress($data,$time){  
    $string = stringDecrypt(keybpjs($time),$data);
    return \LZCompressor\LZString::decompressFromEncodedURIComponent($string);
}


function consFinal($response){
	$data = json_decode($response, TRUE); 
	if ($data['metaData']['code'] == 200) {
		$dec = fullDecompress($data['response'],$data['time']);
		if (empty($dec)) {
			$hasil = array('metaData' => array('code' => 203, 'msg' => 'Gagal mengambil data dari bpjs, silahkan  ulangi lagi'));
		} else {
			$data['response'] = json_decode($dec);
			$hasil = json_encode($data);

		// print($hasil);
		}
	} else {
		$hasil = $response;
	}
	// print_r($hasil)
	header('Content-Type: application/json; charset=utf-8');
	echo $hasil;
}

function consFinalhFis($response){
	$data = json_decode($response, TRUE); 
	if ($data['metadata']['message'] == 'OK') {
		$dec = fullDecompress($data['response'],$data['time']);
		if (empty($dec)) {
			$hasil = array('metaData' => array('code' => 203, 'msg' => 'Gagal mengambil data dari bpjs, silahkan  ulangi lagi'));
		} else {
			$data['response'] = json_decode($dec);
			$hasil = json_encode($data);

		// print($hasil);
		}
	} else {
		$hasil = $response;
	}
	// print_r($hasil)
	header('Content-Type: application/json; charset=utf-8');
	echo $hasil;
}