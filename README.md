# Kick manage articles

### Task Scheduler plugin for Joomla to move articles from categoy A to category B based on date-time value in custom field.

Typical Use Case:
Consider a scenario where you have events, and there's a need to automatically relocate past events from the "Future Events" category to the "Past Events" category.

This plugin is modification of the original plugin (https://github.com/Kicktemp/plg_task_kickmanagearticle) from Kicktemp.

### How it works

You can set 
- category from witch you need articles to move
- category to which articles should be moved
- custom field name where the date-time value is stored
- date-time format you use, the default is "%d.%m.%Y %H:%i

Then, you set the interval of the task run. When the time stored in the custom field is greater than the current time, the plugin moves the article from Category A to Category B.

Compatibillity: Joomla 4/5
