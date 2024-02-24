<?php

include "deepl-translate.php";

interface TranslationInterface
{
	public function requestTranslation();
}

trait TranslationTrait
{
	private ?string $str_to_translate = NULL;
	private ?string $from_lang = NULL;
	private ?string $target_lang = NULL;
	private ?string $translated = NULL;
	private ?string $detected_lang = NULL;
	private ?object $responseObj = NULL;
	public function getTranslatedString() : string|null
	{
		return $this->translated;
	}
	public function getStringToTranslate() : string
	{
		return $this->str_to_translate;
	}
	public function getFromLang() : string|null
	{
		return $this->from_lang;
	}
	public function getTargetLang() : string
	{
		return $this->target_lang;
	}
	public function getDetectedLanguage() : string|null
	{
		return $this->detected_lang;
	}
	public function setStringToTranslate($str) : void
	{
		$this->str_to_translate = $str;
	}
	public function setToLanguage($str) : void
	{
		$this->target_lang = $str;
	}
	public function setFromLanguage($str) : void
	{
		$this->from_lang = $str;
	}
	public function setTranslated($str) : void
	{
		$this->translated = $str;
	}
	public function setDetectedLanguage($str) : void
	{
		$this->detected_lang = $str;
	}
	public function setObj($str) : void
	{
		$this->responseObj = json_decode($str);
	}
	public function getObj() : object|null
	{
		return $this->responseObj;
	}
}