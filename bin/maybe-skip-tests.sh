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
should_run_tests=true


is_ignored_file(){
    for ignore in "${ignored_paths[@]}"; do
		if [[ "${1:-}" == *"$ignore"* ]]; then
			return 0
		fi
	done
	return 1
}

for file in $changed_files; do
    if ! is_ignored_file "$file"; then
		echo "Running tests because $file was changed."
		break
    fi
    should_run_tests=false
    echo "Skipping $file..."
done

if [ "$should_run_tests" = false ]; then
  echo "Only ignored files modified. Skipping Behat tests."
  circleci-agent step halt
fi
