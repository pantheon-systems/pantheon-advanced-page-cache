# Contributing

The best way to contribute to the development of this plugin is by participating on the GitHub project:

https://github.com/pantheon-systems/pantheon-advanced-page-cache

Pull requests and issues are welcome!

## Workflow

The `develop` branch is the development branch which means it contains the next version to be released. `master` contains the corresponding stable development version. Always work on the `develop` branch and open up PRs against `develop`.

## Release Process

1. Starting from `develop`, cut a release branch named `release_X.Y.Z` containing your changes.
1. Update plugin version in `package.json`, `README.md`, `readme.txt`, and `pantheon-advanced-page-cache.php`.
1. Update the Changelog with the latest changes.
1. Create a PR against the `master` branch.
1. After all tests pass and you have received approval from a CODEOWNER (including resolving any merge conflicts), merge the PR into `master`.
1. Pull `master` locally, create a new tag, and push up.
1. Confirm that the necessary assets are present in the newly created tag, and test on a WP install if desired.
1. Create a [new release](https://github.com/pantheon-systems/pantheon-advanced-page-cache/releases/new) using the tag created in the previous steps, naming the release with the new version number, and targeting the tag created in the previous step. Paste the release changelog from the `Changelog` section of the `README` into the body of the release and include a link to the closed issues if applicable.
1. Wait for the [_Release pantheon-advanced-page-cache plugin to wp.org_ action](https://github.com/pantheon-systems/pantheon-advanced-page-cache/actions/workflows/wordpress-plugin-deploy.yml) to finish deploying to the WordPress.org repository. If all goes well, users with SVN commit access for that plugin will receive an emailed diff of changes.
1. Check WordPress.org: Ensure that the changes are live on https://wordpress.org/plugins/pantheon-advanced-page-cache/. This may take a few minutes.