<?php
	$DIR = $_SERVER['DOCUMENT_ROOT'];
	include_once("$DIR/vendor/autoload.php"); 

	$BOT_FUNC_ASSO = [
						'SETTING'=>'menu_seting', 'SETTING_ac'=>'menu_seting', 'SETTING_ms'=>'menu_seting'
					];
	
	
	//Если нужно будет отправлять данные отсюда
	use Telegram\Bot\Api;
	//	Для использования клавиатур(по сути это двумерный масив), из telegram-bot-sdk
	use Telegram\Bot\Keyboard\Keyboard;
	
	//листинг клавиатур
	$reg_usr_kbrd = Keyboard::make()
				->row(//								Generate last image
					Keyboard::inlineButton(['text' => '/GLI', 'resize_keyboard' => true, 'one_time_keyboard' => true ]),
					Keyboard::inlineButton(['text' => 'CALC', 'resize_keyboard' => true, 'one_time_keyboard' => true ])				
				)
				->row(
					Keyboard::inlineButton(['text' => '/version', 'resize_keyboard' => true, 'one_time_keyboard' => true ]),
					Keyboard::inlineButton(['text' => 'SETTING', 'resize_keyboard' => true, 'one_time_keyboard' => true ]),
					Keyboard::inlineButton(['text' => '/help', 'resize_keyboard' => true, 'one_time_keyboard' => true ])
				);
	$reg_setting_kbrd = Keyboard::make()
				->row(
					Keyboard::inlineButton(['text' => 'Add channel', 'resize_keyboard' => true, 'one_time_keyboard' => true ]),					
					Keyboard::inlineButton(['text' => 'Manage setting', 'resize_keyboard' => true, 'one_time_keyboard' => true ]),
					Keyboard::inlineButton(['text' => '/setting_help', 'resize_keyboard' => true, 'one_time_keyboard' => true ])
				)
				->row(
					Keyboard::inlineButton(['text' => 'RETURN', 'resize_keyboard' => true, 'one_time_keyboard' => true ])
				);
	$reg_setting_add_channel_kbrd = Keyboard::make()
				->row(
					Keyboard::inlineButton(['text' => 'Show channels', 'resize_keyboard' => true, 'one_time_keyboard' => true ]),
					Keyboard::inlineButton(['text' => 'Add channels', 'resize_keyboard' => true, 'one_time_keyboard' => true ]),
					Keyboard::inlineButton(['text' => 'Verify channels', 'resize_keyboard' => true, 'one_time_keyboard' => true ]),
					Keyboard::inlineButton(['text' => '/channel_help', 'resize_keyboard' => true, 'one_time_keyboard' => true ])
				)
				->row(
					Keyboard::inlineButton(['text' => 'RETURN', 'resize_keyboard' => true, 'one_time_keyboard' => true ])
				);
	$reg_setting_manage_kbrd = Keyboard::make()
				->row(
					Keyboard::inlineButton(['text' => 'Show setting', 'resize_keyboard' => true, 'one_time_keyboard' => true ]),
					Keyboard::inlineButton(['text' => 'Set gen_info', 'resize_keyboard' => true, 'one_time_keyboard' => true ]),
					Keyboard::inlineButton(['text' => 'Set usr_info', 'resize_keyboard' => true, 'one_time_keyboard' => true ]),
					Keyboard::inlineButton(['text' => '/manage_help', 'resize_keyboard' => true, 'one_time_keyboard' => true ])
				)
				->row(
					Keyboard::inlineButton(['text' => 'RETURN', 'resize_keyboard' => true, 'one_time_keyboard' => true ])
				);
				
	//########################################
	//Создадим переменную для быстрого доступа
	$def_list_of_keyborad_markup = [
									"MainMenu"=> $reg_usr_kbrd,
									"Setting"=> $reg_setting_kbrd,
									"Channel"=> $reg_setting_add_channel_kbrd,
									"Manage"=> $reg_setting_manage_kbrd
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
					case 'Manage setting':
						$rs = $long_req -> ReqCreate('SETTING_ms');
						if(!$rs['error']){
							$rtrn_txt = "Some setting manage";
							$rtrn_kbrd= $def_lokm['Manage'];
						}else{							
							$rtrn_txt = "FORWARD THIS MESSAGE TO ADMIN, ERROR:".$rs['err_discript'];
							$rtrn_kbrd= $def_lokm['MainMenu'];
						}
						break;
					case '/setting_help':
						$rs = $long_req -> ReqCreate($tab_key);
						if(!$rs['error']){
							$rtrn_txt = "This info was helpful for you";
						}else{							
							$rtrn_txt = "FORWARD THIS MESSAGE TO ADMIN, ERROR:".$rs['err_discript'];
							$rtrn_kbrd= $def_lokm['MainMenu'];
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
					case 'Show channels':
						$rs = $long_req -> ReqCreate($tab_key);
						if(!$rs['error']){
							$rtrn_txt = "This is your channals";
							$rtrn_kbrd= $def_lokm['Channel'];
						}else{							
							$rtrn_txt = "FORWARD THIS MESSAGE TO ADMIN, ERROR:".$rs['err_discript'];
							$rtrn_kbrd= $def_lokm['MainMenu'];
						}
						break;
					case 'Add channels':
						$rs = $long_req -> ReqCreate($tab_key);
						if(!$rs['error']){
							$rtrn_txt = "Maybe you have been added new channel";
							$rtrn_kbrd= $def_lokm['Channel'];
						}else{							
							$rtrn_txt = "FORWARD THIS MESSAGE TO ADMIN, ERROR:".$rs['err_discript'];
							$rtrn_kbrd= $def_lokm['MainMenu'];
						}
						break;
					case 'Verify channels':
						$rs = $long_req -> ReqCreate($tab_key);
						if(!$rs['error']){
							$rtrn_txt = "All alright";
							$rtrn_kbrd= $def_lokm['Channel'];
						}else{							
							$rtrn_txt = "FORWARD THIS MESSAGE TO ADMIN, ERROR:".$rs['err_discript'];
							$rtrn_kbrd= $def_lokm['MainMenu'];
						}
						break;
					case '/channel_help':
						$rs = $long_req -> ReqCreate($tab_key);
						if(!$rs['error']){
							$rtrn_txt = "It was help you, with your chann problem";
							$rtrn_kbrd= $def_lokm['Channel'];
						}else{							
							$rtrn_txt = "FORWARD THIS MESSAGE TO ADMIN, ERROR:".$rs['err_discript'];
							$rtrn_kbrd= $def_lokm['MainMenu'];
						}
						break;
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
				
			case 'SETTING_ms':
				switch($t_answ){
					case 'Show setting':
						$rs = $long_req -> ReqCreate($tab_key);
						if(!$rs['error']){
							$rtrn_txt = "Some setting";
							$rtrn_kbrd= $def_lokm['Manage'];
						}else{							
							$rtrn_txt = "FORWARD THIS MESSAGE TO ADMIN, ERROR:".$rs['err_discript'];
							$rtrn_kbrd= $def_lokm['MainMenu'];
						}
						break;
					case 'Set gen_info':
						$rs = $long_req -> ReqCreate($tab_key);
						if(!$rs['error']){
							$rtrn_txt = "Gen_info was set";
							$rtrn_kbrd= $def_lokm['Manage'];
						}else{							
							$rtrn_txt = "FORWARD THIS MESSAGE TO ADMIN, ERROR:".$rs['err_discript'];
							$rtrn_kbrd= $def_lokm['MainMenu'];
						}
						break;
					case 'Set usr_info':
						$rs = $long_req -> ReqCreate($tab_key);
						if(!$rs['error']){
							$rtrn_txt = "Usr_info was set";
							$rtrn_kbrd= $def_lokm['Manage'];
						}else{							
							$rtrn_txt = "FORWARD THIS MESSAGE TO ADMIN, ERROR:".$rs['err_discript'];
							$rtrn_kbrd= $def_lokm['MainMenu'];
						}
						break;
					case '/manage_help':
						$rs = $long_req -> ReqCreate($tab_key);
						if(!$rs['error']){
							$rtrn_txt = "It was help you, with your manage problem";
							$rtrn_kbrd= $def_lokm['Manage'];
						}else{							
							$rtrn_txt = "FORWARD THIS MESSAGE TO ADMIN, ERROR:".$rs['err_discript'];
							$rtrn_kbrd= $def_lokm['MainMenu'];
						}
						break;
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