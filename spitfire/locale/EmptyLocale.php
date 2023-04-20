<?php namespace spitfire\locale;

class EmptyLocale extends Locale
{
	
	public function getCurrency() {
		return new CurrencyLocalizer('.', ',', CurrencyLocalizer::SYMBOL_BEFORE);
	}

	public function getDateFormatter() {
		return null; //TODO: implement
	}
	
	/**
	 * The empty translator just parrots what it gets back like it received it.
	 * Basically providing no translation.
	 * 
	 * @param string $msgid
	 * @return string
	 */
	public function getMessage($msgid) {
		return $msgid;
	}

}

