# SYNONYMS

The Synonyms module enriches content Entities with the notion of synonyms.

## DOCUMENTATION & GUIDES

https://www.drupal.org/docs/contributed-modules/synonyms

## SUMMARY

Currently the module provides the following functionality:

* Support of synonyms through fields - both base and attached ones. Any field,
  for which synonyms provider exists, can be enabled as a source of synonyms.

* Synonyms UI module provides configuration forms and controller. It is used
  during development mostly and can be safely uninstalled at production.

* Synonyms List Field submodule provides a list of all known entity synonyms
  as a display field inside entity's 'Manage display' section.

* Synonyms-friendly Autocomplete and Select widgets for entity reference field
  type are available as respective submodules.

* Synonyms Search submodule provides a functionallity that extends core search
  filtering not only by entity name but also by one of its synonyms.

* Synonyms Views Field submodule provides a views 'Synonyms list' field which
  contains a list of all known entity synonyms.

* Synonyms Views Filter submodule provides synonyms-friendly exposed filter,
  and Synonyms Views Argument Validator submodule provides synonyms-friendly
  views contextual filter argument validation. They allow filtering not only
  by entity name but also by one of its synonyms.

## REQUIREMENTS

The Synonyms module requires Drupal core only.

## SUPPORTED SYNONYMS PROVIDERS

Module ships with ability to provide synonyms from the following field types:

* "Text" field type
* "Entity Reference" field type
* "Number" field type
* "Float" field type
* "Decimal" field type
* "Email" field type
* "Telephone" field type

Worth mentioning here: this list is easily extended further by implementing new
synonyms providers in your code. Refer to Synonyms documentation for more
details on how to accomplish it.

## INSTALLATION

Install as usual. Enable Synonyms, Synonyms UI, and other Synonyms submodules
you need. Synonyms UI submodule can be safely uninstalled on production.

## CONFIGURATION

You can configure synonyms of all eligible entity types at Admin -> Structure
-> Synonyms configuration page (/admin/structure/synonyms).
