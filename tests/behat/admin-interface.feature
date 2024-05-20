Feature: Adjust the Default Max Age setting

Background:
	Given I log in as an admin

Scenario: Set max-age to 600 and auto-update to the default value
	When I go to "/wp-admin/options-general.php?page=pantheon-cache"
	And I fill in "pantheon-cache[default_ttl]" with "600"
	And I press "Save Changes"
	Then I should see "The Pantheon GCDN cache max-age has been updated. The previous value was 10 minutes. The new value is 1 week."
	When I go to "/wp-admin/options-general.php?page=pantheon-cache"
	Then the "pantheon-cache[default_ttl]" field should contain "604800"

Scenario: Change the cache max age
	When I go to "/wp-admin/options-general.php?page=pantheon-cache"
	And I fill in "pantheon-cache[default_ttl]" with "300"
	And I press "Save Changes"
	Then I should see "This is a very low value and may not be optimal for your site" in the ".notice" element
	And I should see "Consider increasing the cache max-age to at least 1 week" in the ".notice" element

Scenario: Change the cache max age to 1 week
	When I go to "/wp-admin/options-general.php?page=pantheon-cache"
	And I fill in "pantheon-cache[default_ttl]" with "604800"
	And I press "Save Changes"
	Then I should see "Settings saved."
