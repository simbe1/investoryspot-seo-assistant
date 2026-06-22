<?php
defined('ABSPATH') || exit;

class InvestorySpot_SEO_Analysis {

    private $content;
    private $keyphrase;
    private $post_title;

    public function __construct($content, $keyphrase = '', $post_title = '') {
        $this->content    = $content;
        $this->keyphrase  = $keyphrase;
        $this->post_title = $post_title;
    }

    public function calculate_score() {
        $score = 0;
        $checks = $this->run_checks();

        $passed = 0;
        foreach ($checks as $check) {
            if ($check['pass']) {
                $passed++;
            }
        }

        $total = count($checks);
        $score = $total > 0 ? round(($passed / $total) * 100) : 0;

        return min($score, 100);
    }

    public function get_checks() {
        return $this->run_checks();
    }

    private function run_checks() {
        $checks = array();

        $word_count = str_word_count(wp_strip_all_tags($this->content));

        $checks[] = array(
            'name' => 'Content length',
            'pass' => $word_count >= 300,
            'note' => $word_count >= 300
                ? "Good: {$word_count} words (min 300)"
                : "Too short: {$word_count} words (need 300+)",
        );

        if ($this->keyphrase) {
            $kw_count = substr_count(strtolower($this->content), strtolower($this->keyphrase));
            $checks[] = array(
                'name' => 'Keyphrase in content',
                'pass' => $kw_count >= 1,
                'note' => $kw_count >= 1
                    ? "Found {$kw_count} time(s)"
                    : "Keyphrase not found in content",
            );

            $checks[] = array(
                'name' => 'Keyphrase in title',
                'pass' => $this->check_keyphrase_in_title(),
                'note' => $this->check_keyphrase_in_title()
                    ? 'Found in title'
                    : 'Not found in title',
            );
        }

        $checks[] = array(
            'name' => 'Meta title length',
            'pass' => true,
            'note' => 'Check in editor',
        );

        $checks[] = array(
            'name' => 'Meta description length',
            'pass' => true,
            'note' => 'Check in editor',
        );

        $headings = $this->count_headings();
        $checks[] = array(
            'name' => 'Headings present',
            'pass' => $headings >= 2,
            'note' => $headings >= 2
                ? "Found {$headings} headings"
                : "Only {$headings} heading(s), use more subheadings",
        );

        $images = $this->count_images();
        $checks[] = array(
            'name' => 'Images in content',
            'pass' => $images >= 1,
            'note' => $images >= 1
                ? "Found {$images} image(s)"
                : 'No images found, add at least one',
        );

        $links = $this->count_links();
        $checks[] = array(
            'name' => 'Internal/external links',
            'pass' => $links >= 1,
            'note' => $links >= 1
                ? "Found {$links} link(s)"
                : 'No links found, add relevant links',
        );

        $checks[] = array(
            'name' => 'Readability',
            'pass' => $this->check_readability(),
            'note' => $this->check_readability()
                ? 'Readable sentence length'
                : 'Sentences too long on average',
        );

        return $checks;
    }

    private function check_keyphrase_in_title() {
        if (empty($this->post_title)) {
            return false;
        }
        return stripos($this->post_title, $this->keyphrase) !== false;
    }

    private function count_headings() {
        preg_match_all('/<h[2-6][^>]*>/i', $this->content, $matches);
        return count($matches[0]);
    }

    private function count_images() {
        preg_match_all('/<img[^>]+>/i', $this->content, $matches);
        return count($matches[0]);
    }

    private function count_links() {
        preg_match_all('/<a[^>]+href=[\'"]/i', $this->content, $matches);
        return count($matches[0]);
    }

    private function check_readability() {
        $text = wp_strip_all_tags($this->content);
        $sentences = preg_split('/[.!?]+/', $text);
        $sentences = array_filter($sentences);

        if (empty($sentences)) {
            return true;
        }

        $total_words = 0;
        foreach ($sentences as $sentence) {
            $total_words += str_word_count(trim($sentence));
        }

        $avg_words = $total_words / count($sentences);
        return $avg_words <= 25;
    }
}
