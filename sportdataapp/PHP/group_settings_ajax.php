<?php
require_once __DIR__ . '/session_bootstrap.php';

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    echo '<div class="empty-chat"><p>ログインが必要です</p></div>';
    exit;
}

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$chat_group_id = isset($_GET['chat_group_id']) ? intval($_GET['chat_group_id']) : 0;

if ($chat_group_id <= 0) {
    echo '<div class="empty-chat"><p>無効なグループIDです</p></div>';
    exit;
}

// データベース接続
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sportdata_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo '<div class="empty-chat"><p>データベース接続エラー</p></div>';
    exit;
}

$conn->set_charset("utf8mb4");

// グループ情報を取得
$stmt = $conn->prepare("SELECT group_name, group_description, created_by FROM chat_group_tbl WHERE chat_group_id = ? AND group_id = ?");
$stmt->bind_param("is", $chat_group_id, $group_id);
$stmt->execute();
$result = $stmt->get_result();
$group = $result->fetch_assoc();
$stmt->close();

if (!$group) {
    echo '<div class="empty-chat"><p>グループが見つかりません</p></div>';
    $conn->close();
    exit;
}

// メンバー一覧を取得
$stmt = $conn->prepare("
    SELECT m.user_id, u.name 
    FROM chat_group_member_tbl m
    JOIN login_tbl u ON m.user_id = u.user_id AND m.group_id = u.group_id
    WHERE m.chat_group_id = ? AND m.group_id = ?
    ORDER BY u.name
");
$stmt->bind_param("is", $chat_group_id, $group_id);
$stmt->execute();
$result = $stmt->get_result();
$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}
$stmt->close();

// 追加可能なメンバーを取得
$stmt = $conn->prepare("
    SELECT u.user_id, u.name 
    FROM login_tbl u
    WHERE u.group_id = ? 
    AND u.user_id NOT IN (
        SELECT m.user_id 
        FROM chat_group_member_tbl m 
        WHERE m.chat_group_id = ? AND m.group_id = ?
    )
    ORDER BY u.name
");
$stmt->bind_param("sis", $group_id, $chat_group_id, $group_id);
$stmt->execute();
$result = $stmt->get_result();
$available_members = [];
while ($row = $result->fetch_assoc()) {
    $available_members[] = $row;
}
$stmt->close();

$conn->close();

$is_creator = ($group['created_by'] === $user_id);
?>

<div class="settings-container-ajax">
    <div class="settings-header">
        <button class="back-btn" onclick="backToChat()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            チャットに戻る
        </button>
        <h2 class="settings-title">グループ設定</h2>
    </div>

    <div class="settings-content">
        <!-- グループ情報 -->
        <div class="settings-section">
            <h3 class="section-title">グループ情報</h3>
            <div class="info-item">
                <label>グループ名</label>
                <p><?= htmlspecialchars($group['group_name'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php if (!empty($group['group_description'])): ?>
            <div class="info-item">
                <label>説明</label>
                <p><?= nl2br(htmlspecialchars($group['group_description'], ENT_QUOTES, 'UTF-8')) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- メンバー一覧 -->
        <div class="settings-section">
            <h3 class="section-title">メンバー (<?= count($members) ?>人)</h3>
            <div class="member-list-settings">
                <?php foreach ($members as $member): ?>
                <?php
                require_once __DIR__ . '/user_icon_helper.php';
                $memberIcon = sportdata_find_user_icon((string)$group_id, (string)$member['user_id']);
                ?>
                <div class="member-item-settings">
                    <div class="member-avatar-settings">
                        <?php if (!empty($memberIcon['url'])): ?>
                            <img src="<?= htmlspecialchars($memberIcon['url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php else: ?>
                            <?= mb_substr($member['name'], 0, 1, 'UTF-8') ?>
                        <?php endif; ?>
                    </div>
                    <div class="member-info">
                        <span class="member-name"><?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php if ($member['user_id'] === $group['created_by']): ?>
                        <span class="member-badge">作成者</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($is_creator && $member['user_id'] !== $group['created_by']): ?>
                    <button class="btn-remove" onclick="removeMember('<?= htmlspecialchars($member['user_id'], ENT_QUOTES, 'UTF-8') ?>', <?= $chat_group_id ?>)">
                        削除
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- メンバー追加 -->
        <?php if ($is_creator && !empty($available_members)): ?>
        <div class="settings-section">
            <h3 class="section-title">メンバーを追加</h3>
            <div class="add-member-list">
                <?php foreach ($available_members as $member): ?>
                <?php
                require_once __DIR__ . '/user_icon_helper.php';
                $availableIcon = sportdata_find_user_icon((string)$group_id, (string)$member['user_id']);
                ?>
                <div class="add-member-item">
                    <div class="member-avatar-settings">
                        <?php if (!empty($availableIcon['url'])): ?>
                            <img src="<?= htmlspecialchars($availableIcon['url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php else: ?>
                            <?= mb_substr($member['name'], 0, 1, 'UTF-8') ?>
                        <?php endif; ?>
                    </div>
                    <span class="member-name"><?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <button class="btn-add" onclick="addMember('<?= htmlspecialchars($member['user_id'], ENT_QUOTES, 'UTF-8') ?>', <?= $chat_group_id ?>)">
                        追加
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- グループ削除 -->
        <?php if ($is_creator): ?>
        <div class="settings-section danger-zone">
            <h3 class="section-title">危険な操作</h3>
            <button class="btn-danger" onclick="deleteGroup(<?= $chat_group_id ?>)">
                グループを削除
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.settings-container-ajax {
    display: flex;
    flex-direction: column;
    height: 100%;
    background: white;
}

.member-avatar-settings {
    overflow: hidden;
}

.member-avatar-settings img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.settings-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
    padding: 20px 24px;
    color: white;
    display: flex;
    align-items: center;
    gap: 16px;
    border-radius: var(--radius) var(--radius) 0 0;
}

.back-btn {
    background: rgba(255, 255, 255, 0.15);
    border: 2px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    transition: var(--transition);
}

.back-btn:hover {
    background: rgba(255, 255, 255, 0.25);
}

.settings-title {
    font-size: 1.4rem;
    margin: 0;
    font-weight: 700;
}

.settings-content {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
    background: #fafbfc;
}

.settings-section {
    background: white;
    border-radius: var(--radius);
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow);
}

.section-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 16px;
}

.info-item {
    margin-bottom: 16px;
}

.info-item label {
    display: block;
    font-weight: 600;
    color: var(--muted);
    margin-bottom: 8px;
    font-size: 0.875rem;
}

.info-item p {
    color: var(--text);
    margin: 0;
    line-height: 1.6;
}

.member-list-settings,
.add-member-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.member-item-settings,
.add-member-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 12px;
    border: 2px solid var(--border);
}

.member-avatar-settings {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary) 0%, #667eea 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
}

.member-info {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
}

.member-name {
    font-weight: 600;
    color: var(--text);
}

.member-badge {
    background: var(--primary);
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.btn-remove,
.btn-add {
    padding: 6px 16px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.875rem;
}

.btn-add {
    background: var(--primary);
    color: white;
}

.btn-add:hover {
    background: var(--primary-hover);
}

.btn-remove {
    background: #fee;
    color: var(--danger);
}

.btn-remove:hover {
    background: var(--danger);
    color: white;
}

.danger-zone {
    border: 2px solid var(--danger);
}

.btn-danger {
    background: var(--danger);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: var(--transition);
    width: 100%;
}

.btn-danger:hover {
    background: #c53030;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(229, 62, 62, 0.3);
}
</style>
