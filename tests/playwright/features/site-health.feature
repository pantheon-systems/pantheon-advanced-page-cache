Feature: Site Health tests based on Cache Max Age

Background:
  Given I log in as an admin

Scenario: Site Health check should pass when Max Age is the recommended value
  When I visit "/wp-admin/options-general.php?page=pantheon-cache"
  And I fill in the field named "pantheon-cache[default_ttl]" with "604800"
  And I press "Save Changes"
  Then I should see "Settings saved."
  When I visit "/wp-admin/site-health.php"
  # The result sits in the collapsed "Passed tests" accordion, so expand it.
  And I press "Passed tests"
  And I press "Pantheon GCDN Cache Max Age set to 1 week"
  Then I should see "Pantheon GCDN Cache Max Age set to 1 week"
  And I should see "The Pantheon cache max age is currently set to 1 week. Our recommendation is 1 week or more."
