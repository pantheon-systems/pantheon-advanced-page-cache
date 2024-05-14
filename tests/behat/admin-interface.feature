Feature: Adjust the Default Max Age setting

Background:
	Given I log in as an admin

Scenario: Change the cache max age
	When I go to "/wp-admin/options-general.php?page=pantheon-cache"
	And I fill in "pantheon-cache[default_ttl]" with "300"
	And I press "Save Changes"
	Then I should see "This is a very low value and may not be optimal for your site" in the ".notice" element
	And I should see "Consider increasing the cache max-age to at least 604800 seconds (1 week)" in the ".notice" element

Scenario: Change the cache max age to 5 days
	When I go to "/wp-admin/options-general.php?page=pantheon-cache"
	And I fill in "pantheon-cache[default_ttl]" with "432000"
	And I press "Save Changes"
	Then I should see "Consider increasing the cache max-age to at least 604800 seconds (1 week)" in the ".notice" element

Scenario: Change the cache max age to 1 week
	When I go to "/wp-admin/options-general.php?page=pantheon-cache"
	And I fill in "pantheon-cache[default_ttl]" with "604800"
	And I press "Save Changes"
	Then I should see "Settings saved."
