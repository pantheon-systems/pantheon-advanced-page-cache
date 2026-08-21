@papc
Feature: PAPC plugin activation and admin page

  Background:
    Given I log in as an admin

  @activation
  Scenario: PAPC plugin is listed and active
    When I go to "/wp-admin/plugins.php"
    Then I should see "Pantheon Advanced Page Cache"

  @admin
  Scenario: Pantheon Page Cache settings page loads
    When I go to "/wp-admin/options-general.php?page=pantheon-cache"
    Then I should see "Pantheon Page Cache"

  @dashboard
  Scenario: WordPress dashboard loads correctly
    When I go to "/wp-admin/"
    Then I should see "Dashboard"
