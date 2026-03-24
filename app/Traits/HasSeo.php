<?php

namespace App\Traits;

trait HasSeo
{
    /**
     * Initialize the trait - add casts
     */
    public function initializeHasSeo(): void
    {
        $this->casts['index'] = 'boolean';
    }
    
    /**
     * Get SEO title for frontend (with fallback)
     * Use this in your blade: {{ $page->getSeoTitle() }}
     */
    public function getSeoTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }
    
    /**
     * Get SEO description for frontend (with fallback)
     * Use this in your blade: {{ $page->getSeoDescription() }}
     */
    public function getSeoDescription(): ?string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }
        
        // Fallback to content excerpt if available
        if (isset($this->content) && $this->content) {
            // Strip shortcodes first, then HTML tags
            $content = $this->stripShortcodes($this->content);
            $content = strip_tags($content);
            
            return \Illuminate\Support\Str::limit($content, 160);
        }
        
        return null;
    }
    
    /**
     * Strip all shortcodes from content
     * Removes: [wufoo form="..."], [iframe ...], [script ...], [embed ...], etc.
     */
    protected function stripShortcodes(string $content): string
    {
        // Remove all shortcodes (anything in square brackets)
        // Pattern matches: [anything] or [anything attr="value"]
        return preg_replace('/\[([^\]]+)\]/', '', $content);
    }
    
    /**
     * Check if this content should be indexed by search engines
     */
    public function shouldIndex(): bool
    {
        return $this->index ?? true;
    }
}