# Kick manage article

## Task Scheduler to move articles from cat a to cat b based on date-time value in custom field

This plugin is modification of the original plugin (https://github.com/Kicktemp/plg_task_kickmanagearticle) from Kicktemp.

### How it works

You can set 
- category from witch you need articles to move
- category to which articles should be moved
- custom field name where the date-time value is stored
- date-time format you use, the default is "%d.%m.%Y %H:%i

Then, you set the interval of the task run. When the time stored in the custom field is greater than the current time, the plugin moves the article from Category A to Category B.

Compatibillity: Joomla 4/5
