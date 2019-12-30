<?php

defined('_CARDANO') or die;

class Language {

	private $lang = array();

	private $langTag;

	private $defaultLangTag;

	public function __construct($defaultLangTag = 'en') {
		foreach(glob(dirname(__DIR__) . '/lang/*.ini') as $langname) {
			$path_parts = pathinfo($langname);
			$this->lang[$path_parts['filename']] = parse_ini_file($langname);
		}
		$this->defaultLangTag = $defaultLangTag;
	}

	public function setLangTag($langTag) {
		$this->langTag = isset($this->lang[$langTag]) ? $langTag : $this->defaultLangTag;
	}

	public function getLangTag() {
		return $this->langTag;
	}

	public function sprint($constant) {
		return isset($this->lang[$this->langTag]) && isset($this->lang[$this->langTag][$constant]) ? $this->lang[$this->langTag][$constant] : $constant;
	}

	public function print($constant) {
		echo $this->sprint($constant);
	}

	public function sprintf($constant, ...$params) {
		return sprintf($this->sprint($constant), ...$params);
	}

	public function printf($constant, ...$params) {
		printf($this->sprint($constant), ...$params);
	}

}