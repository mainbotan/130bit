<?php

namespace App\Services;

class DecoderService {
    public function decode($description) {
        return $this->processNode($description['dom']);
    }

    private function processNode($node) {
        if (!isset($node['children']) || !is_array($node['children'])) {
            return '';
        }

        $output = '';

        foreach ($node['children'] as $child) {
            if (is_string($child)) {
                $output .= $this->escapeText($child);  // Экранированный текст
            } elseif (is_array($child) && isset($child['tag'])) {
                $output .= $this->processTag($child);  // Экранированный тег
            }
        }

        return $output;
    }

    private function processTag($tagNode) {
        $tag = $tagNode['tag'];
        $attributes = $this->processAttributes($tagNode['attributes'] ?? []);
        $content = $this->processNode($tagNode);

        // Для самостоятельных элементов
        if (empty($content)) {
            return "<$tag$attributes />";
        }

        // Для элементов с контентом
        return "<$tag$attributes>$content</$tag>";
    }

    private function processAttributes($attributes) {
        $output = '';
        foreach ($attributes as $key => $value) {
            $output .= ' ' . $this->escapeText($key) . '="' . $this->escapeText($value) . '"';
        }
        return $output;
    }

    // Метод для экранирования текста (для предотвращения XSS)
    private function escapeText($text) {
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
