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

# Fetch the list of changed files across the whole PR, falling back to the last
# commit when there is no base to compare against (push, schedule, dispatch).
if [ -n "${GITHUB_BASE_REF:-}" ] && git rev-parse --verify "origin/${GITHUB_BASE_REF}" >/dev/null 2>&1; then
  changed_files=$(git diff --name-only "origin/${GITHUB_BASE_REF}...HEAD")
else
  changed_files=$(git diff-tree --no-commit-id --name-only -r HEAD)
fi
# Default to running: if the diff came back empty, something is off and skipping
# would silently hide a real change.
should_run_tests=false
[ -z "$changed_files" ] && should_run_tests=true


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
		should_run_tests=true
		break
    fi
    echo "Skipping $file..."
done

if [ "$should_run_tests" = false ]; then
  echo "Only ignored files modified. Skipping Behat tests."
  # Exit with failure to skip tests in GitHub Actions
  # The workflow will handle this with continue-on-error
  exit 1
fi
