<?php

namespace NotificationTracker\Mail;

use Illuminate\Support\Str;
use NotificationTracker\Models\TrackedChannel;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;
use Symfony\Component\Mime\Part\Multipart\RelatedPart;
use Symfony\Component\Mime\Part\TextPart;

class MailConverter
{
    protected bool $pixelAdded = false;

    public function __construct(
        protected TrackedChannel $tracker,
        protected Email          $email,
    )
    {
    }

    public function format()
    {
        $emailBody = $this->email->getBody();
        if (
            ($emailBody instanceof (AlternativePart::class)) ||
            ($emailBody instanceof (MixedPart::class)) ||
            ($emailBody instanceof (RelatedPart::class))
        ) {
            $newParts = [];
            foreach ($emailBody->getParts() as $part) {
                if ($part->getMediaSubtype() === 'html') {
                    $newParts[] = new TextPart(
                        $this->makeTrackable($part->getBody()),
                        $this->email->getHtmlCharset(),
                        $part->getMediaSubtype(),
                        null
                    );

                    continue;
                }

                if ($part->getMediaSubtype() === 'alternative') {
                    if (method_exists($part, 'getParts')) {
                        foreach ($part->getParts() as $subPart) {
                            if ($subPart->getMediaSubtype() == 'html') {
                                $newParts[] = new TextPart(
                                    $this->makeTrackable($subPart->getBody()),
                                    $this->email->getHtmlCharset(),
                                    $subPart->getMediaSubtype(),
                                    null
                                );

                                break;
                            }
                        }
                    }

                    continue;
                }

                $newParts[] = $part;
            }
            $this->email->setBody(new (get_class($emailBody))(...$newParts));

            return;
        }

        if ($emailBody->getMediaSubtype() == 'html') {
            $this->email->setBody(
                new TextPart(
                    $this->makeTrackable($emailBody->getBody()),
                    $this->email->getHtmlCharset(),
                    $emailBody->getMediaSubtype(),
                    null
                )
            );

            return;
        }
    }

    protected function makeTrackable(string $content): string
    {
        $content = $this->addPixelImage($content);

        // TODO: future extension: add other trackers like click?

        return $content;
    }

    protected function addPixelImage(string $content): string
    {
        if ($this->pixelAdded) {
            return $content;
        }

        do {
            $br = Str::random();
        } while (str_contains($content, $br));
        $content = str_replace("\n", $br, $content);

        if (preg_match('/^(.*<body[^>]*>)(.*)$/im', $content, $matches)) {
            $content = $matches[1] . $this->tracker->getPixelImageHtml() . $matches[2];
        } else {
            $content .= $this->tracker->getPixelImageHtml();
        }

        $content = str_replace($br, "\n", $content);

        $this->pixelAdded = true;

        return $content;
    }
}
