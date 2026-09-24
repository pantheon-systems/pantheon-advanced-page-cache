Feature: Adjust the Default Max Age setting

Background:
  Given I log in as an admin

Scenario Outline: Change the cache max age to <label>
  When I go to "/wp-admin/options-general.php?page=pantheon-cache"
  And I fill in "pantheon-cache[default_ttl]" with "<ttl>"
  And I press "Save Changes"
  Then I should see "Settings saved."
  And the "pantheon-cache[default_ttl]" field should contain "<ttl>"

  Examples:
    | label   | ttl      |
    | 1 week  | 604800   |
    | 1 month | 2592000  |
    | 1 year  | 31536000 |
