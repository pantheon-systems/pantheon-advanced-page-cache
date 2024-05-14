#!/bin/bash

# Paths to ignore.
ignored_paths=(
  .editorconfig
  .gitattributes
  .gitignore
  CODEOWNERS
  CONTRIBUTING.md
  LICENSE
  phpcs.xml.dist
  phpunit.xml.dist
  README.md
  readme.txt
  .circleci/config.yml
  bin/maybe-skip-tests.sh
  bin/helpers.sh
  bin/install-local-tests.sh
  bin/install-wp-tests.sh
  bin/phpunit-test.sh
  .wordpress-org/*
  .github/*
  tests/phpunit/*
)

# Fetch list of changed files from the last commit
changed_files=$(git diff-tree --no-commit-id --name-only -r HEAD)
should_skip=false

for file in $changed_files; do
	if echo "$file" | grep -Eq "^($ignored_paths)$"; then
	  should_skip=true
	  echo "Skipping $file..."
	else
	  should_skip=false
	  echo "Running tests because $file was changed."
	  break
	fi
done

if [ "$should_skip" = true ]; then
  echo "Only ignored files modified. Skipping Behat tests."
  circleci-agent step halt
fi
