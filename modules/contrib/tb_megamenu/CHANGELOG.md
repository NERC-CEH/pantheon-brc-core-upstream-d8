# The Better Mega Menu Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [\[Unreleased: 8.x-1.x-dev\]](https://www.drupal.org/project/tb_megamenu/releases/8.x-1.x-dev)

## [\[8.x-1.0-rc3\]](https://www.drupal.org/project/tb_megamenu/releases/8.x-1.0-rc3) - 2021-02-11
### Fixed

- Issue [#3196330](https://www.drupal.org/project/tb_megamenu/issues/3196330) by quondam: Uncaught ReferenceError: value is not defined
- Issue [#3196569](https://www.drupal.org/project/tb_megamenu/issues/3196569) by quondam: TypeError: Argument 1 passed to Drupal\tb_megamenu\TBMegaMenuBuilder::editColumnConfig() causes site crash
- Issue [#3195480](https://www.drupal.org/project/tb_megamenu/issues/3195480) by quondam: Improve messaging in toolbox admin UI

## [\[8.x-1.0-rc2\]](https://www.drupal.org/project/tb_megamenu/releases/8.x-1.0-rc2) - 2021-01-28
### Fixed

- Issue [#3195191](https://www.drupal.org/project/tb_megamenu/issues/3195191) by quondam: WSOD with error related to null block_id

## [\[8.x-1.0-rc1\]](https://www.drupal.org/project/tb_megamenu/releases/8.x-1.0-rc1) - 2021-01-27

This is the first 8.x-1.x Release Candidate!  This version contains the required
fixes needed for Drupal Security Policy coverage.  The goal will be to apply for
coverage with this branch and ultimately roll in into an official and stable
1.0.0 version of The better menu.

### Changed

- Changed README.md to include CHANGELOG notes.
- Issue [#3131180](https://www.drupal.org/project/tb_megamenu/issues/3131180) by quondam, Suresh Prabhu Parkala: Drupal coding standards,
2021-01-22
- Issue [#3183288](https://www.drupal.org/project/tb_megamenu/issues/3183288) by quondam, timotej-pl, Scott Weston, themodularlab: Replace
  calls to \Drupal with Dependency Injection, 2021-01-19
- Issue [#3192235](https://www.drupal.org/project/tb_megamenu/issues/3192235) by knaffles, andrewozone: Accessibility Upgrades, 2021-01-19

### Deprecated

- Deprecated CHANGELOG.txt in favor of CHANGELOG.md

### Removed

- Removed CHANGELOG.txt in favor of CHANGELOG.md

### Fixed

- Issue [#2965871](https://www.drupal.org/project/tb_megamenu/issues/2965871) by quondam, gaurav.bajpai, andrewozone: When Parent menu
disabled Submenu getting shifted to previous parent, 2020-11-20
- Issue [#3194239](https://www.drupal.org/project/tb_megamenu/issues/3194239) by quondam, knaffles: Getting a 500 error when menu items are
reordered and then saved, 2021-01-22

### Security

- Issue [#3186616](https://www.drupal.org/project/tb_megamenu/issues/3186616) by knaffles, andrewozone, quondam, themodularlab: Security Advisory Coverage
no code other than release prep, adding because this is the parent security
coverage issue.
- Issue [#3186612](https://www.drupal.org/project/tb_megamenu/issues/3186612) by quondam: Security Advisory Coverage - Security Review

## [\[8.x-1.0-beta2\]](https://www.drupal.org/project/tb_megamenu/releases/8.x-1.0-beta2) - 2020-10-02
### Changed
- Issue [#3174475](https://www.drupal.org/project/tb_megamenu/issues/3174475) by John.nie: Array and string offset access syntax with
  curly braces is deprecated

### Fixed
- Issue [#3174476](https://www.drupal.org/project/tb_megamenu/issues/3174476) by John.nie, themodularlab: Render #post_render callbacks must
  be methods of a class that implements \Drupal\Core\Security\TrustedCallbackInterface or be an anonymous function.
- Issue [#3174465](https://www.drupal.org/project/tb_megamenu/issues/3174465) by John.nie: Mega menu missing config_export definition
  in its annotation.
- Issue [#3172977](https://www.drupal.org/project/tb_megamenu/issues/3172977) by Ramya Balasubramanian, dev.patrick: Unwanted special
  characters in hook help.

## [\[8.x-1.0-beta1\]](https://www.drupal.org/project/tb_megamenu/releases/8.x-1.0-beta1) - 2020-09-11
### Added
- Added README.md
- Issue [#3095820](https://www.drupal.org/project/tb_megamenu/issues/3095820) by themodularlab, knaffles, RuslanP: D8 Accessibility Upgrades

### Changed
- Issue [#3002715](https://www.drupal.org/project/tb_megamenu/issues/3002715) by i_g_wright, themodularlab, mellowtothemax: Admin permissions
  set too high

### Fixed
- Issue [#3149011](https://www.drupal.org/project/tb_megamenu/issues/3149011) by Project Update Bot, themodularlab: Automated Drupal 9
  compatibility fixes
- Reverting CSS related to accessibility on Issue #3095820
- Issue [#2882051](https://www.drupal.org/project/tb_megamenu/issues/2882051) by knaffles, maithili11: Top level drop downs do not work on
  touch devices when the menu item path is set to <nolink>
- Issue [#3095820](https://www.drupal.org/project/tb_megamenu/issues/3095820) Restore missing snippet for left arrow. Also fix issue where
  <nolink> top level menu items are not focusable using left/right arrow keys.
- Issue [#2952410](https://www.drupal.org/project/tb_megamenu/issues/2952410) by ankitjain28may, SivaprasadC: Typo in
  css/tb_megamenu.default.css
- Issue [#2982006](https://www.drupal.org/project/tb_megamenu/issues/2982006) by brahmjeet789, Neetika K, Vidushi Mehta: Hook help is missing
- Issue [#2921195](https://www.drupal.org/project/tb_megamenu/issues/2921195) by Zemelia, pafa7a: Missing array keys cause notices
- Issue [#2996857](https://www.drupal.org/project/tb_megamenu/issues/2996857) by knyshuk.vova: Incorrect contextual link generation leads to
  an error.
- Issue [#3045390](https://www.drupal.org/project/tb_megamenu/issues/3045390) by RuslanP, knaffles: Module Description: add dot to the end of
  the sentence.

[8.x-1.x-dev]: https://git.drupalcode.org/project/tb_megamenu/-/tree/8.x-1.x-dev
[8.x-1.0-rc3]: https://git.drupalcode.org/project/tb_megamenu/-/tags/8.x-1.0-rc3
[8.x-1.0-rc2]: https://git.drupalcode.org/project/tb_megamenu/-/tags/8.x-1.0-rc2
[8.x-1.0-rc1]: https://git.drupalcode.org/project/tb_megamenu/-/tags/8.x-1.0-rc1
[8.x-1.0-beta2]: https://git.drupalcode.org/project/tb_megamenu/-/tags/8.x-1.0-beta2
[8.x-1.0-beta1]: https://git.drupalcode.org/project/tb_megamenu/-/tags/8.x-1.0-beta1





