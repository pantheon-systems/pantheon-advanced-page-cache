Feature: Site Health tests based on Cache Max Age

Background:
	Given I log in as an admin

Scenario: Site Health should report when Max Age is a low value
	When I go to "/wp-admin/options-general.php?page=pantheon-cache"
	And I fill in "pantheon-cache[default_ttl]" with "300"
	And I go to "/wp-admin/site-health.php"
	Then I should see "Pantheon GCDN Cache Max-Age" in the "#health-check-site-status-recommended" element
	And I should see "The Pantheon GCDN cache max-age is currently set to 5 mins (300 seconds). We recommend increasing to 1 week (604800 seconds)" in the "#health-check-accordion-block-pantheon_edge_cache" element
