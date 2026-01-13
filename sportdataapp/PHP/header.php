<?php
// 共通ヘッダ（ナビ）
// 使用法: ページごとに $NAV_BASE を設定してから require_once 'header.php' してください。
// 例 (PHPルート内のページ): $NAV_BASE = '.'; require_once __DIR__ . '/header.php';
// 例 (swim サブフォルダ内のページ): $NAV_BASE = '..'; require_once __DIR__ . '/../header.php';
if (!isset($NAV_BASE)) {
    // デフォルトは親フォルダ扱い（安全側）
    $NAV_BASE = '.';
}

// スーパー管理者は「管理画面のみ」利用（他ページは管理画面へ誘導）
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$isSuperAdmin = !empty($_SESSION['is_super_admin']);
if ($isSuperAdmin) {
    $allowedPages = ['admin.php', 'logout.php'];
    if (!in_array($currentPage, $allowedPages, true)) {
        $target = $NAV_BASE . '/admin.php';
        if (!headers_sent()) {
            header('Location: ' . $target);
        } else {
            echo '<script>location.href=' . json_encode($target, JSON_UNESCAPED_SLASHES) . ';</script>';
        }
        exit;
    }
}
?>
<!-- 共通ナビ用スタイルを外部ファイルで読み込み -->
<?php
// HTMLテンプレートのCSS読み込みパスから判定
$css_depth = (strpos($_SERVER['REQUEST_URI'], '/swim/') !== false || strpos($_SERVER['REQUEST_URI'], '/basketball/') !== false || strpos($_SERVER['REQUEST_URI'], '/T_MNO/') !== false) ? '../../css/' : '../css/';
?>
<link rel="stylesheet" href="<?= $css_depth ?>nav.css">
<!-- 共通ナビ -->
<div class="meny">
    <button class="hamburger-btn" onclick="toggleMobileMenu()" aria-label="メニュー">
        <span></span>
        <span></span>
        <span></span>
    </button>
    
    <div class="settings-container">
        <button class="settings-btn" onclick="toggleSettingsMenu(event)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        </button>
        <div class="settings-menu" id="settingsMenu">
            <?php if (!empty($_SESSION['is_admin']) || !empty($_SESSION['is_super_admin'])): ?>
            <a href="<?= htmlspecialchars($NAV_BASE . '/admin.php', ENT_QUOTES, 'UTF-8') ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3l9 4.5v6c0 5-3.8 9.4-9 10-5.2-.6-9-5-9-10v-6L12 3z"></path>
                    <path d="M9 12l2 2 4-4"></path>
                </svg>
                管理者
            </a>
            <div class="settings-divider"></div>
            <?php endif; ?>
            <a href="<?= htmlspecialchars($NAV_BASE . '/profile_edit.php', ENT_QUOTES, 'UTF-8') ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                登録情報編集
            </a>
            <div class="settings-divider"></div>
            <a href="<?= htmlspecialchars($NAV_BASE . '/logout.php', ENT_QUOTES, 'UTF-8') ?>" onclick="return confirmLogout(event)" class="logout-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                ログアウト
            </a>
        </div>
    </div>
    <nav class="meny-nav" id="mobileNav">
        <ul class="menu-root">
            <?php if (!empty($_SESSION['is_super_admin'])): ?>
            <li class="has-sub active">
                <button>管理</button>
                <ul class="sub-menu">
                    <li><a href="<?= htmlspecialchars($NAV_BASE . '/admin.php', ENT_QUOTES, 'UTF-8') ?>">管理者</a></li>
                </ul>
            </li>
            <?php else: ?>
            <li class="has-sub <?= (basename($_SERVER['PHP_SELF']) === 'home.php' || basename($_SERVER['PHP_SELF']) === 'diary.php' || basename($_SERVER['PHP_SELF']) === 'chat_list.php' || basename($_SERVER['PHP_SELF']) === 'chat.php') ? 'active' : '' ?>">
                <button>ホーム</button>
                <ul class="sub-menu">
                    <li><a href="<?= htmlspecialchars($NAV_BASE . '/home.php', ENT_QUOTES, 'UTF-8') ?>">ダッシュボード</a></li>
                    <li><a href="<?= htmlspecialchars($NAV_BASE . '/diary.php', ENT_QUOTES, 'UTF-8') ?>">日記</a></li>
                    <li><a href="<?= htmlspecialchars($NAV_BASE . '/chat_list.php', ENT_QUOTES, 'UTF-8') ?>">チャット</a></li>
                </ul>
            </li>
            <?php if (empty($_SESSION['is_admin'])): ?>
            <li class="<?= (basename($_SERVER['PHP_SELF']) === 'pi.php') ? 'active' : '' ?>"><button><a href="<?= htmlspecialchars($NAV_BASE . '/pi.php', ENT_QUOTES, 'UTF-8') ?>">身体情報</a></button></li>
            <?php endif; ?>

            <?php
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            $isTennisPage = (strpos($requestUri, '/T_MNO/') !== false);
            ?>
            <li class="has-sub <?= $isTennisPage ? 'active' : '' ?>">
                <button>テニス</button>
                <ul class="sub-menu">
                    <li><a href="<?= htmlspecialchars($NAV_BASE . '/T_MNO/index.php', ENT_QUOTES, 'UTF-8') ?>">試合設定</a></li>
                    <li><a href="<?= htmlspecialchars($NAV_BASE . '/T_MNO/history.php', ENT_QUOTES, 'UTF-8') ?>">試合履歴</a></li>
                    <li><a href="<?= htmlspecialchars($NAV_BASE . '/T_MNO/personal_stats.php', ENT_QUOTES, 'UTF-8') ?>">個人スタッツ</a></li>
                </ul>
            </li>

            <li class="has-sub <?= (strpos($_SERVER['PHP_SELF'], 'swim') !== false) ? 'active' : '' ?>">
                <button>水泳</button>
                <ul class="sub-menu">
                    <li><a href="<?= htmlspecialchars($NAV_BASE . '/swim/swim_input.php', ENT_QUOTES, 'UTF-8') ?>">記録</a></li>
                    <li><a href="<?= htmlspecialchars($NAV_BASE . '/swim/swim_analysis.php', ENT_QUOTES, 'UTF-8') ?>">分析</a></li>
                </ul>
            </li>

            <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
            $isBasketballPage = in_array($currentPage, ['basketball_index.php', 'game.php', 'analysis.php', 'final.php', 'save_game.php', 'save_to_db.php'], true);
            ?>
            <li class="has-sub <?= ($isBasketballPage) ? 'active' : '' ?>">
                <button>バスケ</button>
                <ul class="sub-menu">
                    <li><a href="<?= htmlspecialchars($NAV_BASE . '/basketball_index.php', ENT_QUOTES, 'UTF-8') ?>">試合設定</a></li>
                    <li><a href="<?= htmlspecialchars($NAV_BASE . '/game.php', ENT_QUOTES, 'UTF-8') ?>">試合記録</a></li>
                    <li><a href="<?= htmlspecialchars($NAV_BASE . '/analysis.php', ENT_QUOTES, 'UTF-8') ?>">分析</a></li>
                    <li><a href="<?= htmlspecialchars($NAV_BASE . '/final.php', ENT_QUOTES, 'UTF-8') ?>">最終結果</a></li>
                </ul>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="app-title">Sports Analytics App</div>
</div>

<!-- ログアウト確認モーダル（JS表示） -->
<div class="logout-modal" id="logoutModal" aria-hidden="true" style="display:none">
    <div class="logout-modal__backdrop" onclick="closeLogoutModal()"></div>
    <div class="logout-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle" aria-describedby="logoutModalDesc">
        <h2 class="logout-modal__title" id="logoutModalTitle">ログアウト</h2>
        <p class="logout-modal__desc" id="logoutModalDesc">ログアウトします。未保存の内容がある場合は、先に保存してください。</p>
        <div class="logout-modal__actions">
            <button type="button" class="logout-modal__btn" onclick="closeLogoutModal()">キャンセル</button>
            <button type="button" class="logout-modal__btn logout-modal__btn--danger" id="logoutModalConfirmBtn" onclick="proceedLogout()">ログアウト</button>
        </div>
    </div>
</div>
<script>
function toggleMobileMenu() {
    const nav = document.getElementById('mobileNav');
    const hamburger = document.querySelector('.hamburger-btn');
    nav.classList.toggle('active');
    hamburger.classList.toggle('active');
}

function initMobileSubmenus() {
    const nav = document.getElementById('mobileNav');
    if (!nav) return;

    const isMobile = window.matchMedia && window.matchMedia('(max-width: 767px)').matches;
    if (!isMobile) return;

    const items = nav.querySelectorAll('.menu-root > li.has-sub > button');
    items.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            // モバイル時は親ボタンでサブメニュー開閉
            e.preventDefault();
            const li = btn.closest('li');
            if (!li) return;
            li.classList.toggle('open');
        });
    });
}

function toggleSettingsMenu(event) {
    event.stopPropagation();
    const menu = document.getElementById('settingsMenu');
    menu.classList.toggle('show');
}

// メニュー外をクリックしたら閉じる
document.addEventListener('click', function(event) {
    const menu = document.getElementById('settingsMenu');
    const settingsBtn = document.querySelector('.settings-btn');
    if (!settingsBtn.contains(event.target)) {
        menu.classList.remove('show');
    }
});

function confirmLogout(event) {
    if (event) event.preventDefault();
    const link = event?.currentTarget;
    window.__pendingLogoutHref = link?.getAttribute('href') || 'logout.php';
    openLogoutModal();
    return false;
}

function openLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (!modal) return;
    modal.style.display = 'flex';
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');

    // フォーカスを確定ボタンへ
    const confirmBtn = document.getElementById('logoutModalConfirmBtn');
    if (confirmBtn) {
        // 直後の描画で確実にフォーカス
        setTimeout(() => confirmBtn.focus(), 0);
    }
}

function closeLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (!modal) return;
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    modal.style.display = 'none';
}

function proceedLogout() {
    const href = window.__pendingLogoutHref || 'logout.php';
    window.location.href = href;
}

// Escで閉じる
document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    const modal = document.getElementById('logoutModal');
    if (modal && modal.classList.contains('show')) {
        closeLogoutModal();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    initMobileSubmenus();
});
</script>
