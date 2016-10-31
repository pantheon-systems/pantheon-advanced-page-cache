Feature: Verify CDN behavior as it pertains to a single WordPress post

  Scenario: Single post emits correct surrogate keys
    Given I go to "/?p=1"
    Then the response header "Surrogate-Key-Raw" should be "post-1 user-1 term-1 single"

