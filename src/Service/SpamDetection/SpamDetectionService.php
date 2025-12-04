<?php
namespace App\Service\SpamDetection;

class SpamDetectionService
{
    private const SPAM_THRESHOLD = 50; // Adjust based on your needs
    
    public function calculateSpamScore(array $formData): int
    {
        $score = 0;
        
        // Check text fields for gibberish
        $textFields = ['contactPerson', 'churchRegistrant', 'dietaryConcerns', 'comments'];
        foreach ($textFields as $field) {
            if (isset($formData[$field]) && !empty($formData[$field])) {
                $score += $this->getTextGibberishScore($formData[$field]);
            }
        }
        
        // Check for suspicious patterns
        $score += $this->checkSuspiciousPatterns($formData);
        
        // Check payment inconsistencies
        $score += $this->checkPaymentIssues($formData);
        
        return min($score, 100); // Cap at 100
    }
    
    public function getSpamDetails(array $formData): array
    {
        $details = [];
        $totalScore = 0;
        
        // Text gibberish analysis
        $textFields = ['contactPerson', 'primaryChurch', 'concerns?', 'questions'];
        foreach ($textFields as $field) {
            if (isset($formData[$field]) && !empty($formData[$field])) {
                $score = $this->getTextGibberishScore($formData[$field]);
                if ($score > 0) {
                    $details[$field] = [
                        'score' => $score,
                        'reasons' => $this->getGibberishReasons($formData[$field])
                    ];
                    $totalScore += $score;
                }
            }
        }

        // Address analysis
        $addressScore = $this->checkAddressIssues($formData);
        
        if ($addressScore > 0) {
            $details['address'] = [
                'score' => $addressScore,
                'reasons' => $this->getAddressReasons($formData)
            ];
            $totalScore += $addressScore;
        }
        
        // Pattern analysis
        $patternScore = $this->checkSuspiciousPatterns($formData);
        if ($patternScore > 0) {
            $details['patterns'] = [
                'score' => $patternScore,
                'reasons' => $this->getPatternReasons($formData)
            ];
            $totalScore += $patternScore;
        }
        
        return [
            'total_score' => min($totalScore, 100),
            'is_spam' => $totalScore >= self::SPAM_THRESHOLD,
            'details' => $details
        ];
    }
    
    private function getTextGibberishScore(string $text): int
    {
        $score = 0;
        
        // Low vowel ratio: +20 points
        if ($this->hasLowVowelRatio($text)) {
            $score += 20;
        }
        
        // Excessive consonant clusters: +15 points
        if ($this->hasExcessiveConsonantClusters($text)) {
            $score += 15;
        }
        
        // Character repetition: +10 points
        if ($this->hasExcessiveRepetition($text)) {
            $score += 10;
        }
        
        // Suspicious casing: +15 points
        if ($this->hasSuspiciousCasing($text)) {
            $score += 15;
        }
        
        // No spaces in long text: +10 points
        if (strlen($text) > 15 && strpos($text, ' ') === false) {
            $score += 10;
        }
        
        return $score;
    }

    private function checkAddressIssues(array $formData): int
    {
        $score = 0;
        // Check if address fields exist
        $hasAddress = isset($formData['line1']) || isset($formData['city']) 
                    || isset($formData['state']) || isset($formData['zipcode']);
        
        if (!$hasAddress) {
            return 0; // No address to check
        }
        
        // Individual field gibberish checks
        $addressFields = ['line1', 'line2', 'city'];
        foreach ($addressFields as $field) {
            if (isset($formData[$field]) && !empty($formData[$field])) {
                $fieldScore = $this->getTextGibberishScore($formData[$field]);
                if ($fieldScore > 30) { // Higher threshold for addresses
                    $score += 20;
                }
            }
        }
        
        // City name validation
        if (isset($formData['city']) && !empty($formData['city'])) {
            if ($this->isInvalidCityName($formData['city'])) {
                $score += 15;
            }
        }
        
        // State validation (if using US states)
        if (isset($formData['state']) && !empty($formData['state'])) {
            if (!$this->isValidUSState($formData['state'])) {
                $score += 20;
            }
        }
        
        // Zipcode validation
        if (isset($formData['zipcode']) && !empty($formData['zipcode'])) {
            if (!$this->isValidZipcode($formData['zipcode'])) {
                $score += 15;
            }
        }
        
        // Check if all address fields are gibberish
        $allGibberish = true;
        foreach (['line1', 'city', 'state'] as $field) {
            if (isset($formData[$field]) && !empty($formData[$field])) {
                if ($this->getTextGibberishScore($formData[$field]) < 30) {
                    $allGibberish = false;
                    break;
                }
            }
        }
        if ($allGibberish) {
            $score += 25;
        }
        
        // Incomplete address (has some fields but missing critical ones)
        if ($this->hasIncompleteAddress($formData)) {
            $score += 10;
        }
        
        return $score;
    }

    private function isInvalidCityName(string $city): bool
    {
        // City names should have vowels and reasonable length
        if (strlen($city) < 2) {
            return true;
        }
        
        // Check for gibberish
        if ($this->hasLowVowelRatio($city) || $this->hasExcessiveConsonantClusters($city)) {
            return true;
        }
        
        // City names shouldn't have numbers (except rare cases like "29 Palms")
        if (preg_match('/\d/', $city) && !preg_match('/^\d+\s+[a-z]+$/i', $city)) {
            return true;
        }
        
        // City names should have spaces for multi-word cities or be single word
        // But shouldn't be one long string of random caps like "WmzBsAFiEdvWIKdqIJMiIhTU"
        if (strlen($city) > 15 && strpos($city, ' ') === false && $this->hasSuspiciousCasing($city)) {
            return true;
        }
        
        return false;
    }

    private function isValidUSState(string $state): bool
    {
        $validStates = [
            // Full names
            'alabama', 'alaska', 'arizona', 'arkansas', 'california', 'colorado',
            'connecticut', 'delaware', 'florida', 'georgia', 'hawaii', 'idaho',
            'illinois', 'indiana', 'iowa', 'kansas', 'kentucky', 'louisiana',
            'maine', 'maryland', 'massachusetts', 'michigan', 'minnesota',
            'mississippi', 'missouri', 'montana', 'nebraska', 'nevada',
            'new hampshire', 'new jersey', 'new mexico', 'new york',
            'north carolina', 'north dakota', 'ohio', 'oklahoma', 'oregon',
            'pennsylvania', 'rhode island', 'south carolina', 'south dakota',
            'tennessee', 'texas', 'utah', 'vermont', 'virginia', 'washington',
            'west virginia', 'wisconsin', 'wyoming',
            // Abbreviations
            'al', 'ak', 'az', 'ar', 'ca', 'co', 'ct', 'de', 'fl', 'ga', 'hi',
            'id', 'il', 'in', 'ia', 'ks', 'ky', 'la', 'me', 'md', 'ma', 'mi',
            'mn', 'ms', 'mo', 'mt', 'ne', 'nv', 'nh', 'nj', 'nm', 'ny', 'nc',
            'nd', 'oh', 'ok', 'or', 'pa', 'ri', 'sc', 'sd', 'tn', 'tx', 'ut',
            'vt', 'va', 'wa', 'wv', 'wi', 'wy', 'dc'
        ];
        
        return in_array(strtolower(trim($state)), $validStates);
    }

    private function isValidZipcode(string $zipcode): bool
    {
        $zipcode = trim($zipcode);
        
        // US Zipcode: 5 digits or 5+4 format
        if (preg_match('/^\d{5}(-\d{4})?$/', $zipcode)) {
            return true;
        }
        
        // Check if it's all gibberish instead of numbers
        if ($this->getTextGibberishScore($zipcode) > 30) {
            return false;
        }
        
        return false;
    }

    private function hasIncompleteAddress(array $formData): bool
    {
        // Has some address fields but missing critical ones
        $hasAnyAddress = !empty($formData['line1']) || !empty($formData['city']) 
                        || !empty($formData['state']) || !empty($formData['zipcode']);
        
        if (!$hasAnyAddress) {
            return false; // No address at all is fine
        }
        
        // If they started an address, they should have at least city, state, zip
        $hasCriticalFields = !empty($formData['city']) 
                            && !empty($formData['state']) 
                            && !empty($formData['zipcode']);
        
        return !$hasCriticalFields;
    }

    private function getAddressReasons(array $formData): array
    {
        $reasons = [];
        
        // Check individual fields
        $addressFields = ['line1', 'line2', 'city'];
        foreach ($addressFields as $field) {
            if (isset($formData[$field]) && !empty($formData[$field])) {
                $fieldScore = $this->getTextGibberishScore($formData[$field]);
                if ($fieldScore > 30) {
                    $reasons[] = ucfirst($field) . ' contains gibberish';
                }
            }
        }
        
        if (isset($formData['city']) && !empty($formData['city']) 
            && $this->isInvalidCityName($formData['city'])) {
            $reasons[] = 'Invalid city name format';
        }
        
        if (isset($formData['state']) && !empty($formData['state']) 
            && !$this->isValidUSState($formData['state'])) {
            $reasons[] = 'Invalid state';
        }
        
        if (isset($formData['zipcode']) && !empty($formData['zipcode']) 
            && !$this->isValidZipcode($formData['zipcode'])) {
            $reasons[] = 'Invalid zipcode format';
        }
        
        $allGibberish = true;
        foreach (['line1', 'city', 'state'] as $field) {
            if (isset($formData[$field]) && !empty($formData[$field])) {
                if ($this->getTextGibberishScore($formData[$field]) < 30) {
                    $allGibberish = false;
                    break;
                }
            }
        }
        if ($allGibberish) {
            $reasons[] = 'All address fields contain gibberish';
        }
        
        if ($this->hasIncompleteAddress($formData)) {
            $reasons[] = 'Incomplete address information';
        }
        
        return $reasons;
    }
    
    //TODO split into seperate methods
    private function checkSuspiciousPatterns(array $formData): int
    {
        $score = 0;
        
        // All fields are gibberish: +20 points
        $gibberishCount = 0;
        $textFields = ['contactPerson', 'primaryChurch', 'concerns?'];
        foreach ($textFields as $field) {
            if (isset($formData[$field]) && $this->getTextGibberishScore($formData[$field]) > 20) {
                $gibberishCount++;
            }
        }
        if ($gibberishCount >= 2) {
            $score += 20;
        }
        
        // Suspicious email pattern (common spam domains)
        if (isset($formData['email'])) {
            $suspiciousDomains = ['tempmail', 'guerrillamail', '10minutemail', 'throwaway'];
            foreach ($suspiciousDomains as $domain) {
                if (stripos($formData['email'], $domain) !== false) {
                    $score += 15;
                    break;
                }
            }
        }
        
        return $score;
    }
    
    private function getGibberishReasons(string $text): array
    {
        $reasons = [];
        
        if ($this->hasLowVowelRatio($text)) {
            $reasons[] = 'Unusual vowel ratio';
        }
        if ($this->hasExcessiveConsonantClusters($text)) {
            $reasons[] = 'Too many consonants together';
        }
        if ($this->hasExcessiveRepetition($text)) {
            $reasons[] = 'Repeated characters';
        }
        if ($this->hasSuspiciousCasing($text)) {
            $reasons[] = 'Random capitalization pattern';
        }
        if (strlen($text) > 15 && strpos($text, ' ') === false) {
            $reasons[] = 'No spaces in long text';
        }
        
        return $reasons;
    }
    
    //TODO split into seperate methods
    private function getPatternReasons(array $formData): array
    {
        $reasons = [];
        
        $gibberishCount = 0;
        $textFields = ['contactPerson', 'churchRegistrant', 'dietaryConcerns'];
        foreach ($textFields as $field) {
            if (isset($formData[$field]) && $this->getTextGibberishScore($formData[$field]) > 20) {
                $gibberishCount++;
            }
        }
        if ($gibberishCount >= 2) {
            $reasons[] = 'Multiple fields contain gibberish';
        }
        
        if (isset($formData['email'])) {
            $suspiciousDomains = ['tempmail', 'guerrillamail', '10minutemail', 'throwaway'];
            foreach ($suspiciousDomains as $domain) {
                if (stripos($formData['email'], $domain) !== false) {
                    $reasons[] = 'Temporary email address detected';
                    break;
                }
            }
        }
        
        return $reasons;
    }
    
    // Detection helper methods
    private function hasLowVowelRatio(string $text): bool
    {
        $length = strlen($text);
        if ($length < 3) return false;
        
        $vowels = preg_match_all('/[aeiou]/i', $text);
        $ratio = $vowels / $length;
        
        return $ratio < 0.15 || $ratio > 0.70;
    }
    
    private function hasExcessiveConsonantClusters(string $text): bool
    {
        return preg_match('/[bcdfghjklmnpqrstvwxyz]{4,}/i', $text) === 1;
    }
    
    private function hasExcessiveRepetition(string $text): bool
    {
        return preg_match('/(.)\1{2,}/', $text) === 1;
    }
    
    private function hasSuspiciousCasing(string $text): bool
    {
        if (strlen($text) < 5) return false;
        
        $changes = 0;
        for ($i = 1; $i < strlen($text); $i++) {
            if (ctype_alpha($text[$i]) && ctype_alpha($text[$i-1])) {
                if (ctype_upper($text[$i]) !== ctype_upper($text[$i-1])) {
                    $changes++;
                }
            }
        }
        
        return ($changes / strlen($text)) > 0.3;
    }
}