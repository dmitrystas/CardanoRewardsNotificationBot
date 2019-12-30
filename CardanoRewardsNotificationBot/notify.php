<?php

if (PHP_SAPI !== 'cli') die;

require_once __DIR__ . '/include/autoload.php';

$config = new Config();

$tg = new Telegram($config->bot_token, $config->bot_name);

$db = new DB($config->db_host, $config->db_name, $config->db_user, $config->db_pass);

$lang = new Language();

$cardano = new Cardano();

$address = array();

$rows = $db->query('
			SELECT r.id, r.chat_id, r.addr, r.total_reward, r.last_reward, u.lang
			FROM rewards AS r
			LEFT JOIN users AS u
			ON r.user_id = u.user_id
			WHERE r.publish = 1
		')->fetchAll();

foreach ($rows as $row) {

	$addr = $row['addr'];
	if (isset($address[$addr])) {
		$info = $address[$addr];
	} else {
		$info = $cardano->getAccountInfo($addr);
	}

	if (is_object($info)) {
		$address[$addr] = $info;
		$total_reward = round($info->value);
		$last_reward = isset($info->last_rewards) ? round($info->last_rewards->reward) : 0;
		if ($total_reward == $row['total_reward'] && $last_reward == $row['last_reward']) {
			continue;
		}
		
		$lang->setLangTag($row['lang']);
		
		$message  = $lang->sprintf('REWARDS_CHANGE', $row['addr']) . "\n\n";
		$message .= $lang->sprint('REWARDS_TOTAL_VALUE') . ' ' . number_format($total_reward / 1000000, 6) . " ADA\n";
		$message .= $lang->sprint('REWARDS_LAST_VALUE') . ' ' . number_format($last_reward / 1000000, 6) . " ADA\n";
		
		$tg->setRecipient($row['chat_id']);

		if ($tg->sendMessage($message)) {
			$sql = $db->prepare('UPDATE rewards SET total_reward=:total_reward, last_reward=:last_reward WHERE id=:id');
						
			$sql->execute(array(
				'total_reward' => $total_reward,
				'last_reward' => $last_reward,
				'id' => $row['id'],
			));
		}
	}

}
