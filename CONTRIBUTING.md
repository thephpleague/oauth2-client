# Contributing

Contributions are **welcome** and will be fully **credited**.

We accept contributions via Pull Requests on [GitHub](https://github.com/thephpleague/oauth2-client).


## Developing

Before working on oauth2-client, install the dependencies by running the following from the root directory of your local git clone:

```bash
composer install
```


## Pull Requests

> [!TIP]
> Before opening a pull request, be sure to run our full test suite locally:
>
> ```bash
> composer test
> ```

- **[PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)** - We follow a superset of PSR-12. The easiest way to apply the conventions is to run `composer cs-fix` before committing your work (be sure to visually inspect any changes the coding style fixer applies).

- **Add tests!** - Your patch won't be accepted if it doesn't have tests.

- **Document any change in behaviour** - Make sure the README and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow SemVer. Randomly breaking public APIs is not an option.

- **Create topic branches** - Don't ask us to pull from your master branch.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please squash them before submitting.

- **Ensure tests pass!** - Please run the tests (see below) before submitting your pull request, and make sure they pass. We won't accept a patch until all tests pass.

- **Ensure no coding standards violations** - Please run PHP Code Sniffer using the PSR-2 standard (see below) before submitting your pull request. A violation will cause the build to fail, so please make sure there are no violations. We can't accept a patch if the build fails.


## Testing

The following tests must pass for a build to be considered successful. If contributing, please ensure these pass before submitting a pull request.

```bash
./vendor/bin/parallel-lint src test
./vendor/bin/phpcs
./vendor/bin/phpunit
```

You can run them all at one time with:

```bash
composer test
```

**Happy coding**!
