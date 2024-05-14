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
  .wordpress-org/*
  .github/*
  tests/phpunit/*
)

# Fetch list of changed files from the last commit
changed_files=$(git diff-tree --no-commit-id --name-only -r HEAD)
should_run_tests=true

for file in $changed_files; do
	if ! echo "$file" | grep -Eq "^($ignored_paths)"; then
	  should_run_tests=true
	  echo "Running tests because $file was changed."
	  break
	else
	  should_run_tests=false
	fi
done

if [ "$should_run_tests" = false ]; then
  echo "Only ignored files modified. Skipping Behat tests."
  circleci-agent step halt
fi
