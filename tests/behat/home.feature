Feature: Verify CDN behavior as it pertains to the WordPress homepage

  Scenario: Homepage emits correct surrogate keys
    Given I go to "/"
    Then the response header "Surrogate-Key-Raw" should be "front home post-1 user-1"
    And the response header "Surrogate-Key" should not be ""

