Feature: Verify CDN behavior as it pertains to a not existing page

  Scenario: Non existing page emits a 404 surrogate key
    Given I go to "/not-existing"
    Then the response header "Surrogate-Key" should not be ""
    And the response header "Surrogate-Key-Raw" should be "404"
