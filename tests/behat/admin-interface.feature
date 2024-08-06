Feature: Adjust the Default Max Age setting

Background:
	Given I log in as an admin

Scenario: Auto-update to the default value if the value
	When I go to "/wp-admin/options-general.php?page=pantheon-cache"
	Then I should see "The Pantheon GCDN cache max age has been updated. The previous value was 10 minutes. The new value is 1 week."
	And the "pantheon-cache[default_ttl]" field should contain "604800"

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
