# Blog Articles Component
A fork of [Joomla! 3](https://github.com/joomla/joomla-cms/)'s *com_content* component, created for use in situations where a client (or developer) may want the core content component's functionality with some customisations (i.e. multiple categories).

## Features
* Multiple categories – allows users to associate articles with more than one category (main category field is still used for SEF URL purposes)
* Embedded video field that supports embedding a YouTube or Vimeo video within an article

## Layout overrides
The files in the `/_layout-files` directory are template overrides that allow the core content layouts to be used with both com_content and com_blog. Upload them to the `/html/layouts/joomla/content/` directory of your active template to use them.

## Core content version equivalents
Below is a quick table showing how this fork's versions line up with those of the core content component, starting with v3.0.9.

| com_blog    | Joomla/com_content  |
| ----------- | ------------------  |
| 3.0.26      | 3.9.26-3.10.2       |
| 3.0.25      | 3.9.25              |
| 3.0.24      | 3.9.24              |
| 3.0.23      | 3.9.23              |
| 3.0.22      | 3.9.21~22           |
| 3.0.19~21   | 3.9.20              |
| 3.0.18      | 3.9.19              |
| 3.0.17      | 3.9.17              |
| 3.0.16      | 3.9.16              |
| 3.0.15      | 3.9.15              |
| 3.0.14      | 3.9.14              |
| 3.0.13      | 3.9.13              |
| 3.0.11~12   | 3.9.11~12           |
| 3.0.10      | 3.9.7~10            |
| 3.0.9       | 3.9.4~6             |

## Miscellaneous
With the release of custom fields in Joomla! 3.7, this component/fork isn't really all that necessary since you can add the video field as a custom field and manipulate its output in a template override (and the multi-category relation can be done with tags, as well). It is neat, though, to see that not only can the core content component be pulled out into its own installable package but both it and the renamed fork can use the same template layouts with some clever overrides. :)
