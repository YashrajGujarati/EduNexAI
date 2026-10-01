<?php
// ============================================================
// EduNexAI — Gemini AI Integration Helper
// Graceful fallback when GEMINI_API_KEY is not configured
// ============================================================

/**
 * Returns the Gemini API key from environment, or null if unconfigured.
 */
function getGeminiApiKey() {
    $key = getenv('GEMINI_API_KEY');
    if (!$key || $key === 'your_gemini_api_key_here' || trim($key) === '') {
        return null;
    }
    return trim($key);
}

/**
 * Checks whether Gemini AI is properly configured.
 */
function isGeminiConfigured() {
    return getGeminiApiKey() !== null;
}
