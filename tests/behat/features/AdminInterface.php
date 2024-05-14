<?php
// tests/behat/features/AdminInterface.php

namespace behat\features\bootstrap;

use Behat\Behat\Context\Context;

class AdminInterface implements Context
{
	/**
	 * @When I open the accordion :accordionText
	 */
	public function iOpenTheAccordion($accordionText)
	{
		$session = $this->getSession();
		$page = $session->getPage();

		$accordion = $page->find('xpath', sprintf('//div[contains(text(), "%s")]', $accordionText));

		if (null === $accordion) {
			throw new \Exception(sprintf('The accordion "%s" was not found.', $accordionText));
		}

		$accordion->click();
	}
}
