# Blog Articles Component
A fork of Joomla 3's com_content component, created for use in situations where a client (or developer) may want the core content component's functionality with some customisations (i.e. multiple categories).

## Features
* Multiple categories – allows users to associate articles with more than one category (main category field is still used for SEF URL purposes)
* Embedded video field that supports embedding a YouTube or Vimeo video within an article

## Miscellaneous
With the release of custom fields in Joomla! 3.7, this component/fork isn't really all that necessary since you can add the video field as a custom field and manipulate its output in a template override (and the multi-category relation can be done with tags, as well). It is neat, though, to see that not only can the core content component be pulled out into its own installable package but both it and the renamed fork can use the same template layouts with some clever overrides. :)
