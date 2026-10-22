Feature: Verify CDN behavior as it pertains to the REST API

  Scenario Outline: Fetching <resource> emits correct surrogate keys
    Given I request "<endpoint>"
    Then the response header "Surrogate-Key-Raw" should be "<keys>"

    Examples:
      | resource                | endpoint                     | keys                                     |
      | an individual post      | /wp-json/wp/v2/posts/1       | rest-post-1                              |
      | a post collection       | /wp-json/wp/v2/posts         | rest-post-collection rest-post-1         |
      | an individual page      | /wp-json/wp/v2/pages/2       | rest-post-2                              |
      | an individual category  | /wp-json/wp/v2/categories/1  | rest-term-1                              |
      | a category collection   | /wp-json/wp/v2/categories    | rest-category-collection rest-term-1     |
      | an individual user      | /wp-json/wp/v2/users/1       | rest-user-1                              |
