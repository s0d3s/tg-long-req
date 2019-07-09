<?php

	
	include_once('varfunc.php');
	
	
	
	use Telegram\Bot\Api; 												//Подключаем сам тг-бот-сдк
	use Telegram\Bot\Keyboard\Keyboard;									//Подкючаем класс для клавиатур
	
	use TgLongReq\TgLongReq;											//Класс для работы с меню, собственной персоной
		
	$BOT_THIS_NAME			=		"IamBOT";							//адресс бота в тг
	$BOT_THIS_TOKEN 		=		'826683436:AAGaJQZno3FptgKQNVT3dozN-6w_tSp8oeI';			//токен выднный @BOTFATHER-ом
	
	$BOT_FILE_DIR			= $DIR.'/bot_root/';
	$BOT_req_dir			= '/bot_root/'.'req/';						//Директория в которой будут храниться данные запросов на запрос
	$CUR_KBRD_0				= $def_lokm['MainMenu'];
	
	
	
	$tapi 					=		new Api($BOT_THIS_TOKEN); 			//создаём объект для работы с ботом
	$result 				= 		$tapi -> getWebhookUpdates();		//получаем информацию о пользователе и полседнем сообщении (https://core.telegram.org/bots/api#update)
	
	if(!isset($result["message"]["chat"]["id"])&&(!isset($result ['inline_query'])||!isset($result['callback_query']))){echo "It`s only telegram bot. Pls go to @$BOT_THIS_NAME"; exit;} //заглушка, скрипт будет работать только с запросами от телеграмм(можно обойти)

	$text 					= $result["message"]["text"]; 				//Ответ юзера
	$unformtime				= $result["message"]["date"];				//UNIX-метка сообщеня
    $usrname 				= $result["message"]["from"]["username"];	//Сигна юзера
	$usrid 					= $result["message"]["from"]["id"];			//ID юзера
	$chat_id				= $usrid;
	
	$tgreq = new TgLongReq($usrid, $BOT_FUNC_ASSO, $BOT_req_dir , $tapi,  $result, true);//Объект для работы с запросами на запрос
	
	//Проаеряем наличие запроса на запрос
	if($tgreq->ReqCheck()){
		
		
		$reqfuncdata 		= array();			//Результат функций-обработчиков
		$CUR_KBRD 			= $reg_usr_kbrd;	//Клавиатура главного меню
		
		
		
		if($text != 'CANCEL') {//Упростим структуру, теперь из любого меню, выбрав 'CANCEL', мы вернёмся в главное меню
			
			/*Дабы упорядочить "общение" с телеграммом, оформи результаты функций-обработчиков следующи образом: 
					return array(
									'txt'				=> 'text for answer',
									'kbrd_mrkp' 		=> 'some keyboard list',
									'output_disabled'	=> 'if true, message doesnt be to send',
									'photo_arr'			=> array('photo_type'=>'real OR id', 0=>'array of photos path OR tg photo id'),
									'txt_arr'			=> array(0=>'caption for each elem in photo_arr')
								)
			*/
			$reqfuncdata = ($tgreq -> ReqHand())['func_res'];//Запускаем обработчик, и получаем результат функции-обработчика| результат функций-обработчиков оборачивается в ['func_res'] 
			
			if($reqfuncdata['output_disabled'] == true) exit;//Если мы решили отключить вывод - "отключаем вывод" 
			
			if(!empty($reqfuncdata['kbrd_mrkp'])) $CUR_KBRD = $reqfuncdata['kbrd_mrkp'];//Клавиатура
	
			if(empty($reqfuncdata['photo_arr'])){
				if(!empty($reqfuncdata['txt']))$tapi->sendMessage([ 'chat_id' => $chat_id, 'parse_mode'=> 'HTML',  'text' => "".$reqfuncdata['txt'], 'reply_markup' =>  $CUR_KBRD ]);//Или текст, или фото
			}else{
				// ['photo_arr']=[0=>id/name, 1 => id/name]; //['txt_arr']=[0=>txt, 1=>txt];
				
				foreach($reqfuncdata['photo_arr'] as $k=>$v) if(empty($reqfuncdata['txt_arr'][$k]))$reqfuncdata['txt_arr'][$k]="\n";//Заглушка для избежания ошибки впихивания пустого значения в описани, и соответсвенно - минимизации кода
				
				if($reqfuncdata['photo_type']=='real'){//Проверяем значение - путь или id
					foreach($reqfuncdata['photo_arr'] as $k=>$v){//Парсим создаём ресурс, добавляем описание, отправляем
						$path		= $v;
						unset($v);
						$v 			= InputFile::create($path, $usrid.date("-y.m.d-h_i_s-").'last_im.png');
						$tapi->sendPhoto(['chat_id' => $chat_id, 'photo' => $v, 'caption' => "".$reqfuncdata['txt_arr'][$k],'parse_mode'=>"HTML"]);
					}
				}else{
					foreach($reqfuncdata['photo_arr'] as $k=>$v){//Парсим id, добавляем описание, отправляем
						$tapi->sendPhoto(['chat_id' => $chat_id, 'photo' => $v, 'caption' => "".$reqfuncdata['txt_arr'][$k],'parse_mode'=>"HTML"]);
					}
				}
				
			}
		}else{
			
			$tgreq->ReqDel();//Удаляем запрос
			$tapi->sendMessage([ 'chat_id' => $chat_id, 'parse_mode'=> 'HTML',  'text' => "Запрос отменён", 'reply_markup' =>  $CUR_KBRD ]);
		}
		exit();
	}
	//Вы можете упростить, или урезать, моё решение, но постарайиесь локализировать "хаос"
	
	
	//Обработку запросов на запрос закончили, разработаем главное меню.
	if(!empty($text)){
		switch($text){
				case '/start'://Стартовое сообщение
					$tapi->sendMessage([ 'chat_id' => $chat_id, 'reply_markup'=>$CUR_KBRD_0,'parse_mode'=> 'HTML','text' => "Добро пожаловать в лабиринт..."]);
					break;
				case '/GLI':
					$tapi->sendMessage([ 'chat_id' => $chat_id, 'reply_markup'=>$CUR_KBRD_0,'parse_mode'=> 'HTML','text' => "Вы только что сгенерировали лучшую жизнь, но её потеряли по пути от DNS /:"]);
					break;
				case 'CALC':
					$tapi->sendMessage([ 'chat_id' => $chat_id,'reply_markup'=>$CUR_KBRD_0 ,'parse_mode'=> 'HTML','text' => "Таблица времени в секундах"]);
					$tapi->sendMessage([ 'chat_id' => $chat_id,'reply_markup'=>$CUR_KBRD_0 ,'parse_mode'=> 'HTML','text' => "<code>1 час - 3600 \n2 часа - 7200\n----------------\n30 мин - 1800 \n20 мин - 1200 \n10 мин - 600\n----------------\n5 мин - 300 \n2 мин - 60 \n1 мин - 60</code>"]);
					break;
				case '/help':
					$tapi->sendMessage([ 'chat_id' => $chat_id, 'reply_markup'=>$CUR_KBRD_0,'parse_mode'=> 'HTML','text' => "Если не знаете как создать тг бота \xF0\x9F\x91\x87 \n https://www.google.com/search?q=%D1%81%D0%BE%D0%B7%D0%B4%D0%B0%D0%BD%D0%B8%D0%B5+%D0%B1%D0%BE%D1%82%D0%B0+%D1%82%D0%B3+habr&oq=%D1%81%D0%BE%D0%B7%D0%B4%D0%B0&sourceid=chrome&ie=UTF-8" ]);
					break;
				case 'SETTING'://Переход в меню
				
					$sett_reply					="REQ_CREATE_ERROR foward this message to admin";//Сообщение на случай ошибки	
					$rr 						= $tgreq->ReqCreate('SETTING');//Создание "запроса на запрос" aka переход в меню
					if(!$rr['error']) {
						//Если прошло без ошибок, сообщаем о переходе и ставим нужную клавиатуру
						$sett_reply 			= "Вы в настройках";
						$CUR_KBRD_0				= $def_lokm['Setting'];
					}
					$tapi->sendMessage([ 'chat_id' => $chat_id,'parse_mode'=> 'HTML','text' => $sett_reply, 'reply_markup'=>$CUR_KBRD_0]);//отсылаем информацию юзеру
					break;
				case '/version':
					
					$tapi->sendMessage([ 'chat_id' => $chat_id, 'parse_mode'=> 'HTML','text' => 'TgLongReq_v1.0.7', 'reply_markup'=>$CUR_KBRD_0]);
					$WasCheck=true;
					break;
				case '/linse':$linse_reply = "Неплохая кнопка, да?! \xF0\x9F\x91\x91\xF0\x9F\x98\x8E\xF0\x9F\x91\x91";$tapi->sendMessage([ 'chat_id' => $chat_id, 'parse_mode'=> 'HTML','text' => ''.trim($linse_reply), 'reply_markup'=>$CUR_KBRD_0]);break;
				default:
					$tapi->sendMessage([ 'chat_id' => $chat_id, 'text' =>"Вы не ввели ниодного параметра, для отображения предидущей генерации виберите /GLI", 'reply_markup'=>$CUR_KBRD_0]);											
					break;
			}
	}

?>