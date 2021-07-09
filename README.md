

# PHPAuthServer
> oAuth 2.0 and OIDC based identity provider written in PHP.

PHPAuth allows you to authenticate applications within a microservice mesh without
requiring them to manage their own credentials. Each application can authenticate a 
user and itself against any application on the identity server and define it's own
contexts (secure areas), allowing users to have granular control over which 
data inside the application other applications can access.

PHPAuth is not a perfect implementation of oAuth or OIDC, so you may run into some 
quirks that differentiate it from other implementations.

## Installing / Getting started

You can start PHPAuth with `docker-compose` like

```shell
docker build .
docker-compose up -d
```

The `d` stands for daemon and keeps docker running in the background, so PHPAuth will continue
running even if you close the terminal. It's optional.

If you want the project to run without `docker` I recommend you still check into the `Dockerfile`
since that contains the detailed instructions on how we exactly set up our environment. But you'll
need [composer](https://getcomposer.com) to pull the dependencies for the project.

```shell
apt install php-mysql php-memcached memcached php-intl
composer install
nano bin/settings/environments.php # To edit the database credentials
```

### Initial Configuration

After installation, you can head to the URL of the application (in docker it will be `localhost:8085` by default)
and you should be presented with the set up page. The set up will guide you through the basic 
configuration to create an administrative account.

The set up also creates a default application for the authentication server and 
initializes the configuration.

## Developing

To help the development of PHPAuth, please start of by reaching out to us. We do
only mirror our code to Github, but internally use Phabricator for code reviews and
similar. This means that accepting Pull Requests is cumbersome for us and will
remove credit from you. Just shoot us an email at cesar@magic3w.com and I'll help
you get started with Phabricator.

Once you have an account on our Phabricator and have `Arcanist` set up, you can just 
pull the repository like normal:

```shell
git clone ssh://git@phabricator.magic3w.com/source/phpas.git
cd phpas/
composer install
```

To start working on your own changes I recommend you check out a new branch on your
local repository.

```shell
git checkout -b dev/my-new-feature
```

You can now make the changes you need. Test the code locally, once you're satisfied,
you can send us a `diff` (Phabricator's version of a Pull Request) by following these
steps.

```shell
composer test # This will tell you if your code matches our guidelines.
git add .
git commit -m "Your commit message"
arc diff
```

You will be prompted to explain the changes you made, how to test them and who should
review your change. You can leave the reviewer empty if you're unsure.

### Building

Once you've made your changes and wish to test the application, just run:

```shell
docker build .
```

You should get a built container out of this that you can now publish to your
server or push to a docker registry from which it can be downloaded. If you're
running on a Kubernetes cluster, you can also push it to the cluster directly.

## Features

* Securely log users into your network, providing a zero-trust environment
* Generate JWT tokens for your applications
* Create scopes / contexts for secure components inside your applications
* Integration with our profile management and permission management servers


## Contributing

Thank you so much for contributing to making the project better. We appreciate
all contributions, big and small.

Code submissions are always welcome. As stated above, I would recommend you reach
out and get onboarded to our Phabricator, but if you just want to make a small
submission you can always send a pull request on Github.

## Links

- Project homepage: https://phabricator.magic3w.com/source/phpas/
- Repository: https://phabricator.magic3w.com/source/phpas/
- Issue tracker: https://phabricator.magic3w.com/maniphest/task/edit/form/default/
  - Alternative link: https://github.com/Csardelacal/PHPAuthServer/issues
  - You will need an account to access the Phabricator
  - In case of sensitive bugs like security vulnerabilities, please contact
    cesar@magic3w.com directly instead of using issue tracker. We value your effort
    to improve the security and privacy of this project!
- Related projects:
  - Permission server: https://phabricator.magic3w.com/source/permission/
  - Profile server: https://phabricator.magic3w.com/source/Switches/


## Licensing

The code in this project is licensed under GPL license.
