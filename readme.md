# DruPen

DruPen is a helper module that provides a few Drush commands which are
helpful when auditing the security of a Drupal 8 site.

## Installation

Place the `drupen` folder in your site's modules directory and enable
the module.

Currently the module only provides a few Drush commands, however more
interaction with Drupal is planned (hence it is a module and not just 
a Drush command).

## Requirements

DruPen requires PHP 5.6+ and Drupal 8.

## Commands

### drush route-list

Display a list of routes defined in the Drupal site.

`drush route-list`

This command will build a list of urls from the site's routes _with_
parameter replacements. This attempts to build an exhaustive list of 
_all_ urls the site will respond to.

### drush route-test

Request all routes and display the HTTP response code for each.

`drush route-test`

By default this command loads urls as an anonymous user, however a
session cookie can be passed as an option.

Output is currently comma delimited with the intention of being sent to
a csv file.

#### Options

**route-test** accepts 4 options:

`--response-code` : Filter output based on a provided HTTP response code.

`--response-cache` : Filter output based on a provided X-Drupal-Cache
HIT/MISS.

`--profile` : Display response times and X-Drupal-Cache HIT/MISS.

`--cookie` : Provide cookie information to the requests, which is
intended to authenticate the user. The `session-cookie` command can be
used to generate the cookie string.
 
#### Examples

List routes with 500 response code as an anonymous user.

`drush route-test --response-code=500`

List routes with 200 response code, with load times, as an authenticated
user.

`drush route-test --response-code=200 --profile --cookie="SESSe7c1f93bf5adaa9459634b1ab92db72d=JPNlqdkf9wT6PIIHu4bIRCH1EJua5vwKyaUUNQOJZvM; expires=Sat, 10-Sep-2016 07:15:28 GMT; Max-Age=2000000; path=/; domain=.example.dev; HttpOnly"`

### drush session-cookie

Output Set-Cookie header from standard Drupal authentication request.

`drush session-cookie`

#### Arguments

**session-cookie** requires 2 arguments:

`username` : Username use for login.

`password` : Password use for login.

#### Example

Create a session as `admin` user and display the cookie string. 

`drush session-cookie admin password`

