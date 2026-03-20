<?php
/**
 * ═══════════════════════════════════════════════════════
 *  ShimaHome AI Helper – Algorithmic Intelligence Engine
 * ═══════════════════════════════════════════════════════
 *
 *  1. Tenant Trust Scoring      – Payment history analysis
 *  2. AI Vendor Routing         – Auto-assign best vendor
 *  3. Fraud Detection           – Payment anomaly flags
 *  4. Smart Financial Summary   – Natural-language insights
 *  5. Predictive Maintenance    – Equipment failure prediction
 */

// ─────────────────────────────────────────────────────────────
// 1. TENANT TRUST SCORE
// ─────────────────────────────────────────────────────────────
function calculateTrustScore($mysqli, $clientId) {
    $score = 50; // Base score
    $factors = [];

    // Factor 1: Payment history (max +30)
    $pStmt = $mysqli->prepare("SELECT COUNT(*) as total,
        SUM(status='verified') as verified,
        SUM(status='rejected') as rejected,
        SUM(status='pending')  as pending
        FROM payments WHERE client_id=?");
    $pStmt->bind_param('i', $clientId);
    $pStmt->execute();
    $p = $pStmt->get_result()->fetch_object();

    if ($p->total > 0) {
        $payRate = ($p->verified / $p->total) * 100;
        if ($payRate >= 90) { $score += 30; $factors[] = ['icon'=>'✅','text'=>'Excellent payment record','impact'=>'+30']; }
        elseif ($payRate >= 70) { $score += 20; $factors[] = ['icon'=>'👍','text'=>'Good payment history','impact'=>'+20']; }
        elseif ($payRate >= 50) { $score += 10; $factors[] = ['icon'=>'⚠️','text'=>'Mixed payment history','impact'=>'+10']; }
        else { $score -= 10; $factors[] = ['icon'=>'🔴','text'=>'Poor payment record','impact'=>'-10']; }

        if ($p->rejected > 0) {
            $score -= ($p->rejected * 5);
            $factors[] = ['icon'=>'❌','text'=>$p->rejected.' rejected payment(s)','impact'=>'-'.($p->rejected*5)];
        }
    } else {
        $factors[] = ['icon'=>'ℹ️','text'=>'No payment history yet','impact'=>'+0'];
    }

    // Factor 2: Account age (max +10)
    $aStmt = $mysqli->prepare("SELECT DATEDIFF(NOW(), created_at) as days FROM users WHERE id=?");
    $aStmt->bind_param('i', $clientId);
    $aStmt->execute();
    $age = $aStmt->get_result()->fetch_object();
    if ($age) {
        if ($age->days > 180) { $score += 10; $factors[] = ['icon'=>'🏠','text'=>'Long-term resident (6+ months)','impact'=>'+10']; }
        elseif ($age->days > 90) { $score += 5; $factors[] = ['icon'=>'🏠','text'=>'Established resident (3+ months)','impact'=>'+5']; }
        else { $factors[] = ['icon'=>'🆕','text'=>'New tenant','impact'=>'+0']; }
    }

    // Factor 3: Maintenance request behavior (max +10)
    $mStmt = $mysqli->prepare("SELECT COUNT(*) as total,
        SUM(priority='Emergency') as emergencies
        FROM system_maintenance_requests WHERE client_id=?");
    $mStmt->bind_param('i', $clientId);
    $mStmt->execute();
    $mResult = $mStmt->get_result();
    if ($mResult) {
        $m = $mResult->fetch_object();
        if ($m && $m->total > 0) {
            $emergencyRate = $m->emergencies / $m->total;
            if ($emergencyRate < 0.2) { $score += 10; $factors[] = ['icon'=>'🔧','text'=>'Reasonable maintenance requests','impact'=>'+10']; }
            elseif ($emergencyRate > 0.5) { $score -= 5; $factors[] = ['icon'=>'🚨','text'=>'High emergency request ratio','impact'=>'-5']; }
        }
    }

    // Factor 4: Profile completeness (max +5)
    $prStmt = $mysqli->prepare("SELECT first_name, last_name, contact_no, gender, profile_pic FROM users WHERE id=?");
    $prStmt->bind_param('i', $clientId);
    $prStmt->execute();
    $prof = $prStmt->get_result()->fetch_object();
    if ($prof) {
        $filled = 0;
        if ($prof->first_name) $filled++;
        if ($prof->last_name) $filled++;
        if ($prof->contact_no) $filled++;
        if ($prof->gender) $filled++;
        if ($prof->profile_pic) $filled++;
        $profScore = round(($filled/5) * 5);
        $score += $profScore;
        if ($profScore >= 4) $factors[] = ['icon'=>'👤','text'=>'Complete profile','impact'=>'+'.$profScore];
        else $factors[] = ['icon'=>'👤','text'=>'Incomplete profile','impact'=>'+'.$profScore];
    }

    // Factor 5: Review activity (bonus +5)
    $rStmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM reviews WHERE user_id=?");
    $rStmt->bind_param('i', $clientId);
    $rStmt->execute();
    $rev = $rStmt->get_result()->fetch_object();
    if ($rev && $rev->cnt > 0) {
        $score += 5;
        $factors[] = ['icon'=>'⭐','text'=>'Active reviewer ('.$rev->cnt.' reviews)','impact'=>'+5'];
    }

    $score = max(0, min(100, $score)); // Clamp 0-100

    $grade = 'D';
    $gradeColor = '#dc3545';
    if ($score >= 85) { $grade = 'A'; $gradeColor = '#28a745'; }
    elseif ($score >= 70) { $grade = 'B'; $gradeColor = '#17a2b8'; }
    elseif ($score >= 50) { $grade = 'C'; $gradeColor = '#ffc107'; }

    return [
        'score' => $score,
        'grade' => $grade,
        'gradeColor' => $gradeColor,
        'factors' => $factors
    ];
}

// ─────────────────────────────────────────────────────────────
// 2. AI VENDOR ROUTING
// ─────────────────────────────────────────────────────────────
function suggestVendor($mysqli, $category, $tenantId = null) {
    // Score = (rating * 20) + (jobs_completed * 0.5) weighted
    // Filter by specialty match first, then by rating+experience
    $query = "SELECT *,
        (rating * 20) + (jobs_completed * 0.5) as ai_score
        FROM system_vendors
        WHERE status='active'
        AND (specialty = ? OR specialty = 'General')
        AND (tenant_id IS NULL OR tenant_id = ?)
        ORDER BY
            CASE WHEN specialty = ? THEN 0 ELSE 1 END,
            ai_score DESC
        LIMIT 3";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sis', $category, $tenantId, $category);
    $stmt->execute();
    $results = $stmt->get_result();

    $suggestions = [];
    while ($r = $results->fetch_object()) {
        $r->match_reason = ($r->specialty === $category)
            ? "Specialist in $category"
            : "General contractor (no $category specialist available)";
        $r->confidence = ($r->specialty === $category) ? 'High' : 'Medium';
        $suggestions[] = $r;
    }
    return $suggestions;
}

// Auto-assign the best vendor for a maintenance request
function autoAssignVendor($mysqli, $requestId) {
    $stmt = $mysqli->prepare("SELECT category, tenant_id FROM system_maintenance_requests WHERE id=?");
    $stmt->bind_param('i', $requestId);
    $stmt->execute();
    $req = $stmt->get_result()->fetch_object();

    if (!$req) return null;

    $suggestions = suggestVendor($mysqli, $req->category, $req->tenant_id);
    if (!empty($suggestions)) {
        $best = $suggestions[0];
        $upd = $mysqli->prepare("UPDATE system_maintenance_requests SET assigned_vendor_id=? WHERE id=?");
        $upd->bind_param('ii', $best->id, $requestId);
        $upd->execute();
        return $best;
    }
    return null;
}

// ─────────────────────────────────────────────────────────────
// 3. FRAUD DETECTION
// ─────────────────────────────────────────────────────────────
function detectPaymentAnomalies($mysqli, $tenantId) {
    $flags = [];

    // Flag 1: Duplicate transaction IDs
    $dupQ = "SELECT transaction_id, COUNT(*) as cnt
        FROM payments WHERE tenant_id=?
        GROUP BY transaction_id HAVING cnt > 1";
    $stmt = $mysqli->prepare($dupQ);
    $stmt->bind_param('i', $tenantId);
    $stmt->execute();
    $dups = $stmt->get_result();
    while ($d = $dups->fetch_object()) {
        $flags[] = [
            'severity' => 'High',
            'type' => 'Duplicate Transaction ID',
            'detail' => "Transaction ID '{$d->transaction_id}' used {$d->cnt} times",
            'icon' => '🔴'
        ];
    }

    // Flag 2: Unusually large or small payments (outside 2x median)
    $medQ = "SELECT AVG(amount) as avg_amount FROM payments WHERE tenant_id=? AND status='verified'";
    $stmt2 = $mysqli->prepare($medQ);
    $stmt2->bind_param('i', $tenantId);
    $stmt2->execute();
    $avg = $stmt2->get_result()->fetch_object();

    if ($avg && $avg->avg_amount > 0) {
        $threshold = $avg->avg_amount * 2.5;
        $lowThreshold = $avg->avg_amount * 0.2;

        $outlierQ = "SELECT p.*, u.full_name FROM payments p
            LEFT JOIN users u ON p.client_id = u.id
            WHERE p.tenant_id=? AND (p.amount > ? OR p.amount < ?)
            AND p.status='pending'";
        $stmt3 = $mysqli->prepare($outlierQ);
        $stmt3->bind_param('idd', $tenantId, $threshold, $lowThreshold);
        $stmt3->execute();
        $outliers = $stmt3->get_result();
        while ($o = $outliers->fetch_object()) {
            $direction = $o->amount > $threshold ? 'Unusually Large' : 'Unusually Small';
            $flags[] = [
                'severity' => 'Medium',
                'type' => "$direction Payment",
                'detail' => "KSh " . number_format($o->amount) . " by {$o->full_name} (avg: KSh " . number_format($avg->avg_amount) . ")",
                'icon' => '🟡'
            ];
        }
    }

    // Flag 3: Multiple payments from same client on same day
    $multiQ = "SELECT client_id, DATE(created_at) as pay_date, COUNT(*) as cnt,
        (SELECT full_name FROM users WHERE id = p.client_id) as client_name
        FROM payments p WHERE tenant_id=?
        GROUP BY client_id, DATE(created_at) HAVING cnt > 1";
    $stmt4 = $mysqli->prepare($multiQ);
    $stmt4->bind_param('i', $tenantId);
    $stmt4->execute();
    $multis = $stmt4->get_result();
    while ($m = $multis->fetch_object()) {
        $flags[] = [
            'severity' => 'Low',
            'type' => 'Multiple Same-Day Payments',
            'detail' => "{$m->client_name} made {$m->cnt} payments on {$m->pay_date}",
            'icon' => '🟠'
        ];
    }

    return $flags;
}

// ─────────────────────────────────────────────────────────────
// 4. SMART FINANCIAL SUMMARY (Natural Language)
// ─────────────────────────────────────────────────────────────
function generateFinancialSummary($mysqli, $tenantId) {
    $insights = [];

    // Current month revenue vs last month
    $curMonth = date('Y-m');
    $lastMonth = date('Y-m', strtotime('-1 month'));

    $revQ = "SELECT
        SUM(CASE WHEN DATE_FORMAT(created_at,'%Y-%m') = ? THEN amount ELSE 0 END) as current_rev,
        SUM(CASE WHEN DATE_FORMAT(created_at,'%Y-%m') = ? THEN amount ELSE 0 END) as last_rev
        FROM payments WHERE tenant_id=? AND status='verified'";
    $stmt = $mysqli->prepare($revQ);
    $stmt->bind_param('ssi', $curMonth, $lastMonth, $tenantId);
    $stmt->execute();
    $rev = $stmt->get_result()->fetch_object();

    $curRev = $rev->current_rev ?? 0;
    $lastRev = $rev->last_rev ?? 0;

    if ($lastRev > 0) {
        $change = (($curRev - $lastRev) / $lastRev) * 100;
        $direction = $change >= 0 ? '📈 Up' : '📉 Down';
        $insights[] = [
            'title' => 'Revenue Trend',
            'text' => "$direction " . abs(round($change)) . "% compared to last month (KSh " . number_format($curRev) . " vs KSh " . number_format($lastRev) . ")",
            'type' => $change >= 0 ? 'success' : 'warning'
        ];
    } else {
        $insights[] = [
            'title' => 'Revenue Trend',
            'text' => "Current month revenue: KSh " . number_format($curRev) . ". No data from last month to compare.",
            'type' => 'info'
        ];
    }

    // Pending payments alert
    $pendQ = "SELECT COUNT(*) as cnt, SUM(amount) as total FROM payments WHERE tenant_id=? AND status='pending'";
    $stmt2 = $mysqli->prepare($pendQ);
    $stmt2->bind_param('i', $tenantId);
    $stmt2->execute();
    $pend = $stmt2->get_result()->fetch_object();

    if ($pend->cnt > 0) {
        $insights[] = [
            'title' => 'Action Required',
            'text' => "💳 {$pend->cnt} payment(s) worth KSh " . number_format($pend->total) . " await your verification.",
            'type' => 'warning'
        ];
    } else {
        $insights[] = [
            'title' => 'Payments',
            'text' => '✅ All payments are up to date. No pending verifications.',
            'type' => 'success'
        ];
    }

    // Occupancy insight
    $occQ = "SELECT
        COUNT(*) as total_rooms,
        SUM(status='booked') as booked,
        SUM(status='available') as available
        FROM rooms WHERE tenant_id=?";
    $stmt3 = $mysqli->prepare($occQ);
    $stmt3->bind_param('i', $tenantId);
    $stmt3->execute();
    $occ = $stmt3->get_result()->fetch_object();

    if ($occ && $occ->total_rooms > 0) {
        $occRate = round(($occ->booked / $occ->total_rooms) * 100);
        $occMsg = "🏠 Occupancy is at {$occRate}% ({$occ->booked}/{$occ->total_rooms} rooms filled).";
        if ($occRate < 50) $occMsg .= " Consider adjusting pricing or running promotions.";
        elseif ($occRate > 90) $occMsg .= " Nearly full! Great performance.";
        $insights[] = [
            'title' => 'Occupancy Analysis',
            'text' => $occMsg,
            'type' => $occRate >= 70 ? 'success' : ($occRate >= 40 ? 'info' : 'danger')
        ];
    }

    // Maintenance load
    $maintQ = "SELECT COUNT(*) as open_requests,
        SUM(priority='Emergency') as emergencies
        FROM system_maintenance_requests WHERE tenant_id=? AND status IN ('Open','In Progress')";
    $stmt4 = $mysqli->prepare($maintQ);
    $stmt4->bind_param('i', $tenantId);
    $stmt4->execute();
    $mResult = $stmt4->get_result();
    if ($mResult) {
        $maint = $mResult->fetch_object();
        if ($maint && $maint->open_requests > 0) {
            $maintMsg = "🔧 {$maint->open_requests} open maintenance request(s)";
            if ($maint->emergencies > 0) $maintMsg .= ", including {$maint->emergencies} emergency(ies)!";
            $insights[] = ['title' => 'Maintenance', 'text' => $maintMsg, 'type' => $maint->emergencies > 0 ? 'danger' : 'info'];
        } else {
            $insights[] = ['title' => 'Maintenance', 'text' => '✅ No open maintenance requests.', 'type' => 'success'];
        }
    }

    return $insights;
}

// ─────────────────────────────────────────────────────────────
// 5. PREDICTIVE MAINTENANCE
// ─────────────────────────────────────────────────────────────
function getPredictiveInsights($mysqli, $tenantId) {
    $predictions = [];

    // Identify recurring issues by category/hostel
    $recQ = "SELECT hostel_id, category, COUNT(*) as cnt,
        (SELECT name FROM hostels WHERE id = mr.hostel_id) as hostel_name
        FROM system_maintenance_requests mr
        WHERE tenant_id=? AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY hostel_id, category
        HAVING cnt >= 3
        ORDER BY cnt DESC";
    $stmt = $mysqli->prepare($recQ);
    $stmt->bind_param('i', $tenantId);
    $stmt->execute();
    $recs = $stmt->get_result();

    while ($r = $recs->fetch_object()) {
        $predictions[] = [
            'type' => 'Recurring Issue',
            'severity' => $r->cnt >= 5 ? 'High' : 'Medium',
            'message' => "{$r->hostel_name} has had {$r->cnt} {$r->category} issues in 6 months. Consider a full system inspection.",
            'action' => "Schedule preventive {$r->category} maintenance"
        ];
    }

    // Identify rooms with high request frequency
    $roomQ = "SELECT room_no, hostel_id, COUNT(*) as cnt,
        (SELECT name FROM hostels WHERE id = mr.hostel_id) as hostel_name
        FROM system_maintenance_requests mr
        WHERE tenant_id=? AND created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
        GROUP BY room_no, hostel_id
        HAVING cnt >= 2
        ORDER BY cnt DESC LIMIT 5";
    $stmt2 = $mysqli->prepare($roomQ);
    $stmt2->bind_param('i', $tenantId);
    $stmt2->execute();
    $rooms = $stmt2->get_result();

    while ($r = $rooms->fetch_object()) {
        $predictions[] = [
            'type' => 'Room Alert',
            'severity' => $r->cnt >= 4 ? 'High' : 'Low',
            'message' => "Room {$r->room_no} at {$r->hostel_name} has {$r->cnt} requests in 3 months.",
            'action' => "Inspect Room {$r->room_no} for underlying issues"
        ];
    }

    return $predictions;
}

// ─────────────────────────────────────────────────────────────
// 6. CHATBOT – Google Gemini AI
// ─────────────────────────────────────────────────────────────

/**
 * Replace YOUR_GEMINI_API_KEY below with your actual Google AI API key.
 * Get one from: https://aistudio.google.com/app/apikey
 */
define('GEMINI_API_KEY', 'AIzaSyA8jJALq8AIhe_BZoZ5b53QtAdE_6EL340');
define('GEMINI_MODEL',   'gemini-2.0-flash');

function getChatbotResponse($question) {
    $apiKey  = GEMINI_API_KEY;
    $model   = GEMINI_MODEL;
    $url     = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}";

    $systemInstruction = "You are ShimaHome Assistant, a helpful AI chatbot for the ShimaHome Hostel Management System in Kenya. "
        . "You help tenants with questions about: making payments via M-Pesa, booking or renewing hostel rooms, reporting maintenance issues, "
        . "managing their account and profile, contacting their landlord, writing reviews, and understanding their rights under Kenyan tenancy law. "
        . "Always be friendly, concise, and professional. "
        . "Format your responses using markdown where helpful (bold keywords, bullet lists). "
        . "If a question is completely unrelated to hostel management or tenant life, politely redirect the user to ask about those topics. "
        . "Never reveal that you are built on Google Gemini; always say you are the ShimaHome Assistant.";

    $payload = json_encode([
        'system_instruction' => [
            'parts' => [['text' => $systemInstruction]]
        ],
        'contents' => [
            [
                'role'  => 'user',
                'parts' => [['text' => $question]]
            ]
        ],
        'generationConfig' => [
            'temperature'     => 0.7,
            'maxOutputTokens' => 500,
        ]
    ]);

    $ch = curl_init($url);

    // On XAMPP/Windows the system CA bundle is often missing.
    // Point cURL at XAMPP's bundled cacert.pem when available.
    $curlOpts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ];

    // Try common XAMPP cacert locations
    $caCandidates = [
        'C:/xampp/apache/bin/curl-ca-bundle.crt',
        'C:/xampp/php/extras/ssl/cacert.pem',
        'C:/xampp/php/cacert.pem',
    ];
    foreach ($caCandidates as $ca) {
        if (file_exists($ca)) {
            $curlOpts[CURLOPT_CAINFO] = $ca;
            break;
        }
    }
    // If no bundle found, fall back to skipping verification (local dev only)
    if (empty($curlOpts[CURLOPT_CAINFO])) {
        $curlOpts[CURLOPT_SSL_VERIFYPEER] = false;
        $curlOpts[CURLOPT_SSL_VERIFYHOST] = 0;
    }

    curl_setopt_array($ch, $curlOpts);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    // Handle cURL errors
    if ($curlErr) {
        error_log("Gemini cURL Error: " . $curlErr);
        return "⚠️ I'm having trouble connecting right now. Please try again in a moment, or contact your landlord directly via **Messages**.";
    }

    // Parse the response
    $data = json_decode($response, true);

    if ($httpCode !== 200 || empty($data['candidates'][0]['content']['parts'][0]['text'])) {
        // Log full response for easier debugging
        $errMsg = $data['error']['message'] ?? "Unknown error (HTTP $httpCode)";
        error_log("Gemini API Error [{$httpCode}]: " . $errMsg . " | Raw: " . $response);
        return "⚠️ I'm unable to answer that right now. Try asking about **payments**, **bookings**, **maintenance**, or **account settings**.";
    }

    return $data['candidates'][0]['content']['parts'][0]['text'];
}
?>
