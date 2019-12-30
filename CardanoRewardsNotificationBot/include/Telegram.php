<?php

defined('_CARDANO') or die;

class Telegram {

	public $chat_id = 0;
	public $user_id = 0;
	public $language_code = '';
	public $first_name = '';
	public $last_name = '';
	public $username = '';
	public $command = '';
	public $params = '';

	private $bot_token;
	private $bot_name;
	
	public function __construct($bot_token, $bot_name) {
		$this->bot_token = $bot_token;
		$this->bot_name = $bot_name;
		$content = json_decode(file_get_contents('php://input'));
		if ($content && is_object($content) && $content->message) {
			$this->chat_id = $content->message->chat->id;
			$this->user_id = $content->message->from->id;
			$this->language_code = $content->message->from->language_code;
			$this->first_name = trim($content->message->from->first_name);
			$this->last_name = trim($content->message->from->last_name);
			$this->username = trim($content->message->from->username);

			$text = strtolower($content->message->text);
			$text = str_replace('@' . strtolower($this->bot_name), '', $text);
			$text = preg_replace('/\s/', ' ', $text);
			$text = preg_replace('/\s\s+/', ' ', $text);
			$text = explode(' ', $text, 2);
			$this->command = isset($text[0]) ? trim($text[0]) : '';
			$this->params = isset($text[1]) ? trim($text[1]) : '';
		}

	}

	public function setRecipient($chat_id) {
		$this->chat_id = $chat_id;
	}

	public function sendMessage($message) {
		$data = array(
			'chat_id' => $this->chat_id,
			'text' => $message,
			'reply_to_message_id' => 0,
			'parse_mode' => 'HTML',
		);

		return $this->send('sendMessage', $data);
	}

	public function send($method, $data) {
		if ($curl = curl_init()) {
			curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot' . $this->bot_token . '/' . $method);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			$res = json_decode(curl_exec($curl));
			curl_close($curl);
			
			if (is_object($res) && $res->ok) {
				return true;
			}
		}

		return false;
	}

}