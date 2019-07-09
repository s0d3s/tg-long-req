<?php

	
	include_once('varfunc.php');
	
	
	
	use Telegram\Bot\Api; 												//Подключаем сам тг-бот-сдк
	use Telegram\Bot\Keyboard\Keyboard;									//Подкючаем класс для клавиатур
	
	use TgLongReq\TgLongReq;											//Класс для работы с меню, собственной персоной
		
	$BOT_THIS_NAME			=		"IamBOT";							//адресс бота в тг
	$BOT_THIS_TOKEN 		=		'xxxx:yyyy';			//токен выднный @BOTFATHER-ом
															//!!! НЕ ЗАБУДЬТЕ ЗАМЕНИТЬ
	
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
		
		
		/*
		!
		!				В ДАННОМ БЛОКЕ ПРЕДОСТАВЛЕН УКОРОЧЕННАЯ ВЕРСИЯ РЕАЛИЗАЦИИ
		!				(рекомендую ознакомиться с полной версией[https://github.com/s0d3s/tg-long-req/tree/master/exmp/def_menu], или возможно сменить подход к отправке данных)
		!
		*/
		
		$reqfuncdata 		= array();					//Результат функций-обработчиков
		$CUR_KBRD 			= $def_lokm['MainMenu'];	//Клавиатура главного меню
		
		
		
		if($text != 'CANCEL') {//Упростим структуру, теперь из любого меню, выбрав 'CANCEL', мы вернёмся в главное меню
			
			$reqfuncdata = ($tgreq -> ReqHand())['func_res'];//Запускаем обработчик, и получаем результат функции-обработчика| результат функций-обработчиков оборачивается в ['func_res'] 
			
			if(!empty($reqfuncdata['kbrd_mrkp'])) $CUR_KBRD = $reqfuncdata['kbrd_mrkp'];//Клавиатура
	
			if(!empty($reqfuncdata['txt']))$tapi->sendMessage([ 'chat_id' => $chat_id, 'parse_mode'=> 'HTML',  'text' => "".$reqfuncdata['txt'], 'reply_markup' =>  $CUR_KBRD ]);//Или текст, или фото
			
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
				case 'CALC':
					$tapi->sendMessage([ 'chat_id' => $chat_id,'reply_markup'=>$CUR_KBRD_0 ,'parse_mode'=> 'HTML','text' => "Таблица времени в секундах"]);
					$tapi->sendMessage([ 'chat_id' => $chat_id,'reply_markup'=>$CUR_KBRD_0 ,'parse_mode'=> 'HTML','text' => "<code>1 час - 3600 \n2 часа - 7200\n----------------\n30 мин - 1800 \n20 мин - 1200 \n10 мин - 600\n----------------\n5 мин - 300 \n2 мин - 60 \n1 мин - 60</code>"]);
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
			}
	}

?>