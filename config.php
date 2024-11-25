<?php

require_once INCLUDE_DIR . 'class.plugin.php';

class SOCMattermostPluginConfig extends PluginConfig {
    function getOptions() {
        return array(
            'socmattermost' => new SectionBreakField(array(
                'label' => 'SOCMattermost Configuration',
            )),

            'mattermost-api-url' => new TextboxField(array(
                'label' => 'Mattermost API URL',
                'required' => true,
                'hint' => 'Enter the Mattermost API URL for sending notifications.',
                'configuration' => array('size' => 100, 'length' => 200),
            )),

            'mattermost-api-key' => new TextboxField(array(
                'label' => 'Mattermost API Key',
                'required' => true,
                'hint' => 'Enter the API key for authenticating with Mattermost.',
                'configuration' => array('size' => 100, 'length' => 200),
            )),

            'mattermost-critical-channel' => new TextboxField(array(
                'label' => 'Critical Severity Channel ID',
                'required' => true,
                'hint' => 'Enter the Mattermost channel ID for Critical severity notifications.',
                'configuration' => array('size' => 100, 'length' => 200),
            )),

            'mattermost-high-channel' => new TextboxField(array(
                'label' => 'High Severity Channel ID',
                'required' => true,
                'hint' => 'Enter the Mattermost channel ID for High severity notifications.',
                'configuration' => array('size' => 100, 'length' => 200),
            )),

            'mattermost-medium-channel' => new TextboxField(array(
                'label' => 'Medium Severity Channel ID',
                'required' => true,
                'hint' => 'Enter the Mattermost channel ID for Medium severity notifications.',
                'configuration' => array('size' => 100, 'length' => 200),
            )),

            'mattermost-low-channel' => new TextboxField(array(
                'label' => 'Low Severity Channel ID',
                'required' => true,
                'hint' => 'Enter the Mattermost channel ID for Low severity notifications.',
                'configuration' => array('size' => 100, 'length' => 200),
            )),

            'socmattermost-debug-enabled' => new BooleanField(array(
                'label' => 'Enable Debugging',
                'required' => false,
                'default' => false,
                'hint' => 'Enable or disable debugging for SOCMattermost plugin.',
            )),

            'notify-critical' => new BooleanField(array(
                'label' => 'Notify on Critical Severity',
                'required' => false,
                'default' => true,
                'hint' => 'Enable or disable notifications for Critical severity.',
            )),

            'notify-high' => new BooleanField(array(
                'label' => 'Notify on High Severity',
                'required' => false,
                'default' => true,
                'hint' => 'Enable or disable notifications for High severity.',
            )),

            'notify-medium' => new BooleanField(array(
                'label' => 'Notify on Medium Severity',
                'required' => false,
                'default' => true,
                'hint' => 'Enable or disable notifications for Medium severity.',
            )),

            'notify-low' => new BooleanField(array(
                'label' => 'Notify on Low Severity',
                'required' => false,
                'default' => true,
                'hint' => 'Enable or disable notifications for Low severity.',
            )),

            'testing-mode' => new BooleanField(array(
                'label' => 'Enable Testing Mode',
                'required' => false,
                'default' => false,
                'hint' => 'Enable testing mode to validate configuration without sending notifications.',
            )),
        );
    }
}
