<?php
/**
 * ContractPeer - File Processor
 * Extracts text from PDF, DOCX, and TXT files using pure PHP (no external binaries).
 */

function extract_text_from_file($filepath, $filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    switch ($ext) {
        case 'txt':
            return extract_from_txt($filepath);
        case 'pdf':
            require_once __DIR__ . '/pdf_extractor.php';
            return extract_from_pdf($filepath);
        case 'docx':
            return extract_from_docx($filepath);
        case 'doc':
            return extract_from_doc($filepath);
        default:
            return ['error' => 'Unsupported file type. Please upload PDF, DOCX, or TXT files.'];
    }
}

function extract_from_txt($filepath) {
    $content = file_get_contents($filepath);
    if ($content === false) {
        return ['error' => 'Could not read file'];
    }
    // Try to detect encoding and convert to UTF-8
    if (!mb_check_encoding($content, 'UTF-8')) {
        $content = mb_convert_encoding($content, 'UTF-8', 'auto');
    }
    return ['text' => $content];
}

// PDF extraction is handled by includes/pdf_extractor.php
// (loaded on demand by the case 'pdf' branch above)

function extract_from_docx($filepath) {
    // DOCX is a ZIP file containing XML
    if (!class_exists('ZipArchive')) {
        return ['error' => 'ZipArchive not available on server. Please upload as PDF or TXT.'];
    }
    
    $zip = new ZipArchive();
    if ($zip->open($filepath) !== true) {
        return ['error' => 'Could not open DOCX file'];
    }
    
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    
    if ($xml === false) {
        return ['error' => 'Could not read document content from DOCX'];
    }
    
    // Parse XML and extract text
    $text = extract_text_from_docx_xml($xml);
    
    if (strlen($text) < 10) {
        return ['error' => 'No readable text found in DOCX file'];
    }
    
    return ['text' => $text];
}

function extract_text_from_docx_xml($xml) {
    // Register namespaces and extract text from w:t elements
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    
    $text = '';
    $paragraphs = $dom->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'p');
    
    foreach ($paragraphs as $paragraph) {
        $runs = $paragraph->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 't');
        $para_text = '';
        foreach ($runs as $run) {
            $para_text .= $run->nodeValue;
        }
        if ($para_text) {
            $text .= $para_text . "\n";
        }
    }
    
    return trim($text);
}

function extract_from_doc($filepath) {
    // Legacy .doc format - limited pure PHP support
    // Try basic extraction
    $content = file_get_contents($filepath);
    if ($content === false) {
        return ['error' => 'Could not read DOC file'];
    }
    
    // Try to extract readable text (very basic)
    $text = '';
    $len = strlen($content);
    $in_text = false;
    
    for ($i = 0; $i < $len; $i++) {
        $char = $content[$i];
        $ord = ord($char);
        // Printable ASCII range
        if ($ord >= 32 && $ord <= 126) {
            $text .= $char;
            $in_text = true;
        } elseif ($in_text && ($ord === 10 || $ord === 13)) {
            $text .= "\n";
        } elseif ($in_text && $ord === 9) {
            $text .= "\t";
        } elseif ($in_text && $ord < 32 && $ord !== 10 && $ord !== 13) {
            // Non-printable, might end text run
            if (strlen($text) > 3) {
                $text .= ' ';
            } else {
                $text = '';
                $in_text = false;
            }
        }
    }
    
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
    if (strlen($text) < 50) {
        return ['error' => 'Could not extract text from this .doc file. Please convert to DOCX or PDF and try again.'];
    }
    
    return ['text' => $text];
}

function save_uploaded_file($file) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['error' => 'No file uploaded'];
    }
    
    $max_size = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $max_size) {
        return ['error' => 'File too large. Maximum size is 10MB.'];
    }
    
    $allowed = ['txt', 'pdf', 'docx', 'doc'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return ['error' => 'Unsupported file type. Please upload PDF, DOCX, or TXT.'];
    }
    
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
    
    $safe_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
    $stored_name = uniqid('contract_') . '_' . $safe_name;
    $stored_path = UPLOAD_PATH . '/' . $stored_name;
    
    if (!move_uploaded_file($file['tmp_name'], $stored_path)) {
        return ['error' => 'Failed to save uploaded file'];
    }
    
    return ['path' => $stored_path, 'filename' => $file['name']];
}

function cleanup_uploaded_file($filepath) {
    if (file_exists($filepath)) {
        @unlink($filepath);
    }
}
