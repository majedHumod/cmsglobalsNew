<?php

namespace App\Listeners;

use App\Services\Mail\TenantMailBrandingService;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\App;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class ApplyTenantMailBranding
{
    public function __construct(
        private readonly TenantMailBrandingService $branding,
    ) {
    }

    public function handle(MessageSending $event): void
    {
        App::setLocale('ar');

        $this->forceRtlHtml($event->message);

        if (! $this->branding->isTenantMailContext()) {
            return;
        }

        $from = config('mail.from.address');
        if (! is_string($from) || $from === '') {
            return;
        }

        $event->message->from(new Address($from, $this->branding->fromName()));
    }

    /**
     * Email CSS inliners / clients often keep text-align:left. Rewrite the HTML
     * body so every content node has explicit RTL direction and alignment.
     */
    private function forceRtlHtml(Email $message): void
    {
        $html = $message->getHtmlBody();
        if (! is_string($html) || $html === '') {
            return;
        }

        $html = preg_replace('/\sdir=(["\'])(ltr|rtl)\1/i', '', $html) ?? $html;
        $html = preg_replace('/<html\b/i', '<html dir="rtl" lang="ar"', $html, 1) ?? $html;
        $html = preg_replace('/<body\b/i', '<body dir="rtl"', $html, 1) ?? $html;

        $html = preg_replace_callback(
            '/<(p|h1|h2|h3|li|td|th|div|span)(\s[^>]*)?>/i',
            function (array $matches): string {
                $tag = strtolower($matches[1]);
                $attrs = $matches[2] ?? '';

                $keepCenter = preg_match('/\balign\s*=\s*(["\']?)center\1/i', $attrs)
                    || preg_match('/\bclass\s*=\s*(["\'])[^"\']*\b(action|button|header|footer)\b/i', $attrs);

                $style = '';
                if (preg_match('/\bstyle\s*=\s*(["\'])(.*?)\1/is', $attrs, $styleMatch)) {
                    $style = $styleMatch[2];
                    $attrs = preg_replace('/\s*\bstyle\s*=\s*(["\'])(.*?)\1/is', '', $attrs) ?? $attrs;
                }

                $style = preg_replace('/\btext-align\s*:\s*[^;]+;?/i', '', $style) ?? $style;
                $style = preg_replace('/\bdirection\s*:\s*[^;]+;?/i', '', $style) ?? $style;
                $style = trim($style, " \t\n\r\0\x0B;");

                if ($keepCenter) {
                    $style = 'direction:rtl;text-align:center;'.($style !== '' ? $style.';' : '');
                } else {
                    $style = 'direction:rtl;text-align:right;'.($style !== '' ? $style.';' : '');
                    if (! preg_match('/\balign\s*=/i', $attrs) && in_array($tag, ['td', 'th', 'p', 'h1', 'h2', 'h3', 'div'], true)) {
                        $attrs .= ' align="right"';
                    }
                }

                $attrs .= ' dir="rtl" style="'.trim($style, ';').';"';

                return '<'.$tag.$attrs.'>';
            },
            $html
        ) ?? $html;

        $message->html($html);
    }
}
