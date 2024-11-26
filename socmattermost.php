<?php
require_once('config.php');

class SOCMattermostPlugin extends Plugin {
    var $config_class = "SOCMattermostPluginConfig";
    static $pluginInstance = null;

    /**
     * Fetch the current plugin instance or default to the first available instance.
     *
     * @param int|null $id
     * @return Plugin|null
     */
    private function getPluginInstance(?int $id) {
        if ($id && ($i = $this->getInstance($id))) {
            return $i;
        }
        return $this->getInstances()->first();
    }

    /**
     * Initialize error logging for the plugin.
     */

    /**
     * Centralized logging with levels.
     *
     * @param string $level Log level (DEBUG, INFO, ERROR).
     * @param string $message The message to log.
     */
    private function log($level, $message) {
        $logFile = '/var/log/osticket/debug.log';
        $timestamp = date('[Y-m-d H:i:s]');
        $formattedMessage = "$timestamp [$level] $message" . PHP_EOL;

        if ($this->getConfig()->get('socmattermost-debug-enabled')) {
            file_put_contents($logFile, $formattedMessage, FILE_APPEND);
        }
    }

    /**
     * Bootstrap the plugin by connecting to signals and enabling error logging.
     */
     function bootstrap() {
        // Load the active plugin instance
        self::$pluginInstance = self::getPluginInstance(null);

        // Register signal for new tickets
        Signal::connect('ticket.created', array($this, 'onTicketCreated'));
    }

    /**
     * Handle the creation of a new ticket.
     *
     * @param Ticket $ticket
     */
    function onTicketCreated(Ticket $ticket) {

        // Retrieve configuration settings
        $config = $this->getConfig(self::$pluginInstance);

        $this->log('INFO', 'onTicketCreated triggered.');
        if (!$ticket) {
            $this->log('ERROR', 'No ticket object provided to onTicketCreated.');
            return;
        }

        try {
            $title = $ticket->getSubject() ?: 'No subject';
            $body = $ticket->getLastMessage() ? $ticket->getLastMessage()->getMessage() : 'No content';
            $severity = $this->extractSeverityFromBody($body) ?: 'medium';

            $this->log('DEBUG', "Ticket Details - Subject: $title, Severity: $severity");

            $channel_id = $this->getChannelForSeverity($severity);

            if (!$channel_id) {
                $this->log('ERROR', "No channel ID configured for severity '$severity'.");
                return;
            }

            $payload = [
                'channel_id' => $channel_id,
                'message' => "**Subject:** $title\n**Message:** $body",
            ];

            $this->log('INFO', 'PAYLOAD: ' . json_encode($payload));

            try {
                $this->sendMattermostNotification($payload);

            } catch (Exception $e) {
                $this->log('ERROR', 'Exception in sendMattermostNotification(): ' . $e->getMessage());
            }
        } catch (Exception $e) {
            $this->log('ERROR', 'Exception in onTicketCreated: ' . $e->getMessage());
        }
    }

    /**
     * Extract severity from the ticket body.
     *
     * @param string $body The ticket body.
     * @return string|null Severity level or null.
     */
    private function extractSeverityFromBody($body) {
        if (preg_match('/Severity\s*:\s*(Critical|High|Medium|Low)/i', $body, $matches)) {
            $severity = strtolower($matches[2]);
            $this->log('DEBUG', "Extracted severity: $severity");
            return $severity;
        }
        $this->log('DEBUG', 'No severity pattern found in ticket body.');
        return null;
    }

    /**
     * Retrieve the Mattermost channel ID for a given severity.
     *
     * @param string $severity Severity level.
     * @return string|null Channel ID or null if not found.
     */
    private function getChannelForSeverity($severity) {
        $channel_id = $this->getConfig()->get("mattermost-{$severity}-channel");
        if ($channel_id) {
            $this->log('DEBUG', "Retrieved channel ID for '$severity': $channel_id");
        } else {
            $this->log('ERROR', "Channel ID for '$severity' not found in configuration.");
        }
        return $channel_id;
    }

    /**
     * Send a notification to Mattermost.
     *
     * @param array $payload The API payload.
     */
    private function sendMattermostNotification($payload) {

        $api_url = $this->getConfig()->get('mattermost-api-url');
        $api_key = $this->getConfig()->get('mattermost-api-key');

        if (!$api_url || !$api_key) {
            $this->log('ERROR', 'Mattermost API URL or API key not configured.');
            return;
        }

        try {

            if (!function_exists('curl_init')) {
                $this->log('ERROR', 'cURL is not installed or enabled in PHP.');
                return;
            }
            try {
                $ch = curl_init($api_url);
            } catch (Exception $e) {
                $this->log('ERROR', 'Exception in curl_init: ' . $e->getMessage());
            }

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $api_key,
                'Content-Type: application/json',
            ]);

            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);

            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($response === false) {
                $this->log('ERROR', 'cURL error: ' . curl_error($ch));
            } elseif ($statusCode != 200) {
                $this->log('ERROR', "API error (HTTP $statusCode): $response");
            } else {
                $this->log('INFO', 'Notification sent successfully.');
            }

            curl_close($ch);
        } catch (Exception $e) {
            $this->log('ERROR', 'Exception during API request: ' . $e->getMessage());
        }
    }
}
