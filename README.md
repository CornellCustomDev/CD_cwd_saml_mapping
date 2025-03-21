[![Latest Stable Version](https://poser.pugx.org/cubear/cwd_saml_mapping/v/stable)](https://packagist.org/packages/cubear/cwd_saml_mapping)

## INTRODUCTION

The cwd_saml_mapping module is for use with drupal/saml_sp, and allows for the following:

- Authentication through multiple IDP's ex. Cornell Test, Weill Test, Cornell Production Shibboleth
- Configuration of which IDP to use in production (test or prod) and hooks all other non prod instance to test Shibboleth
- Auto role assignment via a Saml Role Mapping config entity
- Ability to map saml data into Drupal user fields
- Ability to add redirect per role on login
- Ability to add redirect per role on logout

## REQUIREMENTS

This module depends on the drupal/saml_sp module.

## INSTALLATION

``` composer require cubear/cwd_saml_mapping ```

``` drush en cwd_saml_mapping ```


## CONFIGURATION
- Enable the module as you would any other module
- Configure the global module settings: /admin/config/people/cwd-saml-mapping-config
- Configure the roles you want mapped: /admin/config/people/cwd-saml-mapping-config/saml-role-mapping
- Configure field mapping form saml to Drupal user fields: /admin/config/people/cwd-saml-mapping-config/saml-field-mapping
- Configure login redirect: /admin/config/people/cwd-saml-mapping-config/saml-login-redirect
- Configure logout redirects: /admin/config/people/cwd-saml-mapping-config/saml-logout-redirect

## DOCUMENTATION

A whole lot more documentation is available on Confluence: https://confluence.cornell.edu/display/customdev/SAML_SP

## MAINTAINERS

Current maintainers for Drupal 10:

- Bill Juda
