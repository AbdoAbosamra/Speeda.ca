<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Support\UnsubscribeLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Base for every automated email whose copy is editable from the dashboard.
 *
 * Subclasses only declare WHICH template key they are and what decoration
 * (stats strip / progress bar) belongs to them. Subject and all body copy are
 * resolved through EmailTemplate, so an admin edit takes effect immediately
 * without a deploy — and if no override exists, the shipped default is used.
 */
abstract class TemplatedEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** Resolved copy, memoised so envelope() and content() agree. */
    private ?array $resolved = null;

    /**
     * $recipient is the account the mail is going to. It is optional only so a
     * mailable can still be rendered for preview without one; every real send
     * passes it, and without it the email goes out with no opt-out link.
     */
    public function __construct(
        public readonly string $recipientName,
        public readonly string $dashboardUrl,
        public readonly ?User $recipient = null,
    ) {}

    /**
     * Signed opt-out link for this recipient, or null when unknown.
     */
    protected function unsubscribeUrl(): ?string
    {
        return UnsubscribeLink::forNullable($this->recipient);
    }

    /**
     * Key in EmailTemplate::defaults().
     */
    abstract protected function templateKey(): string;

    /**
     * Optional stats strip: [['value' => '3x', 'label' => 'More Views'], ...]
     */
    protected function stats(): array
    {
        return [];
    }

    /**
     * Optional progress bar: ['label' => ..., 'text' => ..., 'percent' => 0-100]
     */
    protected function progress(): array
    {
        return [];
    }

    protected function footerNote(): string
    {
        $group = EmailTemplate::defaults()[$this->templateKey()]['group'] ?? 'provider';

        return $group === 'client'
            ? "You're receiving this because you have an account on Speeda."
            : "You're receiving this because you registered as a service provider on Speeda.";
    }

    protected function content_(): array
    {
        return $this->resolved ??= EmailTemplate::resolve($this->templateKey(), [
            'provider_name' => $this->recipientName,
            'dashboard_url' => $this->dashboardUrl,
        ]);
    }

    public function envelope(): Envelope
    {
        $unsubscribeUrl = $this->unsubscribeUrl();

        if ($unsubscribeUrl === null) {
            return new Envelope(subject: $this->content_()['subject']);
        }

        return new Envelope(
            subject: $this->content_()['subject'],
            // Powers the "Unsubscribe" control Gmail and Outlook render next to
            // the sender name. Mailbox providers read its presence as a strong
            // positive reputation signal, so it also protects delivery of the
            // mail people do want.
            using: [
                function (\Symfony\Component\Mime\Email $message) use ($unsubscribeUrl) {
                    $headers = $message->getHeaders();
                    $headers->addTextHeader('List-Unsubscribe', '<' . $unsubscribeUrl . '>');
                    $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
                },
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.provider.journey',
            with: [
                'c' => $this->content_(),
                'dashboardUrl' => $this->dashboardUrl,
                'stats' => $this->stats(),
                'progress' => $this->progress(),
                'footerNote' => $this->footerNote(),
                'unsubscribeUrl' => $this->unsubscribeUrl(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
