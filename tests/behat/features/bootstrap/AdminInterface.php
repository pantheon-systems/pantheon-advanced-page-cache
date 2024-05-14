<?php
// tests/behat/features/AdminInterface.php

namespace Behat\Features\Bootstrap;

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;

class AdminInterface extends MinkContext implements Context
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
