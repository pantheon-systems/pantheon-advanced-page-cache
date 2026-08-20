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
  .github/workflows/playwright-bdd.yml
  .github/workflows/lint-test.yml
  .github/workflows/build-tag-release.yml
  .github/workflows/release-pr.yml
  .github/workflows/wordpress-plugin-deploy.yml
  .github/workflows/composer-npm-diff.yml
  .github/workflows/validate-actions-workflows.yml
  .github/workflows/validate-plugin-version.yml
  bin/maybe-skip-tests.sh
  bin/helpers.sh
  bin/install-local-tests.sh
  bin/install-wp-tests.sh
  bin/phpunit-test.sh
  .wordpress-org/*
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
  # Exit with failure to skip tests in GitHub Actions
  # The workflow will handle this with continue-on-error
  exit 1
fi
