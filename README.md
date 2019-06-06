
# TG LONG REQUEST
## What is this?
  
This is a small help-oriented class for creating tg bots. It helps with the creation and navigation in the menu telegram bot

---
Это небольшой класс для телеграм ботов. Он помогает в создании меню и дальнейшей навигации в нём.
## Abstract exemple
![](http://g.recordit.co/rqonFOdR4t.gif)
## INSTALL
**via composer**

(add this to section "require" in your composer.json)

    {
      "require" : {  
    	"s0d3s/tg-long-req": ">=1"   
      }
    }
   and 
   
    include_once('path/to/autoload.php');
	use TgLongReq\TgLongReq;

**manual**

Copy the "TgLongReq.php" to the project dir and:

    include_once('TgLongReq.php');

## Simple use

1. **Create a TgLongReq obj**
>$tg_req = TgLongReq
>(
> 'user_id', 
> 'req_func list' 
> */*optinal*/* ,
> 'user_request_dir', 
> tg_api_key, 
> tg_api_result
> );
> 
|Var|Type|Caption|
|--|--|--|
|$user_id| STRING | Telegram user id |
|$ReqFunc| ARRAY | Requests and functions association table |
|$usr_req_dir|STR| Path to general request dir **|
|$tg_api_key| SOMETH/* | irazasyed/telegram-bot-sdk Api() object* |
|$tg_api_result| SOMETH/* |Api()->getWebhookUpdates() |

*It could be something else, or be null.
**Temporary requests from users will be stored in this folder.

---
2. **Create request**

> $tg_req->ReqCreate('req_name'); *

*This parameter depends only on you (this name should be in the association table)

---
3.  **Save and restore temprorary data**

 - **saving**
For store (temporary) data, you can use "SaveToTemp", the function accepts any data types and saves it as json array
> $tg_req->SaveToTemp(\$some_data);
- **restore**
But, the data is restored from file, only as an array.
>$some_array = array();
>$some_array = \$tg_req->GetFromTemp(\$hold_it==false);*

\*  If needed, you can leave a temporary file until the next useб for this pass to function "true"(bool)

---
4. **Check the existence of the request**

>  $tg_req->ReqCheck();
>  //return true if exists, else false
---
5. **Delete user request**

> $tg_req->ReqDel();
---
6. **Handling request**

> $tg_req->ReqHand();
> //This method will call the corresponding function from the association table.
---
 7. **NOTES**
 - Association table is array('req_name'=>'func_name')
 - Functions specified in the table may not process passed parameters 

> I will add an example of use soon...

