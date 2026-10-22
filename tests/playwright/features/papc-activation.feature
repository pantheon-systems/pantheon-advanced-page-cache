Feature: PAPC plugin activation and admin page

  Background:
    Given I log in as an admin

  Scenario: PAPC plugin is listed and active on the plugins page
    When I visit "/wp-admin/plugins.php"
    Then I should see "Pantheon Advanced Page Cache"
    And the "pantheon-advanced-page-cache/pantheon-advanced-page-cache.php" plugin should be active

  Scenario: Pantheon Page Cache settings page loads
    When I visit "/wp-admin/options-general.php?page=pantheon-cache"
    Then I should see "Pantheon Page Cache"

  Scenario: WordPress dashboard loads correctly
    When I visit "/wp-admin/"
    Then I should see "Dashboard"
