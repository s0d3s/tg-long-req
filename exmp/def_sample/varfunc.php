<?php
	$DIR = $_SERVER['DOCUMENT_ROOT'];
	include_once("$DIR/vendor/autoload.php"); 

	$BOT_FUNC_ASSO = [
						'SETTING'=>'menu_seting', 'SETTING_ac'=>'menu_seting'
					];
	
	
	//Если нужно будет отправлять данные отсюда
	use Telegram\Bot\Api;
	//	Для использования клавиатур(по сути это двумерный масив), из telegram-bot-sdk
	use Telegram\Bot\Keyboard\Keyboard;
	
	//листинг клавиатур
	$reg_usr_kbrd = Keyboard::make()
				->row(
					Keyboard::inlineButton(['text' => 'CALC', 'resize_keyboard' => true, 'one_time_keyboard' => true ])				
				)
				->row(
					Keyboard::inlineButton(['text' => 'SETTING', 'resize_keyboard' => true, 'one_time_keyboard' => true ])					
				);
	$reg_setting_kbrd = Keyboard::make()
				->row(					
					Keyboard::inlineButton(['text' => 'Add channel', 'resize_keyboard' => true, 'one_time_keyboard' => true ])
				)
				->row(
					Keyboard::inlineButton(['text' => 'RETURN', 'resize_keyboard' => true, 'one_time_keyboard' => true ])
				);
	$reg_setting_add_channel_kbrd = Keyboard::make()
				->row(
					Keyboard::inlineButton(['text' => 'RETURN', 'resize_keyboard' => true, 'one_time_keyboard' => true ])
				);
				
	//########################################
	//Создадим переменную для быстрого доступа
	$def_list_of_keyborad_markup = [
									"MainMenu"=> $reg_usr_kbrd,
									"Setting"=> $reg_setting_kbrd,
									"Channel"=> $reg_setting_add_channel_kbrd
								];
	//Тут же синоним						
	$def_lokm					 = $def_list_of_keyborad_markup;
	//########################################
	
	/*			Я вижу два логичных варианта обработки запроса на запрос
	*
		а)	Обработка "каждого" в отдельной функции
		б)	Обработка "всех" в одном агрегаторе, создавая  события(запрос на запрос), ведущие на одну функцию,
			('some_req0'=>'req_hand', 'some_req1'=>'req_hand', 'some_req...'=>'req_hand')
			и последующая вариация действий в зависимости от ключа запроса
	*/
	function menu_seting($tg_res, $long_req, $tab_key){//Главный обработчик
		global $def_lokm;//Импортируем клавиатуры
		
		$t_answ				= $tg_res["message"]["text"];											//Получаем ответ юзера
		$rtrn_txt 			= "";																	//Текст возврата
		$rtrn_kbrd			= $def_lokm['Setting'];													//Стандартная клавиатура возврата
		
		switch($tab_key){																			//Проверяем по ключам в каком меню мы находимся
			case 'SETTING':
				switch($t_answ){																	//Когда мы уже знаем наше местонахождения, можем можем обрабатывать команды
					case 'Add channel':
						$rs = $long_req -> ReqCreate('SETTING_ac');									//Создание запроса на запрос
						if(!$rs['error']){
							$rtrn_txt = "Channel manager";											//Текст возврата
							$rtrn_kbrd= $def_lokm['Channel'];										//Клавиатура возврата
						}else{							
							$rtrn_txt = "FORWARD THIS MESSAGE TO ADMIN, ERROR:".$rs['err_discript'];//Сообщаем об ошибке
							$rtrn_kbrd= $def_lokm['MainMenu'];										//Клавиатура главного меню
						}
						break;
					case 'RETURN':																	//Возврат в предидущие меню
						$rtrn_txt = "You are in MainMenu";								
						$rtrn_kbrd= $def_lokm['MainMenu'];											//Клавиатура нужного меню
						break;
				}
				break;
			//Дальнейшие секции идут по полной анологии к только что предоставленной и коментирование их излишне, если вы уже поняли структуру можете сразу перейти к возврату функции-обработчика	
			case 'SETTING_ac':
				switch($t_answ){					
					case 'RETURN':
						
						$rs = $long_req -> ReqCreate('SETTING');
						if(!$rs['error']){
							$rtrn_txt = "You was rtrn to SETTING";
							$rtrn_kbrd= $def_lokm['Setting'];
						}else{							
							$rtrn_txt = "FORWARD THIS MESSAGE TO ADMIN, ERROR:".$rs['err_discript'];
							$rtrn_kbrd= $def_lokm['MainMenu'];
						}
						break;
				}
				break;
		}
		
		return array('txt'=>$rtrn_txt, 'kbrd_mrkp'=>$rtrn_kbrd);									//Заполняем поля из которых и вскоре сделаем вывод юзеру
	}
?>