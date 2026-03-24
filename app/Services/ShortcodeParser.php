<?php

namespace App\Services;

class ShortcodeParser
{
    protected array $handlers = [];

    public function __construct()
    {
        $this->registerDefaultHandlers();
    }

    /**
     * Register default shortcode handlers
     */
    protected function registerDefaultHandlers(): void
    {
        // Wufoo forms
        $this->register('wufoo', function($attributes) {
            $formId = $attributes['form'] ?? '';
            $header = $attributes['header'] ?? 'true';
            
            if (!$formId) {
                // Debug: show what attributes were received
                $attrDebug = empty($attributes) ? 'No attributes found' : 'Attributes: ' . json_encode($attributes);
                return "<!-- Wufoo form ID missing. {$attrDebug} -->";
            }
            
            return view('shortcodes.wufoo', [
                'formId' => $formId,
                'showHeader' => $header === 'true',
            ])->render();
        });

        // iframes
        $this->register('iframe', function($attributes) {
            $url = $attributes['url'] ?? '';
            $width = $attributes['width'] ?? '100%';
            $height = $attributes['height'] ?? '400';
            
            if (!$url) {
                return '<!-- iframe URL missing -->';
            }
            
            return view('shortcodes.iframe', [
                'url' => $url,
                'width' => $width,
                'height' => $height,
            ])->render();
        });

        // External scripts
        $this->register('script', function($attributes) {
            $src = $attributes['src'] ?? '';
            
            if (!$src) {
                return '<!-- script src missing -->';
            }
            
            return view('shortcodes.script', [
                'src' => $src,
            ])->render();
        });

        // Custom HTML/embed code
        $this->register('embed', function($attributes) {
            $code = $attributes['code'] ?? '';
            
            if (!$code) {
                return '<!-- embed code missing -->';
            }
            
            // Decode if it was base64 encoded
            if (isset($attributes['encoded']) && $attributes['encoded'] === 'true') {
                $code = base64_decode($code);
            }
            
            return $code;
        });
    }

    /**
     * Register a shortcode handler
     */
    public function register(string $name, callable $handler): void
    {
        $this->handlers[$name] = $handler;
    }

    /**
     * Parse content and replace shortcodes
     */
    public function parse(string $content): string
    {
        // Decode HTML entities first (RichEditor encodes quotes as &quot;)
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5);
        
        // Remove <p> tags that might wrap shortcodes
        $content = preg_replace('/<p>\s*(\[.*?\])\s*<\/p>/', '$1', $content);
        
        // Pattern matches: [shortcode attr="value" attr2="value2"]
        // Made more lenient to handle messy RichEditor output
        $pattern = '/\[(\w+)([^\]]*?)\]/s';
        
        return preg_replace_callback($pattern, function($matches) {
            $shortcode = $matches[1];
            $attributesString = trim($matches[2]);
            
            // Parse attributes
            $attributes = $this->parseAttributes($attributesString);
            
            // Call handler if exists
            if (isset($this->handlers[$shortcode])) {
                return $this->handlers[$shortcode]($attributes);
            }
            
            // Return original if no handler found
            return $matches[0];
        }, $content);
    }

    /**
     * Parse attribute string into array
     */
    protected function parseAttributes(string $attributesString): array
    {
        $attributes = [];
        
        // First, try to extract href from any <a> tags in the attributes
        // This handles the RichEditor auto-linking issue
        if (preg_match('/href=["\']([^"\']+)["\']/', $attributesString, $hrefMatch)) {
            // Found an href, use it as the URL
            $extractedUrl = $hrefMatch[1];
            
            // Replace the entire <a> tag mess with just the URL
            $attributesString = preg_replace('/<a[^>]*href=["\']([^"\']+)["\'][^>]*>.*?<\/a>/', '$1', $attributesString);
            $attributesString = preg_replace('/<a[^>]*href=["\']([^"\']+)["\'][^>]*>/', '$1', $attributesString);
        }
        
        // Pattern matches: attr="value" or attr='value'
        preg_match_all('/(\w+)\s*=\s*["\']([^"\']*)["\']/', $attributesString, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $value = $match[2];
            
            // Strip any remaining HTML tags
            $value = strip_tags($value);
            
            // If this is a URL attribute and we found an href earlier, use that instead
            if (isset($extractedUrl) && in_array($match[1], ['url', 'src', 'href'])) {
                $value = $extractedUrl;
            }
            
            $attributes[$match[1]] = $value;
        }
        
        return $attributes;
    }
}