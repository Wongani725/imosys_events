<?php

namespace App\Mail;

use GuzzleHttp\Client;

class MicrosoftGraphTransport implements \Swift_Transport
{
    protected $tenantId;
    protected $clientId;
    protected $clientSecret;
    protected $fromAddress;
    protected $started = false;
    protected $plugins = [];

    public function __construct($tenantId, $clientId, $clientSecret, $fromAddress)
    {
        $this->tenantId = $tenantId;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->fromAddress = $fromAddress;
    }

    public function isStarted()
    {
        return $this->started;
    }

    public function start()
    {
        $this->started = true;
    }

    public function stop()
    {
        $this->started = false;
    }

    public function ping()
    {
        return true;
    }

    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        $this->plugins[] = $plugin;
    }

    public function send(\Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $token = $this->getAccessToken();

        $client = new Client();
        $response = $client->post("https://graph.microsoft.com/v1.0/users/{$this->fromAddress}/sendMail", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => $this->buildPayload($message),
        ]);

        $recipients = array_merge(
            array_keys($message->getTo() ?? []),
            array_keys($message->getCc() ?? []),
            array_keys($message->getBcc() ?? [])
        );

        return count($recipients);
    }

    protected function getAccessToken(): string
    {
        $client = new Client();
        $response = $client->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token", [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'https://graph.microsoft.com/.default',
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        if (!isset($data['access_token'])) {
            throw new \RuntimeException('Failed to obtain Microsoft Graph access token: ' . ($data['error_description'] ?? 'Unknown error'));
        }

        return $data['access_token'];
    }

    protected function buildPayload(\Swift_Mime_SimpleMessage $message): array
    {
        $bodyContent = $message->getBody() ?? '';
        $contentType = $message->getContentType() ?? 'text/plain';
        $isHtml = false;

        if (stripos($contentType, 'text/html') !== false) {
            $isHtml = true;
        } elseif (stripos($contentType, 'multipart') !== false) {
            foreach ($message->getChildren() as $child) {
                if ($child instanceof \Swift_Mime_MimePart && stripos($child->getContentType() ?? '', 'text/html') !== false) {
                    $htmlBody = $child->getBody();
                    if (is_resource($htmlBody)) {
                        $htmlBody = stream_get_contents($htmlBody);
                    }
                    if (!empty($htmlBody)) {
                        $bodyContent = $htmlBody;
                        $isHtml = true;
                    }
                    break;
                }
            }
        }

        if (!$isHtml) {
            $isHtml = preg_match('/^\s*<(html|!DOCTYPE|head|body)/i', trim($bodyContent));
        }

        $mail = [
            'subject' => $message->getSubject() ?? '',
            'body' => [
                'contentType' => $isHtml ? 'HTML' : 'Text',
                'content' => $bodyContent,
            ],
            'toRecipients' => $this->addressesToRecipients($message->getTo()),
        ];

        $cc = $message->getCc();
        if (!empty($cc)) {
            $mail['ccRecipients'] = $this->addressesToRecipients($cc);
        }

        $bcc = $message->getBcc();
        if (!empty($bcc)) {
            $mail['bccRecipients'] = $this->addressesToRecipients($bcc);
        }

        $attachments = $this->getAttachments($message);
        if (!empty($attachments)) {
            $mail['attachments'] = $attachments;
        }

        return ['message' => $mail, 'saveToSentItems' => false];
    }

    protected function addressesToRecipients(array $addresses): array
    {
        $result = [];
        foreach ($addresses as $email => $name) {
            $entry = ['emailAddress' => ['address' => $email]];
            if (!empty($name)) {
                $entry['emailAddress']['name'] = $name;
            }
            $result[] = $entry;
        }
        return $result;
    }

    protected function getAttachments(\Swift_Mime_SimpleMessage $message): array
    {
        $attachments = [];
        foreach ($message->getChildren() as $child) {
            if ($child instanceof \Swift_Mime_Attachment) {
                $body = $child->getBody();
                if (is_resource($body)) {
                    $body = stream_get_contents($body);
                }
                $attachments[] = [
                    '@odata.type' => '#microsoft.graph.fileAttachment',
                    'name' => $child->getFilename() ?? 'attachment',
                    'contentType' => $child->getContentType(),
                    'contentBytes' => base64_encode($body ?? ''),
                ];
            }
        }
        return $attachments;
    }
}
