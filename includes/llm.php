<?php
/**
 * ContractPeer - LLM Integration for Contract Analysis
 * Supports OpenAI and Anthropic via curl.
 */

function analyze_contract($contract_text, $user_preferences = []) {
    if (!LLM_API_KEY) {
        return ['error' => 'LLM API key not configured. Please contact support.'];
    }
    
    // Truncate extremely long contracts to stay within token limits
    $max_chars = 100000; // ~25K tokens
    if (strlen($contract_text) > $max_chars) {
        $contract_text = substr($contract_text, 0, $max_chars) . "\n\n[... CONTRACT TRUNCATED - showing first portion only ...]";
    }
    
    $system_prompt = get_analysis_system_prompt();
    $user_prompt = get_analysis_user_prompt($contract_text, $user_preferences);
    
    if (LLM_PROVIDER === 'anthropic') {
        return call_anthropic($system_prompt, $user_prompt);
    } else {
        return call_openai($system_prompt, $user_prompt);
    }
}

function get_analysis_system_prompt() {
    return <<<PROMPT
You are ContractPeer, an expert legal AI assistant specializing in contract review and risk analysis. Your role is to analyze contracts clause-by-clause and identify potential risks, unfavorable terms, and areas of concern.

IMPORTANT DISCLAIMERS:
- You are a decision-support tool, NOT providing legal advice.
- Always recommend the reviewing attorney make the final judgment.
- Clearly state confidence levels for each risk identified.
- Do not hallucinate clauses or provisions that don't exist in the contract.

ANALYSIS FRAMEWORK:
For each risk identified, provide:
1. Clause reference (quote the relevant text)
2. Risk category (e.g., liability, indemnification, termination, payment, IP, confidentiality, jurisdiction, dispute resolution)
3. Severity: HIGH (significant legal/financial exposure), MEDIUM (moderate concern), LOW (minor issue or suggestion)
4. Plain-language explanation of the risk
5. Recommended action or suggested revision

CATEGORIES TO CHECK:
- Indemnification clauses (one-sided vs mutual)
- Limitation of liability (caps, exclusions)
- Termination provisions (convenience vs cause, notice periods)
- Payment terms (timing, late fees, audit rights)
- Intellectual property ownership and licensing
- Confidentiality scope and duration
- Non-compete / non-solicitation clauses
- Dispute resolution (arbitration vs litigation, jurisdiction, class action waivers)
- Warranties and representations
- Force majeure provisions
- Amendment and modification procedures
- Assignment restrictions
- Insurance requirements
- Data protection and privacy clauses

OUTPUT FORMAT (JSON):
{
  "summary": "2-3 sentence overall assessment of the contract",
  "risk_level": "high|medium|low",
  "risks": [
    {
      "clause": "quoted text from contract",
      "category": "category name",
      "severity": "HIGH|MEDIUM|LOW",
      "confidence": "high|medium|low",
      "explanation": "plain-language explanation",
      "recommendation": "suggested action or revision"
    }
  ],
  "positive_aspects": ["favorable terms worth noting"],
  "missing_clauses": ["important clauses that are absent"],
  "key_terms": {
    "parties": "identified parties",
    "effective_date": "if stated",
    "term": "contract duration if stated",
    "governing_law": "if stated",
    "payment_terms": "summary if present"
  }
}

Always return valid JSON. If the document is not a contract, return:
{"summary": "This document does not appear to be a contract.", "risk_level": "low", "risks": [], "positive_aspects": [], "missing_clauses": [], "key_terms": {}}
PROMPT;
}

function get_analysis_user_prompt($contract_text, $preferences) {
    $pref_note = '';
    if (!empty($preferences['focus_areas'])) {
        $pref_note = "\n\nADDITIONAL FOCUS AREAS: The reviewer has requested special attention to: " . implode(', ', $preferences['focus_areas']);
    }
    if (!empty($preferences['perspective'])) {
        $pref_note .= "\nREVIEWER PERSPECTIVE: Analyze from the perspective of the {$preferences['perspective']} party.";
    }
    
    return "Please analyze the following contract and return your analysis as JSON:\n\n{$pref_note}\n\n--- CONTRACT TEXT ---\n{$contract_text}\n--- END CONTRACT ---";
}

function call_openai($system_prompt, $user_prompt) {
    $data = [
        'model' => LLM_MODEL,
        'messages' => [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $user_prompt]
        ],
        'temperature' => 0.3,
        'response_format' => ['type' => 'json_object'],
        'max_tokens' => 4096
    ];
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . LLM_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => 'LLM API connection error: ' . $error];
    }
    
    $decoded = json_decode($response, true);
    if ($http_code >= 400) {
        $msg = $decoded['error']['message'] ?? 'Unknown API error';
        return ['error' => "LLM API error ({$http_code}): {$msg}"];
    }
    
    $content = $decoded['choices'][0]['message']['content'] ?? '';
    $analysis = json_decode($content, true);
    
    if (!$analysis) {
        return ['error' => 'Failed to parse LLM response as JSON'];
    }
    
    return ['success' => true, 'analysis' => $analysis];
}

function call_anthropic($system_prompt, $user_prompt) {
    $data = [
        'model' => LLM_MODEL ?: 'claude-sonnet-4-20250514',
        'system' => $system_prompt,
        'messages' => [
            ['role' => 'user', 'content' => $user_prompt]
        ],
        'temperature' => 0.3,
        'max_tokens' => 4096
    ];
    
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-api-key: ' . LLM_API_KEY,
        'anthropic-version: 2023-06-01'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => 'LLM API connection error: ' . $error];
    }
    
    $decoded = json_decode($response, true);
    if ($http_code >= 400) {
        $msg = $decoded['error']['message'] ?? 'Unknown API error';
        return ['error' => "LLM API error ({$http_code}): {$msg}"];
    }
    
    $content = $decoded['content'][0]['text'] ?? '';
    $analysis = json_decode($content, true);
    
    if (!$analysis) {
        return ['error' => 'Failed to parse LLM response as JSON'];
    }
    
    return ['success' => true, 'analysis' => $analysis];
}
