<?php

require_once __DIR__ . '/include/autoload.php';

$config = new Config();

$tg = new Telegram($config->bot_token, $config->bot_name);

if (!$tg->user_id || !$tg->chat_id || !$tg->command) {
	return;
}

$db = new DB($config->db_host, $config->db_name, $config->db_user, $config->db_pass);

$lang = new Language();
$lang->setLangTag($tg->language_code);

$db->prepare('
	INSERT IGNORE INTO users (user_id, username, first_name, last_name, lang)
	VALUES (:user_id, :username, :first_name, :last_name, :lang)
	ON DUPLICATE KEY UPDATE
	`username` = :username_update,
	`first_name` = :first_name_update,
	`last_name` = :last_name_update,
	`update_time` = CURRENT_TIMESTAMP(),
	`lang` = :lang_update
')->execute(array(
	'user_id' => $tg->user_id,
	'username' => $tg->username,
	'first_name' => $tg->first_name,
	'last_name' => $tg->last_name,
	'lang' => $lang->getLangTag(),
	'username_update' => $tg->username,
	'first_name_update' => $tg->first_name,
	'last_name_update' => $tg->last_name,
	'lang_update' => $lang->getLangTag(),
));

$cardano = new Cardano();

$message = '';

switch ($tg->command) {
    case '/start':
		$message = $lang->sprint('START_MESSAGE');
        break;
    case '/help':
		$message = $lang->sprint('HELP_MESSAGE');
        break;
    case '/getrewardsaddress':
		$addr = '';
		$mnemonic = $tg->params;

		if ($mnemonic !== '') {
			$addr = $cardano->getAddressFromMnemonic($mnemonic);
		}
		
		$message = $addr ? $lang->sprintf('YOUR_REWARDS_ADDRESS', '<b>' . $addr . '</b>') : $lang->sprint('WRONG_MNEMONIC_PHRASE');

        break;
    case '/getrewards':
    case '/subscriberewards':
    case '/unsubscriberewards':
		$info = '';
		$addr = $tg->params;
		
		if ($addr !== '') {
			$info = $cardano->getAccountInfo($addr);
			if (is_object($info)) {
				$total_reward = round($info->value);
				$last_reward = isset($info->last_rewards) ? round($info->last_rewards->reward) : 0;
				if ($tg->command === '/subscriberewards' || $tg->command === '/unsubscriberewards') {
					$publish = $tg->command === '/subscriberewards' ? 1 : 0;
					
					$db->prepare('
						INSERT IGNORE INTO rewards (user_id, chat_id, addr, total_reward, last_reward, publish)
						VALUES (:user_id, :chat_id, :addr, :total_reward, :last_reward, :publish)
						ON DUPLICATE KEY UPDATE
						`total_reward` = :total_reward_update,
						`last_reward` = :last_reward_update,
						`publish` = :publish_update
					')->execute(array(
						'user_id' => $tg->user_id,
						'chat_id' => $tg->chat_id,
						'addr' => $addr,
						'total_reward' => $total_reward,
						'last_reward' => $last_reward,
						'publish' => $publish,
						'total_reward_update' => $total_reward,
						'last_reward_update' => $last_reward,
						'publish_update' => $publish,
					));
					$message .= ($tg->command === '/subscriberewards' ? $lang->sprint('REWARDS_SUBSCRIBE_SUCCESSFUL') : $lang->sprint('REWARDS_UNSUBSCRIBE_SUCCESSFUL')) . "\n\n";
				}
				$message .= $lang->sprint('REWARDS_TOTAL_VALUE') . ' ' . number_format($total_reward / 1000000, 6, ) . " ADA\n";
				$message .= $lang->sprint('REWARDS_LAST_VALUE') . ' ' . number_format($last_reward / 1000000, 6) . " ADA\n";
			}
		}
		
		if (!$message) {
			$message = $lang->sprintf('WRONG_REWARDS_ADDRESS', $tg->command);
		}

		break;
}

if ($message) {
	$tg->sendMessage($message);
}