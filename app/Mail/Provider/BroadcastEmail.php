<?php

namespace App\Mail\Provider;

use App\Models\EmailTemplate;
use App\Models\ProviderBroadcast;
use App\Support\AdminHtml;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * A single admin-composed broadcast, addressed to one provider.
 *
 * Deliberately NOT a TemplatedEmail subclass: those resolve their copy from
 * EmailTemplate defaults, whereas every word here comes from what the admin
 * typed. Only the placeholder substitution helper is reused.
 *
 * Also deliberately NOT ShouldQueue. SendProviderBroadcastEmail is already the
 * queued unit of work; if this mailable queued itself too, the job would hand
 * the message off to a second job and immediately mark the recipient
 * "delivered" — recording a success for mail that had not been sent yet, and
 * losing any SMTP error in a job that cannot update the ledger. Sending
 * synchronously inside the job is what makes the delivery report truthful.
 */
class BroadcastEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ProviderBroadcast $broadcast,
        public readonly string $recipientName,
        public readonly string $dashboardUrl,
        public readonly string $unsubscribeUrl,
    ) {}

    /**
     * Subject and body with {{ placeholder }} tokens filled in.
     *
     * @return array{subject:string, body:string, cta_label:string, cta_url:string, preheader:string}
     */
    private function copy(): array
    {
        $resolved = EmailTemplate::substitute([
            'subject' => $this->broadcast->subject,
            'body' => $this->broadcast->body,
            'cta_label' => (string) $this->broadcast->cta_label,
            'cta_url' => (string) $this->broadcast->cta_url,
            'preheader' => (string) $this->broadcast->preheader,
        ], [
            'provider_name' => $this->recipientName,
            'dashboard_url' => $this->dashboardUrl,
        ]);

        // The body is the only field rendered unescaped, so it is cleaned again
        // at render time — a row written before the sanitiser existed, or by a
        // direct database edit, still cannot inject active content.
        $resolved['body'] = AdminHtml::clean($resolved['body']);

        return $resolved;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->copy()['subject'],
            // One-click unsubscribe support in Gmail/Outlook. Mailbox providers
            // treat its presence as a strong positive reputation signal.
            using: [
                function (\Symfony\Component\Mime\Email $message) {
                    $headers = $message->getHeaders();
                    $headers->addTextHeader('List-Unsubscribe', '<' . $this->unsubscribeUrl . '>');
                    $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
                },
            ],
        );
    }

    public function content(): Content
    {
        $copy = $this->copy();

        return new Content(
            view: 'emails.provider.broadcast',
            with: [
                'copy' => $copy,
                'plainBody' => AdminHtml::toText($copy['body']),
                'unsubscribeUrl' => $this->unsubscribeUrl,
                'dashboardUrl' => $this->dashboardUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
