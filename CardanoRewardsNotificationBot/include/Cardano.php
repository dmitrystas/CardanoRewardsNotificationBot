<?php

defined('_CARDANO') or die;

class Cardano {
	
	private $cardano_path;

	public function __construct() {
		$this->cardano_path = dirname(dirname(__DIR__)) . '/cgi-bin';
	}

	public function getAccountInfo($addr) {
		return json_decode(shell_exec($this->cardano_path . '/jcli rest v0 account get ' . $addr . ' --output-format json 2>/dev/null'));
	}

	public function getAddressFromMnemonic($mnemonic) {
		$res = shell_exec('echo -e "' . $mnemonic . '\n" | ' . $this->cardano_path . '/cardano-wallet mnemonic reward-credentials 2>/dev/null');
		preg_match('/ed25519e_.*\S/', $res, $matches);
	
		if (count($matches) == 1) {
			$pk = preg_replace('/[^0-9a-z_]/i', '', $matches[0]);
			return shell_exec($this->cardano_path . '/jcli address account --testing --prefix addr $(echo ' . $pk . ' | jcli key to-public 2>/dev/null) 2>/dev/null');
		}
		
		return false;
	}

}