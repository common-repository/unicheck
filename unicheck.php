<?php
/*
Plugin Name: Unicheck
Author URI: alex3dev@gmail.com
Description: Отправка текста на проверку уникальности, грамматики и заспамленности. Формирование счета к оплате за статью. 
Отправка отчета с результатами проверки и суммы к оплате на почту
Version: 1.0.0
Author: alex3dev
*/


/*  Copyright 2016  alex3dev  (email : alex3dev@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//создаем поле uid при активации плагина, в которое будет записываться результат проверки и настройки плагина по умолчанию
function unchck_activate() {
global $wpdb;
$wpdb->get_var("ALTER TABLE $wpdb->posts ADD `uid` TEXT NOT NULL;");
$wpdb->get_var("ALTER TABLE $wpdb->posts ADD `rez` BOOLEAN NOT NULL;");

//настройки плагина по умолчанию при его активации
add_option( 'unchck_stavka_text', '50'); 
add_option( 'unchck_water_text', '90' );
add_option( 'unchck_znaktype_text', '100' );
add_option( 'unchck_fixstavka_text', '200' );
add_option( 'unchck_normaznak_text', '2000' );
add_option( 'unchck_unique_text', '80' );
add_option( 'unchck_sovpad_text', '25' );
add_option( 'unchck_spam_text', '65' );
add_option( 'unchck_paytype_text', 'znakpay' );
add_option( 'unchck_spacepaytype_text', 'bez_probelov_text' );
add_option( 'unchck_bill_text', 'bill_yes_text' );
add_option( 'unchck_except_author_text', '' );
}

register_activation_hook( __FILE__, 'unchck_activate' );


//страница настроек плагина
function unchck_SettingsPage()
{
?>
<div class="unchck_wrap">

<h2>Настройки плагина Unicheсk - проверки уникальности текста и формирования счета к оплате</h2>
 <form method="post" action="options.php" id="unchck_unicheckset">
 <?php 
settings_fields('unicheck-group'); 

 
 ?>
 
 <div class="unchck_oplata" id="unchck_oplata">
  <table class="form-table">

  <tr valign="top">
   <th scope="row"><a href="https://text.ru/alexsan/api-check" target="_blank">Ваш USER KEY:</a></th>
   <td><input type="text" name="unchck_userkey_text" value="<?php echo get_option('unchck_userkey_text'); ?>"/></td>
 </tr>
 

    <tr valign="top">
   <th scope="row">Формирование счета к оплате в дополнение к отчету с результатами проверки</th>
    <td>
<select name="unchck_bill_text" id="unchck_bill_text">
<option value="bill_yes_text" <?php if (get_option('unchck_bill_text')=="bill_yes_text") {echo 'selected';}; ?>>да</option>
<option value="bill_no_text" <?php if (get_option('unchck_bill_text')=="bill_no_text") {echo 'selected';}; ?>>нет</option>
</select> </td>
  </tr>
  

  
  <tr valign="top">
   <th scope="row">Учитывать ли пробелы при рассчете знаков</th>
    <td>
<select name="unchck_spacepaytype_text">
<option value="bez_probelov_text" <?php if (get_option('unchck_spacepaytype_text')=="bez_probelov_text") {echo 'selected';}; ?>>без пробелов</option>
<option value="s_probelami_text" <?php if (get_option('unchck_spacepaytype_text')=="s_probelami_text") {echo 'selected';}; ?>>с пробелами</option>
</select> </td>
  </tr>



<tr valign="top" id="unchck_h1" <?php if (get_option('unchck_bill_text')=="bill_no_text") {echo 'hidden';}; ?>>
<th scope="row">Вариант оплаты</th>
<td> 
<select name="unchck_paytype_text" id="unchck_paytype_text">
<option value="znakpay"<?php if (get_option('unchck_paytype_text')=="znakpay") {echo 'selected';}; ?>>оплата за 1000 знаков</option>
<option value="fixpay" <?php if (get_option('unchck_paytype_text')=="fixpay") {echo 'selected';}; ?>>фиксированная оплата за статью</option>
<option value="fixbonuspay" <?php if (get_option('unchck_paytype_text')=="fixbonuspay") {echo 'selected';}; ?>>фиксированная оплата с бонусной частью за превышение нормы объема</option>
</select> </td>
</tr>

   <tr valign="top"  id="unchck_h2" <?php if (get_option('unchck_bill_text')=="bill_no_text") {echo 'hidden';}; ?>>
   <th scope="row">Цена за 1000 знаков</th>
   <td><input type="text" name="unchck_stavka_text" value="<?php echo get_option('unchck_stavka_text'); ?>" /></td>
</tr>

  <tr valign="top"  id="unchck_h3" <?php if (get_option('unchck_bill_text')=="bill_no_text") {echo 'hidden';}; ?>>
   <th scope="row">фиксированная часть оплаты:</th>
   <td><input type="text" name="unchck_fixstavka_text" value="<?php echo get_option('unchck_fixstavka_text'); ?>" /></td>
  </tr>
  
    <tr valign="top"  id="unchck_h4" <?php if (get_option('unchck_bill_text')=="bill_no_text") {echo 'hidden';}; ?>>
   <th scope="row">объем текста в которую включена фиксоплата, выше которой идет доплата</th>
   <td><input type="text" name="unchck_normaznak_text" value="<?php echo get_option('unchck_normaznak_text'); ?>"/></td>
  </tr>

   <tr valign="top">
   <th scope="row">совпадение по одному источнику не выше </th>
   <td><input type="text" name="unchck_sovpad_text" value="<?php echo get_option('unchck_sovpad_text'); ?>" />%</td>
  </tr>
  
    <tr valign="top">
   <th scope="row">спамость текста не выше</th>
   <td><input type="text" name="unchck_spam_text" value="<?php echo get_option('unchck_spam_text'); ?>" />%</td>
  </tr>
   
  
      <tr valign="top">
   <th scope="row">уникальность текста не ниже</th>
   <td><input type="text" name="unchck_unique_text" value="<?php echo get_option('unchck_unique_text'); ?>" />%</td>
  </tr>
  
      <tr valign="top">
   <th scope="row">вода в тексте не более</th>
   <td><input type="text" name="unchck_water_text" value="<?php echo get_option('unchck_water_text'); ?>" />%</td>
  </tr>
  
      <tr valign="top">
   <th scope="row">логин пользователя, тексты которого не отправляются на проверку (предполагается, что можно исключить администратора)</th>
   <td><input type="text" name="unchck_except_author_text" value="<?php echo get_option('unchck_except_author_text'); ?>" /></td>
  </tr>
 
  </table>
 
  <input type="hidden" name="unchck_action" value="update" />
  <input type="hidden" name="unchck_page_options" value="unchck_userkey_text,unchck_water_text,unchck_spam_text,unchck_sovpad_text,unchck_unique_text,unchck_znaktype_text,unchck_fixstavka_text,unchck_normaznak_text,unchck_stavka_text,unchck_paytype_text,unchck_spacepaytype_text,unchck_except_author_text" />
 
  <p class="submit">
   <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
  </p>


 </form>


<p><strong>Предложения по улучшению и замечания об ошибках отправляйте на: <a href="mailto:alex3dev@gmail.com">alex3dev@gmail.com</a></strong></p>
   </div>
</div>
 
 
 





 <script type="text/javascript">
var unchck_paytype_text = document.querySelector('#unchck_paytype_text'),
		unchck_bill_text = document.querySelector('#unchck_bill_text'),
		unchck_oplata = document.querySelectorAll('.unchck_oplata input');
function option () {
	switch (unchck_paytype_text.value) {
  	case 'znakpay':
	  unchck_oplata[0].disabled = '';
	  unchck_oplata[1].disabled = '';
      unchck_oplata[2].disabled = 'disabled';
	  unchck_oplata[3].disabled = 'disabled';
    	break;
    case 'fixpay':
	  unchck_oplata[0].disabled = '';
	  unchck_oplata[1].disabled = 'disabled';
      unchck_oplata[2].disabled = '';
	  unchck_oplata[3].disabled = 'disabled';
    	break;
     case 'fixbonuspay':
	  unchck_oplata[0].disabled = '';
 	  unchck_oplata[1].disabled = '';
      unchck_oplata[2].disabled = '';
	  unchck_oplata[3].disabled = '';
     	break;
  }
}
option();
unchck_paytype_text.addEventListener('change', function () {option();});
unchck_bill_text.addEventListener('change', function () {
	switch (unchck_bill_text.value) {
  	case 'bill_yes_text':
       unchck_h1.hidden = '';
	   unchck_h2.hidden = '';
       unchck_h3.hidden = '';
	   unchck_h4.hidden = '';
      option();
    	break;
    case 'bill_no_text':
         unchck_h1.hidden = 'hidden';
    	 unchck_h2.hidden = 'hidden';
		 unchck_h3.hidden = 'hidden';
		 unchck_h4.hidden = 'hidden';
    	break;
  }
});
</script>

<?php 

} 

//добавляем настройки плагина в меню настроек
function unchck_CreatePluginMenu()
{   
    if (function_exists('add_options_page'))
    {        
        add_options_page('Настройки плагина Unicheck', 'Настройки плагина Unicheck', 'manage_options', 'sendPostPublishedInfo', 'unchck_SettingsPage');
		add_action( 'admin_init', 'unchck_register_plugin_settings' );
    }
}
 
//передаем внесенные значения настроек плагина из формы в базу
function unchck_register_plugin_settings() { 
register_setting( 'unicheck-group', 'unchck_userkey_text' );
register_setting( 'unicheck-group', 'unchck_stavka_text' );
register_setting( 'unicheck-group', 'unchck_water_text' );
register_setting( 'unicheck-group', 'unchck_znaktype_text' );
register_setting( 'unicheck-group', 'unchck_fixstavka_text' );
register_setting( 'unicheck-group', 'unchck_normaznak_text' );
register_setting( 'unicheck-group', 'unchck_unique_text' );
register_setting( 'unicheck-group', 'unchck_sovpad_text' );
register_setting( 'unicheck-group', 'unchck_spam_text' );
register_setting( 'unicheck-group', 'unchck_paytype_text' );
register_setting( 'unicheck-group', 'unchck_spacepaytype_text' );
register_setting( 'unicheck-group', 'unchck_bill_text' );
register_setting( 'unicheck-group', 'unchck_except_author_text' );
}
 
add_action('admin_menu', 'unchck_CreatePluginMenu');


	/*
		2 функции для взаимодействия с API Text.ru посредством POST-запросов.
		Ответы с сервера приходят в формате JSON. 
	*/

	//-----------------------------------------------------------------------
	
	/**
	 * Добавление текста на проверку
	 *
	 * @param string $text - проверяемый текст
	 * @param string $user_key - пользовательский ключ
	 * @param string $exceptdomain - исключаемые домены
	 *
	 * @return string $text_uid - uid добавленного текста 
	 * @return int $error_code - код ошибки
	 * @return string $error_desc - описание ошибки
	 */

	function unchck_addPost($text, $userkey)
	{
		$postQuery = array();
		$postQuery['text'] = $text;
		$postQuery['userkey'] = $userkey;
		// домены разделяются пробелами либо запятыми. Данный параметр является необязательным.
		$postQuery['exceptdomain'] = $_SERVER['SERVER_NAME'];
		// Раскомментируйте следующую строку, если вы хотите, чтобы результаты проверки текста были по-умолчанию доступны всем пользователям
		$postQuery['visible'] = "vis_on";
		// Раскомментируйте следующую строку, если вы не хотите сохранять результаты проверки текста в своём архиве проверок
		//$postQuery['copying'] = "noadd";
		//В следующий файл будет 3 раза посланы результаты проверки от text.ru. После 3 го раза в файле getrez будет сформирован отчет и отправлен на почту
		$plugins_url = plugins_url();
		$postQuery['callback'] = 'http://'.$_SERVER['SERVER_NAME'].'/?unchck-mycallback=1';
		$postQuery = http_build_query($postQuery, '', '&');
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://api.text.ru/post');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postQuery);
		$json = curl_exec($ch);
		$errno = curl_errno($ch);

		// если произошла ошибка
		if (!$errno)
		{
			$resAdd = json_decode($json);
			if (isset($resAdd->text_uid))
			{
				$text_uid = $resAdd->text_uid;
				return $text_uid;	
			}
			else
			{
				$error_code = $resAdd->error_code;
				$error_desc = $resAdd->error_desc;
				
global $wpdb;
$to=get_option('admin_email');
$subject = 'Описание ошибки: '.$error_desc; 
$headers[] = 'Content-type: text/html; charset=utf-8'; // в виде массива
wp_mail($to, $subject, $text, $headers);
			}
		}
		else
		{
			$errmsg = curl_error($ch);
		}

		curl_close($ch);
}


//получаем uid при сохранении поста
function unchck_checktext(){
global $wpdb;
global $post;


$user_info = get_userdata($post->post_author);


/*если статья не является исправленной автором из исключения, тогда отправить на проверку
для избежания двойной проверки, например если администратор сайта вносит правки в статью,
либо если статья куплена на бирже, чтобы избежать повторную проверку.*/


$except_author_text=get_option('unchck_except_author_text');

if ($user_info->user_login !==$except_author_text) {

//получаем айди статьи
$postid = get_the_ID();
$post = get_post($postid );


//для того, чтобы повторная проверка сработала, нужно обнулить результат для текущего uid
$wpdb->get_var("update $wpdb->posts set `rez`=0 where `id`=$postid");
	

//отправляем текст на проверку
$userkey=get_option('unchck_userkey_text');
$text_uid=unchck_addPost($post->post_content, $userkey);

//заносим в базу uid по которому будет осуществляться поиск результатов проверки
$wpdb->get_var("update $wpdb->posts set `uid`="."'$text_uid'"." where `id`=$postid");
}

}
add_action('publish_post', 'unchck_checktext');





/**
	 * Получение статуса и результатов проверки текста в формате json
	 *
	 * @param string $text_uid - uid проверяемого текста
	 * @param string $user_key - пользовательский ключ
	 *
	 * @return float $unique - уникальность текста (в процентах)
	 * @return string $result_json - результат проверки текста в формате json
	 * @return int $error_code - код ошибки
	 * @return string $error_desc - описание ошибки
	 */
	function unchck_getResultPost($text_uid, $userkey, $arturl,$stavka, $stavkatype, $stavkavar, $title, $username, $fixstavka, $znakvar, $znaktype, $normaznak, $normaplagiat, $normaspam, $normauniс, $normavoda, $bill, $to)
	{
		$postQuery = array();
		$postQuery['uid'] = $text_uid;
		$postQuery['userkey'] = $userkey;
		// Раскомментируйте следующую строку, если вы хотите получить более детальную информацию в результатах проверки текста на уникальность
		$postQuery['jsonvisible'] = "detail";

		$postQuery = http_build_query($postQuery, '', '&');			 

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://api.text.ru/post');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postQuery);
		$json = curl_exec($ch);
		$errno = curl_errno($ch);

		if (!$errno)
		{
	
			$resCheck = json_decode($json);

			if (isset($resCheck->text_unique)
			and (isset($resCheck->spell_check))
			and (isset($resCheck->seo_check)))
			{
				//если уникальность, грамматика и сео-параметры проверены, то составляем отчет
				$text_unique = $resCheck->text_unique;
				$seo_check = json_decode($resCheck->seo_check);
				$spell_check = json_decode($resCheck->spell_check);
				$result_json = json_decode($resCheck->result_json);

				$znakovbp=$seo_check->count_chars_without_space;
				$znakovsp=$seo_check->count_chars_with_space;
				$water=json_decode($seo_check->water_percent);



				
//если в админке включена отправка формирования счета, то рассчитать сумму к оплате		
if ($bill=='bill_yes_text') {	

	
				//если оплата идет за 1000
				if ($stavkatype==$stavkavar[0]) {
					//если оплата идет за 1000 знаков без пробелов
					if ($znaktype==$znakvar[0]) {
						$itogo=ceil($znakovbp*$stavka/1000);
					}
					//если оплата идет за 1000 знаков c пробелами
					if ($znaktype==$znakvar[1]) {
						$itogo=ceil($znakovsp*$stavka/1000);
					}
				}
				//если оплата фиксированная за статью, без бонусов
				if ($stavkatype==$stavkavar[1]) {
					$itogo=$fixstavka;
				}
				
					//если оплата фиксированная за статью + бонусы за превышение
				if ($stavkatype==$stavkavar[2]) {
					//если учет бонуса идет с подсчетом знаков без пробелов
					if ($znaktype==$znakvar[0]) {
						//если число знаков выше нормы, то рассчитать бонус, иначе фиксированная оплата
						if ($znakovbp>=$normaznak) {
						$fixpart=($znakovbp-$normaznak)*$stavka/1000; //fixpart - сумма за оплату доп. текста
						$itogo=ceil($fixpart+$fixstavka);
						}
						else {$itogo=$fixstavka; $fixpart=0;}
					}
					//если учет бонуса идет с подсчетом знаков с пробелами
					if ($znaktype==$znakvar[1]) {
						//если число знаков выше нормы, то рассчитать бонус, иначе фиксированная оплата
						if ($znakovsp>=$normaznak) {
						$fixpart=($znakovsp-$normaznak)*$stavka/1000; //fixpart - сумма за оплату доп. текста
						$itogo=ceil($fixpart+$fixstavka);
						}
						else {$itogo=$fixstavka; $fixpart=0;}
					}
				}
				
				
}				


			
//проверка грамматики
foreach ($spell_check as $val) {
if (preg_match('/[А-Яа-я0-9a-zA-Z]/',$val->error_text)) {
$errors.=$val->error_text."<br />";
}
}


//просмотр совпадений плагиата с другими сайтами
$ubratpagiat=0;
foreach ($result_json->urls as $val) {
if ($val->plagiat>=$normaplagiat) {
$ubratpagiat=1;
$plagiat.='<a  target="_blank" href="'.$val->url.'">'.$val->plagiat."</a><br />";
}
}



//стандартные текстовые сообщения
$usertext="Автор текста: <strong> $username </strong><br>";
$plagiattext="<strong>Совпадение текста по 1 источнику должно быть менее 20%. Обнаружено превышение по следующим ссылкам:</strong><br /> $plagiat ";

if ($bill=='bill_yes_text') {
//формирование текста с результатами проверки
if ($stavkatype==$stavkavar[0]) {
$stavkatyptext="Тип оплаты: $stavkatype $znaktype <br>";
}
if($stavkatype==$stavkavar[1]) {
$stavkatyptext="Тип оплаты: $stavkatype <br>";
}
if($stavkatype==$stavkavar[2]) {
$stavkatyptext="Тип оплаты: $stavkatype в $normaznak знаков $znaktype <br>";
}

if($stavkatype==$stavkavar[0]) { 
$oplatatext="<strong>К оплате $itogo р.</strong> <br>
Ставка оплаты за 1000 знаков $znaktype: ".$stavka.'р.<br />';
}

if($stavkatype==$stavkavar[1]) { 
$oplatatext="<strong>К оплате $itogo р.</strong> <br>";
}


if($stavkatype==$stavkavar[2]) { 
$oplatatext="<strong>К оплате $itogo р.</strong> <br>
Из которых оплата фиксированной части: $fixstavka р.<br>
Оплата дополнительного текста $fixpart р.<br />".
"Ставка оплаты бонусных 1000 знаков: $znaktype: ".$stavka.'р.<br /><br />';
}
 
 if(($stavkatype==$stavkavar[2]) and ($fixpart==0)) { 
$oplatatext="<strong>К оплате $itogo р.</strong><br>
Бонусная часть отсуствует<br />";

}


$oplatatext2="<strong>После исправления замечаний</strong>. $oplatatext";
}


$podrobniylink='<a target="_blank" href="'."https://text.ru/alexsan/antiplagiat/$text_uid".'">смотреть результат проверки</a><br><br />';
$zagolovok=$arturl."<br />";
$spamtext="Заспамленность: ".$seo_check->spam_percent."%<br />";
$orfotext="Вероятно следующие слова содержат опечатки. Нужно проверить и может быть поправить: <br />".$errors."<br />";
$unictext= "Уникальность текста: ".$result_json->unique."%<br />";
$znakitext="В тексте $znakovbp знаков без пробелов<br>";
$znakitext2="В тексте $znakovsp знаков c пробелами<br>";
$watertext="Водянистость текста: $water %<br>";
$spamtext2=" - <strong>сервис выявил, что в тексте много спамного текста. Вероятно следует внести правки в текст, чтобы было менее $normaspam %, сейчас: ".$seo_check->spam_percent."%</strong><br /><br />";
$unictext2=" - <strong>сервис выявил, что текст не уникальный. Нужно не менее $normauniс %, сейчас: ".$result_json->unique."% </strong><br /><br />";
$watertext2=" - <strong>сервис выявил, что в тексте много воды. Нужно менее $normavoda %, сейчас: ".$water."% </strong><br /><br />";


//если уникальность, водянистость заспамленность в норме, то выслать в таком виде для принятия
if (($result_json->unique>=$normauniс) 
and ($seo_check->spam_percent<$normaspam) 
and ($water<=$normavoda) 
and ($ubratpagiat==0)) {
$text=$zagolovok.
$oplatatext.
$stavkatyptext.
$usertext.
$podrobniylink.
$unictext.
$spamtext.
$znakitext.
$znakitext2.
$watertext."<br /><br />".
$orfotext;
}

//если уникальность или заспамленность не в норме, тогда выслать вариант для доработки:
else {
if ($seo_check->spam_percent>=$normaspam) {$spamtext=$spamtext2;}
if ($result_json->unique<$normauniс) {$unictext=$unictext2;}
if ($water>$normavoda) {$watertext=$watertext2;}
if ($ubratpagiat==0) {$plagiattext='';}
$text=$zagolovok."<br />".
$podrobniylink.
$oplatatext2.
$usertext.
$unictext.
$plagiattext.
$stavkatyptext.
$spamtext.
$znakitext.
$znakitext2.
$watertext."<br /><br />".
$orfotext;
}


$subject = "$title (проверка уникальности текста)"; 
$headers[] = 'Content-type: text/html; charset=utf-8'; // в виде массива
wp_mail($to, $subject, $text, $headers);


//метка, что результат отправлен на почту
global $wpdb;

$wpdb->query("UPDATE $wpdb->posts SET rez = '1' WHERE uid='$text_uid'");


} 
			
			
			
			else
			//если не все проверено
			{
			
				$error_code = $resCheck->error_code;
				$error_desc = $resCheck->error_desc;


			}
		}
		else
		{
			$errmsg = curl_error($ch);
			
			
		}

		curl_close($ch);
	}

	

//отправка параметров в функцию получения результата проверки и формирования отчета и счета
function unchck_mycallback() {
  if ( $_SERVER['REQUEST_URI'] != '/?unchck-mycallback=1' or $_SERVER['REQUEST_METHOD'] != 'POST') return;
  else {

//найти запись, у которой есть уид, результатов проверки по которому еще нет
global $wpdb;
$wpdb->get_var("select $wpdb->posts.uid,  $wpdb->posts.rez,  $wpdb->posts.post_title,  
$wpdb->posts.post_author,  $wpdb->posts.guid, $wpdb->users.user_login from $wpdb->posts
INNER JOIN $wpdb->users ON $wpdb->posts.post_author = $wpdb->users.id
where $wpdb->posts.rez=0 and $wpdb->posts.uid!=0");

$result=$wpdb->last_result;

//перевернуть, так как стоит обратная сортировка
$result= array_reverse($result);




	//если еще не проверено, тогда проверить
	if (($result[0]->rez==0) and ($result[0]->uid!==null)) {
	

$text_uid=$result[0]->uid; //uid статьи
$username=$result[0]->user_login; //логин автора
$guid=$result[0]->guid; //адрес статьи
$title=$result[0]->post_title; //заголовок статьи
$arturl='<a  target="_blank"  href="'.$guid.'">'.$title.'</a>'; //ссылка на статью с анкором заголовка





//варианты оплаты
$stavkavar[0]='оплата за 1000 знаков'; 
$stavkavar[1]='фиксированная оплата за статью';
$stavkavar[2]='фиксированная оплата с бонусной частью за превышение нормы объема';

if (get_option('unchck_paytype_text')=='znakpay'){$stavkatype=$stavkavar[0];}
if (get_option('unchck_paytype_text')=='fixpay'){$stavkatype=$stavkavar[1];}
if (get_option('unchck_paytype_text')=='fixbonuspay'){$stavkatype=$stavkavar[2];}


//вариант ставки за 1000 знаков
$znakvar[0]='без пробелов'; 
$znakvar[1]='с пробелами';

if (get_option('unchck_spacepaytype_text')=='bez_probelov_text'){$znaktype=$znakvar[0];}
if (get_option('unchck_spacepaytype_text')=='s_probelami_text'){$znaktype=$znakvar[1];}


$userkey=get_option('unchck_userkey_text');
$stavka=get_option('unchck_stavka_text');
$normavoda=get_option('unchck_water_text');
$fixstavka=get_option('unchck_fixstavka_text');
$normaznak=get_option('unchck_normaznak_text');
$normauniс=get_option('unchck_unique_text');
$normaplagiat=get_option('unchck_sovpad_text');
$normaspam=get_option('unchck_spam_text');
$bill=get_option('unchck_bill_text');
$to=get_option('admin_email');


//запуск получения результата
unchck_getResultPost($text_uid, $userkey, $arturl,$stavka, $stavkatype, $stavkavar, $title, $username, $fixstavka, $znakvar, $znaktype, $normaznak, $normaplagiat, $normaspam, $normauniс, $normavoda,$bill,$to); 
}
  exit;}
 } 
 
 add_action('init', 'unchck_mycallback');

?>