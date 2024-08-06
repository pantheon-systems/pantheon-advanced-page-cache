Feature: Site Health tests based on Cache Max Age

Background:
	Given I log in as an admin

Scenario: Site Health check should pass when Max Age is the recommneded value
	When I go to "/wp-admin/options-general.php?page=pantheon-cache"
	And I fill in "pantheon-cache[default_ttl]" with "604800"
	And I press "Save Changes"
	And I go to "/wp-admin/site-health.php"
	Then I should see "Pantheon GCDN Cache Max Age set to 1 week"
	And I should see "The Pantheon cache max age is currently set to 1 week. Our recommendation is 1 week or more."
