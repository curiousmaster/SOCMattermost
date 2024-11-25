# SOCMattermost
osTicket to Mattermost notification plugin

This plugin will parse the ticket body for "Severity: [CRITICAL|HIGH|MEDIUM|LOW]" and send a notification to the defined Mattermost channel.

## Installation
1) Download and install this repo in your plugins directory (e.g. /var/www/osTicket/upload/include/plugins).
2) Under Admin Panel -> Manage -> Plugins add the new plugin, configure it and finally enable it.
