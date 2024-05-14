Feature: Site Health tests based on Cache Max Age

Background:
	Given I log in as an admin

Scenario: Site Health should report when Max Age is a low value
	When I go to "/wp-admin/options-general.php?page=pantheon-cache"
	And I fill in "pantheon-cache[default_ttl]" with "300"
	And I press "Save Changes"
	And I go to "/wp-admin/site-health.php"
	Then I should see "Pantheon GCDN Cache Max-Age"
