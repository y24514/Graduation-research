<?php
// エラー表示を有効にする（デバッグ時のみ。本番では off に推奨）
ini_set('display_errors', 0); 
header('Content-Type: application/json; charset=UTF-8');

// --- 1. 設定項目 ---
$api_key = 'AIzaSyBY5MSyB0vD3JaJuscbtSltGW85_i6_cK4'; // ★ここにあなたのGemini APIキーを貼り付けてください
$model_id = 'gemini-2.0-flash-latest';   // 指定された最新モデルID

// データベース接続設定
$host   = 'localhost';
$dbname = 'sportsdata';
$user   = getenv('DB_USER') ?: 'sportsdata_user';
$pass   = getenv('DB_PASS') ?: 'fujidai14';

// --- 2. リクエスト解析 ---
$gameId = isset($_POST['game_id']) ? intval($_POST['game_id']) : null;

if (!$gameId) {
    echo json_encode(['success' => false, 'error' => '試合IDが正しく送信されていません。']);
    exit;
}

try {
    // --- 3. データベース接続 ---
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- 4. 試合データの取得 ---
    // 基本情報
    $stmt = $db->prepare("SELECT * FROM tennis_games WHERE id = ?");
    $stmt->execute([$gameId]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$game) {
        throw new Exception("対象の試合データが見つかりません。");
    }

    // 詳細アクション（スタッツ用）
    $stmtAct = $db->prepare("SELECT * FROM tennis_actions WHERE game_id = ? ORDER BY id ASC");
    $stmtAct->execute([$gameId]);
    $history = $stmtAct->fetchAll(PDO::FETCH_ASSOC);

    // --- 5. プロンプト（AIへの指示文）作成 ---
    $prompt = "あなたはプロのテニスコーチです。以下の試合データを分析し、選手へ具体的で熱いアドバイスを日本語で作成してください。\n\n";
    $prompt .= "【対戦】 {$game['team_a']} VS {$game['team_b']} \n";
    $prompt .= "【最終ゲームスコア】 {$game['games_a']} - {$game['games_b']} \n";
    $prompt .= "【ポイント詳細履歴】\n";
    
    foreach ($history as $h) {
        $pName = $h['player_name'];
        $act   = $h['action_type'];
        $sA    = $h['score_a'];
        $sB    = $h['score_b'];
        $prompt .= "- {$pName}の{$act} (スコア: {$sA}-{$sB})\n";
    }

    $prompt .= "\n上記データを踏まえ、以下の3点について150文字〜200文字程度で回答してください。\n";
    $prompt .= "1. 試合の決定的な分かれ目\n";
    $prompt .= "2. 勝者の良かった点、敗者の改善すべき点\n";
    $prompt .= "3. 次の練習で意識すべき具体的なショット";

    // --- 6. Gemini API 呼び出し ---
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model_id}:generateContent?key={$api_key}";
    
    $postData = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.7,
            "maxOutputTokens" => 800
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // 環境によってはfalseにする必要があるが推奨はtrue

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        throw new Exception("API通信エラー: " . $curl_error);
    }

    $result = json_decode($response, true);
    
    // APIからのエラー回答をチェック
    if (isset($result['error'])) {
        throw new Exception("Gemini API Error: " . $result['error']['message']);
    }

    // AIの回答テキストを抽出
    $ai_comment = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

    if (!$ai_comment) {
        throw new Exception("AIからの回答が空でした。レスポンスを確認してください。");
    }

    // --- 7. DBにAIコメントを保存 ---
    $update = $db->prepare("UPDATE tennis_games SET ai_comment = ? WHERE id = ?");
    $update->execute([$ai_comment, $gameId]);

    // --- 8. 成功レスポンスを返す ---
    echo json_encode([
        'success' => true,
        'comment' => $ai_comment
    ]);

} catch (Exception $e) {
    // エラーレスポンスを返す
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}