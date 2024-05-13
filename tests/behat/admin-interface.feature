Feature: Adjust the Default Max Age setting

Background:
	Given I log in as an admin

Scenario: Change the cache max age
	When I go to "/wp-admin/options-general.php?page=pantheon-cache"
	And I fill in "pantheon-cache[default_ttl]" with "300"
	Then I should see "We recommend increasing to 1 week"
