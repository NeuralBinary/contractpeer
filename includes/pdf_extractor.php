<?php
/**
 * ContractPeer - PDF Text Extraction
 * Uses byte-precise stream extraction and zlib decompression (FlateDecode).
 * Handles real-world PDFs from Word, Google Docs, Adobe, etc.
 */

function extract_from_pdf($filepath) {
    $content = file_get_contents($filepath);
    if ($content === false) {
        return ['error' => 'Could not read PDF file'];
    }
    
    if (substr($content, 0, 4) !== '%PDF') {
        return ['error' => 'This file does not appear to be a valid PDF document.'];
    }
    
    $text = '';
    $offset = 0;
    $stream_count = 0;
    $decompressed_count = 0;
    
    // Find all "stream" keywords and extract their content
    while (($pos = strpos($content, 'stream', $offset)) !== false) {
        // Make sure this is a stream keyword, not text content
        // Stream keywords should be preceded by a dictionary with /Length
        $stream_count++;
        
        // Skip past "stream" keyword
        $start = $pos + 6;
        
        // After "stream" there should be \r\n or \n (per PDF spec)
        if ($start < strlen($content) && substr($content, $start, 1) === "\r") $start++;
        if ($start < strlen($content) && substr($content, $start, 1) === "\n") $start++;
        
        // Find "endstream"
        $end = strpos($content, 'endstream', $start);
        if ($end === false) break;
        
        // Trim trailing \r\n or \n before "endstream"
        $raw_end = $end;
        while ($raw_end > $start && (substr($content, $raw_end - 1, 1) === "\n" || substr($content, $raw_end - 1, 1) === "\r")) {
            $raw_end--;
        }
        
        $raw_stream = substr($content, $start, $raw_end - $start);
        
        if (strlen($raw_stream) > 0) {
            // Try to decompress (FlateDecode - zlib format)
            $decompressed = @gzuncompress($raw_stream);
            
            // If that failed, try gzdecode (gzip format)
            if ($decompressed === false) {
                $decompressed = @gzdecode($raw_stream);
            }
            
            // If that also failed, try raw inflate
            if ($decompressed === false) {
                $decompressed = @zlib_decode($raw_stream);
            }
            
            // If all decompression failed, the stream might be uncompressed
            if ($decompressed === false) {
                $decompressed = $raw_stream;
            } else {
                $decompressed_count++;
            }
            
            // Extract text operators from the (decompressed) content stream
            $text .= pdf_extract_text_operators($decompressed);
        }
        
        $offset = $end + 9; // Move past "endstream"
    }
    
    // If stream extraction found little text, try extracting from raw content
    if (strlen(trim($text)) < 50) {
        $text .= pdf_extract_text_operators($content);
    }
    
    // Clean up
    $text = clean_pdf_text($text);
    
    if (strlen($text) < 50) {
        // Check if the PDF has images (likely a scanned document)
        if (preg_match_all('/\/Subtype\s*\/Image/i', $content)) {
            return ['error' => 'This appears to be a scanned PDF (images only, no text layer). Please upload the document as DOCX or TXT, or use an OCR tool to convert it first.'];
        }
        return ['error' => 'Could not extract readable text from this PDF. Please try uploading as DOCX or TXT.'];
    }
    
    return ['text' => $text];
}

/**
 * Extract text from PDF content stream operators
 */
function pdf_extract_text_operators($stream) {
    $text = '';
    
    // Pattern 1: (text) Tj - show text
    // Use a regex that handles escaped parens inside the string
    if (preg_match_all('/\((?:[^\\\\()\\\\]|\\\\.)*\)\s*Tj/', $stream, $matches)) {
        foreach ($matches[0] as $match) {
            if (preg_match('/^\((.*)\)\s*Tj$/s', $match, $str_match)) {
                $text .= pdf_decode_string($str_match[1]) . " ";
            }
        }
    }
    
    // Pattern 2: [(text) -num (text) num] TJ - show text with positioning
    if (preg_match_all('/\[([^\]]*)\]\s*TJ/', $stream, $matches)) {
        foreach ($matches[1] as $arr) {
            if (preg_match_all('/\((?:[^\\\\()\\\\]|\\\\.)*\)/', $arr, $parts)) {
                foreach ($parts[0] as $part) {
                    $str = substr($part, 1, -1);
                    $text .= pdf_decode_string($str);
                }
                $text .= " ";
            }
        }
    }
    
    // Pattern 3: ' operator (move to next line and show text)
    if (preg_match_all('/\((?:[^\\\\()\\\\]|\\\\.)*\)\s*\'/', $stream, $matches)) {
        foreach ($matches[0] as $match) {
            if (preg_match('/^\((.*)\)\s*\'$/s', $match, $str_match)) {
                $text .= pdf_decode_string($str_match[1]) . "\n";
            }
        }
    }
    
    // Pattern 4: " operator (set spacing, move to next line, show text)
    if (preg_match_all('/\((?:[^\\\\()\\\\]|\\\\.)*\)\s*"/', $stream, $matches)) {
        foreach ($matches[0] as $match) {
            if (preg_match('/^\((.*)\)\s*"$/s', $match, $str_match)) {
                $text .= pdf_decode_string($str_match[1]) . "\n";
            }
        }
    }
    
    return $text;
}

/**
 * Decode a PDF string (handle escape sequences and hex encoding)
 */
function pdf_decode_string($str) {
    $str = trim($str);
    
    // Handle hex strings <...>
    if (preg_match('/^<(.*)>$/s', $str, $hex_match)) {
        $hex = preg_replace('/\s/', '', $hex_match[1]);
        $decoded = '';
        for ($i = 0; $i + 1 < strlen($hex); $i += 2) {
            $decoded .= chr(hexdec(substr($hex, $i, 2)));
        }
        if (strlen($decoded) >= 2 && ord($decoded[0]) === 0xFE && ord($decoded[1]) === 0xFF) {
            $decoded = mb_convert_encoding(substr($decoded, 2), 'UTF-8', 'UTF-16BE');
        }
        return $decoded;
    }
    
    // Handle escape sequences in literal strings
    $result = '';
    $len = strlen($str);
    $i = 0;
    while ($i < $len) {
        $char = $str[$i];
        if ($char === '\\') {
            $i++;
            if ($i >= $len) break;
            $next = $str[$i];
            switch ($next) {
                case 'n': $result .= "\n"; break;
                case 'r': $result .= "\r"; break;
                case 't': $result .= "\t"; break;
                case 'b': $result .= "\x08"; break;
                case 'f': $result .= "\x0c"; break;
                case '(': $result .= "("; break;
                case ')': $result .= ")"; break;
                case '\\': $result .= "\\"; break;
                case '0': case '1': case '2': case '3':
                case '4': case '5': case '6': case '7':
                    $octal = $next;
                    if ($i + 1 < $len && isset($str[$i+1]) && $str[$i+1] >= '0' && $str[$i+1] <= '7') {
                        $octal .= $str[++$i];
                        if ($i + 1 < $len && isset($str[$i+1]) && $str[$i+1] >= '0' && $str[$i+1] <= '7') {
                            $octal .= $str[++$i];
                        }
                    }
                    $result .= chr(octdec($octal));
                    break;
                default:
                    if ($next === "\n" || $next === "\r") {
                        // Line continuation - skip
                    } else {
                        $result .= $next;
                    }
            }
        } else {
            $result .= $char;
        }
        $i++;
    }
    
    return $result;
}

/**
 * Clean up extracted PDF text
 */
function clean_pdf_text($text) {
    $text = preg_replace('/[ \t]+/', ' ', $text);
    $text = preg_replace('/\n{3,}/', "\n\n", $text);
    $text = preg_replace('/\s+([.,;:!?])/', '$1', $text);
    $text = trim($text);
    
    if (strlen($text) > 0) {
        $printable = 0;
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $ord = ord($text[$i]);
            if (($ord >= 32 && $ord <= 126) || $ord === 10 || $ord === 13 || $ord === 9) {
                $printable++;
            }
        }
        if ($printable / strlen($text) < 0.5) {
            return '';
        }
    }
    
    return $text;
}
