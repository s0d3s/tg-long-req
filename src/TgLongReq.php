<?php
namespace TgLongReq;
	/*
			MIT License

		Copyright (c) 2019 SoDeS

		Permission is hereby granted, free of charge, to any person obtaining a copy
		of this software and associated documentation files (the "Software"), to deal
		in the Software without restriction, including without limitation the rights
		to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
		copies of the Software, and to permit persons to whom the Software is
		furnished to do so, subject to the following conditions:

		The above copyright notice and this permission notice shall be included in all
		copies or substantial portions of the Software.

		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
		IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
		FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
		AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
		LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
		OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
		SOFTWARE.
	*/





   /*###################################################################################################################################
	#		It`s a basic class for create and execute 'longer' request in telegram bot	(for exemple: when usr working with bot menu) 
	*###################################################################################################################################
	*
	*	I'll tell an example soon...
	*/

class TgLongReq{
	
	/*
	@	$usrid			INT								tg usr id
	@	$usr_req_dir	STR								standart request dir
	@	$ReqFunc		ARRAY							AssoTable->'ReqName'=>'Req Handler Func'
														Function must recive $tg_result(contain info about usr answer), the rest is not necessary
	@	$tg_api			new Api()						Obj returned from irazasyed\telegram-bot-sdk Api()
	@	$tg_result		new Api()->getWebhookUpdates()	Data about usr(usrname/messege/time/and oth.)
	*
	!	IF YOU WANT 'tg_api' and 'tg_result' can be of any type, or be NULL, depending on their further use
	*/
	private $temp_file_prefix = 'TempData';
	public  $temp_data_dir;
	public  $usrid;
	public  $usr_req_dir;
	private $ReqFunc = array();
	private $tg_result;
	private $tg_api;
	private $err_tab=array();
	
	
	/*
	!	You must transfer both api and the result 
	!	to the constructor in order not to connect telegram-bot-sdk, 
	!	and to facilitate the class itself.
	*/
	
	public function __construct($u_id, $ReqFunc, $usr_req_dir = 'req/', $tg_api=null, $tg_result=null){
		
		$this->usrid 		= $u_id;
		$this->usr_req_dir	= $_SERVER['DOCUMENT_ROOT'].$usr_req_dir;
		$this->ReqFunc		= $ReqFunc;
		$this->tg_result	= $tg_result;
		$this->tg_api		= $tg_api;	
		$this->temp_data_dir= $this->usr_req_dir.$this->temp_file_prefix.'/';
		
		if(!file_exists($this->temp_data_dir)) mkdir($this->temp_data_dir, 0777, true);
		if(!file_exists($this->usr_req_dir)) mkdir($this->usr_req_dir, 0777, true);
	}
	
	/*		RETRUNS
		@ 	$BaseRtrnObj	ARRAY	array('error'=>true/false, 'err_discript'=>none/'str', 'func_res'=>none/somthing)//ReqHand() return 'func_res'=>REQ_FUNC_RTRN-result
	*	
		But ReqCheck() return true(if req exists) otherwise false
	*/
	
	public function ReqCheck(){
		/*
			CHECKS FOR REQUEST
		*/
		$file_list = glob( $this->usr_req_dir.'/'.$this->usrid."*.txt");
		if(!$file_list)
			return false; 
		else 
			return trim(file($file_list[0], FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES)[1]);
	}
	
	public function ReqCreate($name, $type = 'usual'){
		/*
			FOR CREATE NEW REQUEST
		*/
		/*
		@	$name	STR		request_name
		@	$type	STR		request_type(usual/inline)
		)
		*/
		if($this->ReqCheck()) $this->ReqDel();
		$name=trim($name);
		foreach($this->ReqFunc as $key=>$val){
			if($key == $name){
				
				$usr_req_file = fopen($this->usr_req_dir.'/'.$this->usrid . date("-y.m.d-h_i_s").'.txt', 'w+');
				if(!file_exists($usr_req_dir)) mkdir($usr_req_file, 0777, true);
				fwrite($usr_req_file, $key."\n".$type);
				fclose($usr_req_file);
				return $this->SetError(array($key, $type));
			}
		}
		return $this->SetError(NULL, true, 'REQ_DIDNT_EXIST_IN_THE_TABLE');
	}
	public function ReqDel(){
		/*
			DELETE USR REQUESTS
		*/
		global $usrid, $usr_lp_req_dir;
		foreach(glob("$usr_lp_req_dir/$usrid*.txt") as $reqF) unlink($reqF);
		return $this->SetError();
	}
	
	public function ReqHand(){
		/*
			HANDLE REQUEST
		*/
		
		/*	Struct of file(req):
		*	[ req_name  ]
		*	[type_of_req]
		*/
		$curreq 	= "";
		$curtype 	= "";
		foreach (glob($this->usr_req_dir."/".$this->usrid."*.txt") as $reqF) {
			$reqfile 	= file($reqF, FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);
			$curreq 	= trim($reqfile[0]);
			$curtype	= trim($reqfile[1]);
			unlink($reqF);
			break;
		}
		
		foreach($this->ReqFunc as $key => $value){
				if($key == $curreq) return $this->SetError(($this->ReqFunc[$key])($this->tg_result, $this, $key));
		}
		return $this->SetError(NULL, true, 'REQ_DIDNT_MATCH');
	}
	
	public function SaveToTemp($data){
		/*
			SAVING TEMPORARY DATA TO TEMP FILE
		*/
		
		/*	Struct of file(temp data file):
		*	[json_ensoded data]
		*/
		/*
		@	$data		|ALL without 'resorce'|		Something for writing to file
		*/
		
		$str2write = json_encode($data);
		$tmp_name = $this->temp_data_dir.$this->temp_file_prefix.'-'.$this->usrid.'.txt';
		
		$temp_file = fopen($tmp_name, 'w+');
		fwrite($temp_file, $str2write);
		fclose($temp_file);
		
		return $this->SetError($str2write, false);
	}
	
	public function GetFromTemp($hold_it = false){
		/*
			RETURNS THE OBJECT RECEIVED FROM THE TEMP FILE
		*/
		/*
		@	$hold_it		BOOL		If true, then file is not deleted after use
		*/
		
		$tmp_name = $this->temp_data_dir.$this->temp_file_prefix.'-'.$this->usrid.'.txt';
			
		$temp_data = file_get_contents ($tmp_name);
		$rtrn_obj = json_decode($temp_data);
		
		if(!$hold_it) unlink($tmp_name);	
		return $rtrn_obj;
	}
	
	public function GetError(){
		/*
			GET LAST ERROR
		*/
		$rtrn_arr = array();
		
		if($this->err_tab['error'])
			$rtrn_arr['err_discript'] = $this->err_tab['err_discript'];			
		$rtrn_arr['error'] = $this->err_tab['error'];
		
		return $rtrn_arr;
	}
	private function SetError(&$func_res=NULL, $error = false, $err_discript="SOME_ERROR"){
		
		if($error)
			$this->err_tab['err_discript'] = $err_discript;
		$this->err_tab['error'] = $error;
		$rtrn_arr = $this->err_tab;
		$rtrn_arr['func_res'] = $func_res;
		
		return $rtrn_arr;
	}
}

?>