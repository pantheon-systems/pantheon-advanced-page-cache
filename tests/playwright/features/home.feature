Feature: Verify CDN behavior as it pertains to the WordPress homepage

  Scenario: Homepage emits correct surrogate keys
    Given I request "/"
    Then the response header "Surrogate-Key-Raw" should be "front home post-1"
