Feature: Verify CDN behavior as it pertains to the REST API

  Scenario: Fetching an individual post emits correct surrogate keys
    Given I go to "/wp-json/wp/v2/posts/1"
    Then the response header "Surrogate-Key" should not be ""
    And the response header "Surrogate-Key-Raw" should be "rest-post-1"

  Scenario: Fetching a post collection emits correct surrogate keys
    Given I go to "/wp-json/wp/v2/posts"
    Then the response header "Surrogate-Key" should not be ""
    And the response header "Surrogate-Key-Raw" should be "rest-post-collection rest-post-1"

  Scenario: Fetching an individual page emits correct surrogate keys
    Given I go to "/wp-json/wp/v2/pages/2"
    Then the response header "Surrogate-Key" should not be ""
    And the response header "Surrogate-Key-Raw" should be "rest-post-2"

  Scenario: Fetching an individual category emits correct surrogate keys
    Given I go to "/wp-json/wp/v2/categories/1"
    Then the response header "Surrogate-Key" should not be ""
    And the response header "Surrogate-Key-Raw" should be "rest-term-1"

  Scenario: Fetching a category collection emits correct surrogate keys
    Given I go to "/wp-json/wp/v2/categories"
    Then the response header "Surrogate-Key" should not be ""
    And the response header "Surrogate-Key-Raw" should be "rest-category-collection rest-term-1"

  Scenario: Fetching an individual user emits correct surrogate keys
    Given I go to "/wp-json/wp/v2/users/1"
    Then the response header "Surrogate-Key" should not be ""
    And the response header "Surrogate-Key-Raw" should be "rest-user-1"
