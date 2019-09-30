Feature: Verify CDN behavior as it pertains to the REST API

  Scenario: Fetching an individual post emits correct surrogate keys
    Given I go to "/wp-json/wp/v2/posts/1"
    Then the response header "Surrogate-Key" should not be ""
    And the response header "Surrogate-Key-Raw" should be "rest-post-1"

  Scenario: Fetching an individual page emits correct surrogate keys
    Given I go to "/wp-json/wp/v2/pages/2"
    Then the response header "Surrogate-Key" should not be ""
    And the response header "Surrogate-Key-Raw" should be "rest-post-2"
