<?php

namespace App\Modules\Platform\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Public articles are authored in a browser, so they are untrusted input just
 * like any other request. Tiptap makes sensible HTML, but this whitelist is
 * the server-side boundary that removes scripts and event attributes.
 */
class BlogContent
{
    private const ALLOWED = [
        'p', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'strong', 'em', 'b', 'i',
        'a', 'blockquote', 'code', 'pre', 'br', 'hr', 'img', 'figure', 'figcaption',
    ];

    private const FORBIDDEN = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'svg', 'math',
    ];

    public static function sanitize(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->loadHTML('<?xml encoding="utf-8" ?><div id="blog-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('blog-root');
        if (! $root) {
            return '';
        }

        self::cleanChildren($root);

        $clean = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $clean .= $document->saveHTML($child);
        }

        return trim($clean);
    }

    /** @return array<int, array{question: string, answer: string}> */
    public static function faqItems(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->loadHTML('<?xml encoding="utf-8" ?><div id="blog-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('blog-root');
        if (! $root) return [];

        $inFaq = false;
        $items = [];
        foreach (iterator_to_array($root->childNodes) as $node) {
            if (! $node instanceof DOMElement) continue;
            $tag = strtolower($node->tagName);
            $text = trim($node->textContent);

            if ($tag === 'h2') {
                $inFaq = (bool) preg_match('/preguntas\s+frecuentes|faq/i', $text);
                continue;
            }

            if ($inFaq && in_array($tag, ['h3', 'h4'], true) && str_ends_with($text, '?')) {
                $answerNode = $node->nextSibling;
                while ($answerNode && (! $answerNode instanceof DOMElement || strtolower($answerNode->tagName) !== 'p')) {
                    $answerNode = $answerNode->nextSibling;
                }
                $answer = $answerNode ? trim($answerNode->textContent) : '';
                if ($answer !== '') $items[] = ['question' => $text, 'answer' => $answer];
            }
        }

        return $items;
    }

    private static function cleanChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if (! $node instanceof DOMElement) continue;

            $tag = strtolower($node->tagName);
            if (in_array($tag, self::FORBIDDEN, true)) {
                $parent->removeChild($node);
                continue;
            }
            if (! in_array($tag, self::ALLOWED, true)) {
                self::cleanChildren($node);
                self::unwrap($node);
                continue;
            }

            self::cleanAttributes($node, $tag);
            self::cleanChildren($node);
        }
    }

    private static function cleanAttributes(DOMElement $element, string $tag): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim($attribute->value);
            $allowed = ($tag === 'a' && in_array($name, ['href', 'target', 'rel'], true))
                || ($tag === 'img' && in_array($name, ['src', 'alt'], true));

            if (! $allowed || (($name === 'href' || $name === 'src') && ! self::isSafeUrl($value, $name === 'src'))) {
                $element->removeAttribute($attribute->name);
            }
        }

        if ($tag === 'a' && $element->getAttribute('target') === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private static function isSafeUrl(string $url, bool $isImage): bool
    {
        if ($url === '' || str_starts_with($url, '//')) return false;
        if (str_starts_with($url, '/')) return true;
        if ($isImage || str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
        }

        return str_starts_with($url, 'mailto:');
    }

    private static function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if (! $parent) return;

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }
        $parent->removeChild($element);
    }
}
