Feature: Verify CDN behavior as it pertains to a single WordPress post

  Scenario: Single post emits correct surrogate keys
    Given I go to "/?p=1"
    Then the response header "Surrogate-Key" should not be ""
    And the response header "Surrogate-Key-Raw" should be "post-1 post-user-1 post-term-1 single"

