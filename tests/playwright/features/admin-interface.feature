Feature: Adjust the Default Max Age setting

Background:
	Given I log in as an admin

Scenario: Change the cache max age to 1 week
	When I go to "/wp-admin/options-general.php?page=pantheon-cache"
	And I fill in "pantheon-cache[default_ttl]" with "604800"
	And I press "Save Changes"
	Then I should see "Settings saved."
	And the "pantheon-cache[default_ttl]" field should contain "604800"

Scenario: Change the cache max age to 1 month
	When I go to "/wp-admin/options-general.php?page=pantheon-cache"
	And I fill in "pantheon-cache[default_ttl]" with "2592000"
	And I press "Save Changes"
	Then I should see "Settings saved."
	And the "pantheon-cache[default_ttl]" field should contain "2592000"

Scenario: Change the cache max age to 1 year
	When I go to "/wp-admin/options-general.php?page=pantheon-cache"
	And I fill in "pantheon-cache[default_ttl]" with "31536000"
	And I press "Save Changes"
	Then I should see "Settings saved."
	And the "pantheon-cache[default_ttl]" field should contain "31536000"
