<?php
defined('ABSPATH') || exit;

class InvestorySpot_SEO_AI {

    private $api_key;
    private $model;
    private $api_url = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct() {
        $this->api_key = get_option('investoryspot_seo_api_key', '');
        $this->model   = get_option('investoryspot_seo_model', 'llama-3.3-70b-versatile');
    }

    public function is_configured() {
        return !empty($this->api_key);
    }

    public function generate_meta_title($content, $keyphrase = '') {
        $prompt = 'Generate an SEO-optimized meta title (max 60 characters) for the following content.';
        if ($keyphrase) {
            $prompt .= ' Focus keyphrase: "' . sanitize_text_field($keyphrase) . '".';
        }
        $prompt .= ' Return ONLY the title text, nothing else. Content: ' . wp_trim_words($content, 200);

        return $this->call_groq($prompt);
    }

    public function generate_meta_description($content, $keyphrase = '') {
        $prompt = 'Generate an SEO-optimized meta description (max 160 characters) for the following content.';
        if ($keyphrase) {
            $prompt .= ' Focus keyphrase: "' . sanitize_text_field($keyphrase) . '".';
        }
        $prompt .= ' Write a compelling snippet that encourages clicks. Return ONLY the description text, nothing else. Content: ' . wp_trim_words($content, 200);

        return $this->call_groq($prompt);
    }

    public function suggest_keyphrases($content) {
        $prompt = 'Suggest 5 SEO keyphrases for the following content. Return as a comma-separated list only. Content: ' . wp_trim_words($content, 200);
        return $this->call_groq($prompt);
    }

    public function analyze_content($content, $keyphrase = '') {
        $prompt = 'Analyze the following content for SEO. Provide a JSON response with exactly these keys: "score" (0-100 number), "issues" (array of strings with problems found), "suggestions" (array of strings with improvements), "readability" (string: easy/medium/hard), "keyword_density" (string percentage).';
        if ($keyphrase) {
            $prompt .= ' Analyze for keyphrase: "' . sanitize_text_field($keyphrase) . '".';
        }
        $prompt .= ' Content: ' . wp_trim_words($content, 300);

        $response = $this->call_groq($prompt);

        $decoded = json_decode($response, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return array(
            'score'           => 50,
            'issues'          => array('Could not analyze content automatically.'),
            'suggestions'     => array('Review content manually.'),
            'readability'     => 'medium',
            'keyword_density' => '0%',
        );
    }

    private function call_groq($prompt) {
        if (!$this->is_configured()) {
            return 'API key not configured. Please add your Groq API key in Settings > InvestorySpot SEO Assistant.';
        }

        $body = array(
            'model'    => $this->model,
            'messages' => array(
                array(
                    'role'    => 'system',
                    'content' => 'You are an expert SEO assistant. Provide concise, accurate SEO recommendations.',
                ),
                array(
                    'role'    => 'user',
                    'content' => $prompt,
                ),
            ),
            'temperature' => 0.7,
            'max_tokens'  => 500,
        );

        $response = wp_remote_post($this->api_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/json',
            ),
            'body'    => wp_json_encode($body),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return 'Error: ' . $response->get_error_message();
        }

        $status = wp_remote_retrieve_response_code($response);
        $body   = wp_remote_retrieve_body($response);
        $data   = json_decode($body, true);

        if ($status !== 200) {
            $msg = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown API error';
            return 'API Error: ' . $msg;
        }

        if (isset($data['choices'][0]['message']['content'])) {
            return trim($data['choices'][0]['message']['content']);
        }

        return 'Unexpected API response format.';
    }
}
