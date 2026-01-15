<?php
// 共通ヘッダ（ナビ）
// 使用法: ページごとに $NAV_BASE を設定してから require_once 'header.php' してください。
// 例 (PHPルート内のページ): $NAV_BASE = '.'; require_once __DIR__ . '/header.php';
// 例 (swim サブフォルダ内のページ): $NAV_BASE = '..'; require_once __DIR__ . '/../header.php';
if (!isset($NAV_BASE)) {
    // デフォルトは親フォルダ扱い（安全側）
    $NAV_BASE = '.';
}

// タブごとのセッション分離用 tab_id
$__tabId = (string)($_GET['tab_id'] ?? ($_POST['tab_id'] ?? ($GLOBALS['SPORTDATA_TAB_ID'] ?? '')));
if ($__tabId !== '' && !preg_match('/^[A-Za-z0-9_-]{8,64}$/', $__tabId)) {
    $__tabId = '';
}

function sportdata_add_tab_id(string $url, string $tabId): string {
    if ($tabId === '') return $url;
    $sep = (strpos($url, '?') !== false) ? '&' : '?';
    return $url . $sep . 'tab_id=' . rawurlencode($tabId);
}

// スーパー管理者は「管理画面のみ」利用（他ページは管理画面へ誘導）
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$isSuperAdmin = !empty($_SESSION['is_super_admin']);
if ($isSuperAdmin) {
    $allowedPages = ['admin.php', 'logout.php', 'contact.php'];
    if (!in_array($currentPage, $allowedPages, true)) {
        $target = sportdata_add_tab_id($NAV_BASE . '/admin.php', $__tabId);
        if (!headers_sent()) {
            header('Location: ' . $target);
        } else {
            echo '<script>location.href=' . json_encode($target, JSON_UNESCAPED_SLASHES) . ';</script>';
        }
        exit;
    }
}

// 種目によるメニュー出し分け
// - sport が未設定/空/"all" の場合: 全て表示
// - 管理者/スーパー管理者: 全て表示
$isAdminUser = !empty($_SESSION['is_admin']) || !empty($_SESSION['is_super_admin']);
$isViewerAdmin = !empty($_SESSION['is_admin']) && empty($_SESSION['is_super_admin']);
$rawSport = isset($_SESSION['sport']) ? (string)$_SESSION['sport'] : '';
$rawSport = strtolower(trim($rawSport));
$selectedSports = [];
if ($rawSport !== '') {
    // "swim,basketball" のようなCSVを許容
    $parts = preg_split('/\s*,\s*/', $rawSport, -1, PREG_SPLIT_NO_EMPTY);
    if (is_array($parts)) {
        foreach ($parts as $p) {
            $p = strtolower(trim((string)$p));
            if ($p !== '') {
                $selectedSports[] = $p;
            }
        }
    }
}
$restrictSports = (!$isAdminUser && !empty($selectedSports) && !in_array('all', $selectedSports, true));
$canShowTennis = (!$restrictSports) || in_array('tennis', $selectedSports, true);
$canShowSwim = (!$restrictSports) || in_array('swim', $selectedSports, true);
$canShowBasketball = (!$restrictSports) || in_array('basketball', $selectedSports, true);

// 管理者（先生/顧問）は「記録」系メニューを非表示にする
if ($isViewerAdmin) {
    $canShowTennis = false;
    $canShowSwim = false;
    $canShowBasketball = false;
}

// お問い合わせ（モーダル）用CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$__csrfToken = (string)$_SESSION['csrf_token'];
?>
<!-- 共通ナビ用スタイルを外部ファイルで読み込み -->
<?php
// HTMLテンプレートのCSS読み込みパスから判定
$css_depth = (strpos($_SERVER['REQUEST_URI'], '/swim/') !== false || strpos($_SERVER['REQUEST_URI'], '/basketball/') !== false || strpos($_SERVER['REQUEST_URI'], '/T_MNO/') !== false || strpos($_SERVER['REQUEST_URI'], '/T_board/') !== false || strpos($_SERVER['REQUEST_URI'], '/B_board/') !== false) ? '../../css/' : '../css/';
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
            <?php if (empty($_SESSION['is_super_admin'])): ?>
            <a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/contact.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>" onclick="return openContactModal(event)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path>
                </svg>
                お問い合わせ
            </a>
            <?php endif; ?>
            <a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/profile_edit.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                登録情報編集
            </a>
            <div class="settings-divider"></div>
            <a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/logout.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>" onclick="return confirmLogout(event)" class="logout-link">
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
                    <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/admin.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">管理者</a></li>
                </ul>
            </li>
            <?php else: ?>
            <?php if (!empty($_SESSION['is_admin']) && empty($_SESSION['is_super_admin'])): ?>
            <li class="<?= (basename($_SERVER['PHP_SELF']) === 'admin.php') ? 'active' : '' ?>">
                <button><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/admin.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">管理</a></button>
            </li>
            <?php endif; ?>
            <li class="has-sub <?= (basename($_SERVER['PHP_SELF']) === 'home.php' || basename($_SERVER['PHP_SELF']) === 'diary.php' || basename($_SERVER['PHP_SELF']) === 'chat_list.php' || basename($_SERVER['PHP_SELF']) === 'chat.php') ? 'active' : '' ?>">
                <button>ホーム</button>
                <ul class="sub-menu">
                    <?php if (empty($_SESSION['is_admin'])): ?>
                    <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/home.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">ダッシュボード</a></li>
                    <?php endif; ?>
                    <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/diary.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">日記</a></li>
                    <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/chat_list.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">チャット</a></li>
                </ul>
            </li>
            <?php if (empty($_SESSION['is_admin'])): ?>
            <li class="<?= (basename($_SERVER['PHP_SELF']) === 'pi.php') ? 'active' : '' ?>"><button><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/pi.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">身体情報</a></button></li>
            <?php endif; ?>

            <?php
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            $isTennisPage = (strpos($requestUri, '/T_MNO/') !== false || strpos($requestUri, '/T_board/') !== false);
            ?>
            <?php if ($canShowTennis): ?>
            <li class="has-sub <?= $isTennisPage ? 'active' : '' ?>">
                <button>テニス</button>
                <ul class="sub-menu">
                    <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/T_MNO/index.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">試合設定</a></li>
                    <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/T_MNO/history.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">試合履歴</a></li>
                    <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/T_MNO/personal_stats.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">個人スタッツ</a></li>
                                <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/T_board/T_board.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">作戦ボード</a></li>
                </ul>
            </li>

            <?php endif; ?>

            <?php if ($canShowSwim): ?>
            <li class="has-sub <?= (strpos($_SERVER['PHP_SELF'], 'swim') !== false) ? 'active' : '' ?>">
                <button>水泳</button>
                <ul class="sub-menu">
                    <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/swim/swim_input.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">記録</a></li>
                    <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/swim/swim_practice_create.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">練習作成</a></li>
                    <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/swim/swim_analysis.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">分析</a></li>
                </ul>
            </li>
            <?php endif; ?>

            <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
            $isBasketballPage = in_array($currentPage, ['basketball_index.php', 'game.php', 'analysis.php', 'final.php', 'save_game.php', 'save_to_db.php', 'B_board.php'], true);
            ?>
            <?php if ($canShowBasketball): ?>
            <li class="has-sub <?= ($isBasketballPage) ? 'active' : '' ?>">
                <button>バスケ</button>
                <ul class="sub-menu">
                    <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/basketball_index.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">試合設定</a></li>
                    <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/game.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">試合記録</a></li>
                    <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/analysis.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">分析</a></li>
                    <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/final.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">最終結果</a></li>
                    <li><a href="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/B_board/B_board.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">作戦ボード</a></li>
                </ul>
            </li>
            <?php endif; ?>
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

<!-- お問い合わせモーダル（JS表示 / JS無効時は contact.php へ遷移） -->
<div class="contact-modal" id="contactModal" aria-hidden="true" style="display:none">
    <div class="contact-modal__backdrop" onclick="closeContactModal()"></div>
    <div class="contact-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="contactModalTitle" aria-describedby="contactModalDesc">
        <h2 class="contact-modal__title" id="contactModalTitle">お問い合わせ</h2>
        <p class="contact-modal__desc" id="contactModalDesc">不具合報告・改善要望などを送信できます。</p>
        <div class="contact-modal__flash" id="contactModalFlash"></div>

        <form id="contactModalForm" method="post" action="<?= htmlspecialchars(sportdata_add_tab_id($NAV_BASE . '/contact.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($__csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($__tabId !== ''): ?>
                <input type="hidden" name="tab_id" value="<?= htmlspecialchars($__tabId, ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>

            <div class="contact-modal__row">
                <label class="contact-modal__label" for="contactModalCategory">種類</label>
                <select class="contact-modal__select" id="contactModalCategory" name="category" required>
                    <option value="bug">不具合</option>
                    <option value="improve">改善</option>
                    <option value="other">その他</option>
                </select>
            </div>

            <div class="contact-modal__row">
                <label class="contact-modal__label" for="contactModalSubject">件名</label>
                <input class="contact-modal__input" id="contactModalSubject" name="subject" type="text" maxlength="200" required>
            </div>

            <div class="contact-modal__row">
                <label class="contact-modal__label" for="contactModalMessage">内容</label>
                <textarea class="contact-modal__textarea" id="contactModalMessage" name="message" maxlength="4000" required></textarea>
            </div>

            <div class="contact-modal__actions">
                <button type="button" class="contact-modal__btn" onclick="closeContactModal()">閉じる</button>
                <button type="submit" class="contact-modal__btn contact-modal__btn--primary" id="contactModalSubmitBtn">送信</button>
            </div>
        </form>
    </div>
</div>
<script>
function isMobileNav() {
    return window.matchMedia && window.matchMedia('(max-width: 767px)').matches;
}

function closeAllDesktopSubmenus() {
    const nav = document.getElementById('mobileNav');
    if (!nav) return;
    nav.querySelectorAll('.menu-root > li.has-sub.open').forEach((li) => {
        li.classList.remove('open');
        const btn = li.querySelector(':scope > button');
        if (btn) btn.setAttribute('aria-expanded', 'false');
    });
}

function toggleMobileMenu() {
    const nav = document.getElementById('mobileNav');
    const hamburger = document.querySelector('.hamburger-btn');
    nav.classList.toggle('active');
    hamburger.classList.toggle('active');

    // 閉じたときはサブメニューも閉じる
    if (!nav.classList.contains('active')) {
        closeAllMobileSubmenus();
    }
}

function closeAllMobileSubmenus() {
    const nav = document.getElementById('mobileNav');
    if (!nav) return;

    nav.querySelectorAll('.menu-root > li.has-sub.open').forEach((li) => {
        li.classList.remove('open');
        const btn = li.querySelector(':scope > button');
        if (btn) btn.setAttribute('aria-expanded', 'false');
    });
}

function closeMobileNav() {
    const nav = document.getElementById('mobileNav');
    const hamburger = document.querySelector('.hamburger-btn');
    if (!nav || !hamburger) return;
    nav.classList.remove('active');
    hamburger.classList.remove('active');
    closeAllMobileSubmenus();
}

function initMobileSubmenus() {
    const nav = document.getElementById('mobileNav');
    if (!nav) return;

    if (!isMobileNav()) return;

    // 二重登録防止
    if (nav.dataset.submenusInit === '1') return;
    nav.dataset.submenusInit = '1';

    // aria-controls 用に submenu にID付与
    let submenuIndex = 0;
    nav.querySelectorAll('.menu-root > li.has-sub').forEach((li) => {
        const btn = li.querySelector(':scope > button');
        const submenu = li.querySelector(':scope > .sub-menu');
        if (!btn || !submenu) return;
        if (!submenu.id) {
            submenuIndex += 1;
            submenu.id = `submenu-${submenuIndex}`;
        }
        btn.setAttribute('aria-haspopup', 'true');
        btn.setAttribute('aria-controls', submenu.id);
        btn.setAttribute('aria-expanded', li.classList.contains('open') ? 'true' : 'false');
    });

    const items = nav.querySelectorAll('.menu-root > li.has-sub > button');
    items.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            // モバイル時は親ボタンでサブメニュー開閉
            e.preventDefault();
            const li = btn.closest('li');
            if (!li) return;

            // アコーディオン（他は閉じる）
            nav.querySelectorAll('.menu-root > li.has-sub.open').forEach((other) => {
                if (other === li) return;
                other.classList.remove('open');
                const otherBtn = other.querySelector(':scope > button');
                if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
            });

            const willOpen = !li.classList.contains('open');
            li.classList.toggle('open');
            btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });

    // サブメニューのリンクを押したらナビ全体を閉じる
    nav.querySelectorAll('.sub-menu a').forEach((a) => {
        a.addEventListener('click', () => {
            closeMobileNav();
        });
    });
}

function initDesktopSubmenus() {
    const nav = document.getElementById('mobileNav');
    if (!nav) return;
    if (isMobileNav()) return;

    // 二重登録防止
    if (nav.dataset.desktopSubmenusInit === '1') return;
    nav.dataset.desktopSubmenusInit = '1';

    // aria-controls 用にsubmenuにID付与（未設定のものだけ）
    let submenuIndex = 0;
    nav.querySelectorAll('.menu-root > li.has-sub').forEach((li) => {
        const btn = li.querySelector(':scope > button');
        const submenu = li.querySelector(':scope > .sub-menu');
        if (!btn || !submenu) return;
        if (!submenu.id) {
            submenuIndex += 1;
            submenu.id = `submenu-${submenuIndex}`;
        }
        btn.setAttribute('aria-haspopup', 'true');
        btn.setAttribute('aria-controls', submenu.id);
        btn.setAttribute('aria-expanded', li.classList.contains('open') ? 'true' : 'false');
    });

    nav.querySelectorAll('.menu-root > li.has-sub > button').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            // 親ボタンはリンクではないので常に開閉
            e.preventDefault();
            const li = btn.closest('li');
            if (!li) return;

            const willOpen = !li.classList.contains('open');
            // 他を閉じる
            nav.querySelectorAll('.menu-root > li.has-sub.open').forEach((other) => {
                if (other === li) return;
                other.classList.remove('open');
                const otherBtn = other.querySelector(':scope > button');
                if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
            });

            li.classList.toggle('open');
            btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });

        // キーボード操作: Enter/Spaceで開閉
        btn.addEventListener('keydown', (e) => {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            e.preventDefault();
            btn.click();
        });
    });

    // サブメニュー項目を押したら閉じる
    nav.querySelectorAll('.sub-menu a').forEach((a) => {
        a.addEventListener('click', () => {
            closeAllDesktopSubmenus();
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

    // モバイルナビが開いているとき、外側タップで閉じる
    if (isMobileNav()) {
        const nav = document.getElementById('mobileNav');
        const hamburger = document.querySelector('.hamburger-btn');
        if (nav && hamburger && nav.classList.contains('active')) {
            const clickedInsideNav = nav.contains(event.target);
            const clickedHamburger = hamburger.contains(event.target);
            if (!clickedInsideNav && !clickedHamburger) {
                closeMobileNav();
            }
        }
    }

    // Desktop: 外側クリックでサブメニューを閉じる
    if (!isMobileNav()) {
        const nav = document.getElementById('mobileNav');
        if (nav) {
            const clickedInsideNav = nav.contains(event.target);
            if (!clickedInsideNav) {
                closeAllDesktopSubmenus();
            }
        }
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

function openContactModal(event) {
    if (event) event.preventDefault();
    const modal = document.getElementById('contactModal');
    if (!modal) return false;
    modal.style.display = 'flex';
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');

    // 設定メニューを閉じる
    const menu = document.getElementById('settingsMenu');
    if (menu) menu.classList.remove('show');

    const subject = document.getElementById('contactModalSubject');
    if (subject) {
        setTimeout(() => subject.focus(), 0);
    }
    return false;
}

function closeContactModal() {
    const modal = document.getElementById('contactModal');
    if (!modal) return;
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    modal.style.display = 'none';
}

function setContactModalFlash(message, type) {
    const el = document.getElementById('contactModalFlash');
    if (!el) return;
    el.className = 'contact-modal__flash is-show' + (type === 'error' ? ' is-error' : ' is-success');
    el.textContent = message;
}

function clearContactModalFlash() {
    const el = document.getElementById('contactModalFlash');
    if (!el) return;
    el.className = 'contact-modal__flash';
    el.textContent = '';
}

// Escで閉じる
document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    const modal = document.getElementById('logoutModal');
    if (modal && modal.classList.contains('show')) {
        closeLogoutModal();
        return;
    }

    const contactModal = document.getElementById('contactModal');
    if (contactModal && contactModal.classList.contains('show')) {
        closeContactModal();
        return;
    }

    // モバイルナビを閉じる
    if (isMobileNav()) {
        const nav = document.getElementById('mobileNav');
        if (nav && nav.classList.contains('active')) {
            closeMobileNav();
        }
    }

    // Desktop: ESCでサブメニューを閉じる
    if (!isMobileNav()) {
        closeAllDesktopSubmenus();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    initMobileSubmenus();
    initDesktopSubmenus();

    // お問い合わせ送信（JS有効時はページ遷移せずモーダル内で送信）
    const contactForm = document.getElementById('contactModalForm');
    if (contactForm) {
        contactForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            clearContactModalFlash();

            const submitBtn = document.getElementById('contactModalSubmitBtn');
            if (submitBtn) submitBtn.disabled = true;

            try {
                const fd = new FormData(contactForm);
                const url = <?= json_encode(sportdata_add_tab_id($NAV_BASE . '/contact_submit_ajax.php', $__tabId), JSON_UNESCAPED_SLASHES) ?>;
                const res = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: fd,
                    headers: { 'Accept': 'application/json' }
                });

                const data = await res.json().catch(() => null);
                if (!data) {
                    setContactModalFlash('送信に失敗しました。', 'error');
                    return;
                }

                if (!res.ok || !data.ok) {
                    setContactModalFlash(data.message || '送信に失敗しました。', 'error');
                    return;
                }

                setContactModalFlash('お問い合わせを送信しました。', 'success');
                const subject = document.getElementById('contactModalSubject');
                const message = document.getElementById('contactModalMessage');
                if (subject) subject.value = '';
                if (message) message.value = '';
            } catch (err) {
                setContactModalFlash('通信に失敗しました。', 'error');
            } finally {
                const submitBtn2 = document.getElementById('contactModalSubmitBtn');
                if (submitBtn2) submitBtn2.disabled = false;
            }
        });
    }

    // 画面サイズが変わったら初期化し直す
    window.addEventListener('resize', () => {
        const nav = document.getElementById('mobileNav');
        if (!nav) return;
        // フラグを落として再初期化（イベント多重付与のリスクはあるため、状態切替のときだけ）
        // ここでは閾値を跨いだときだけ再初期化
        const nowMobile = isMobileNav();
        const prevMobile = nav.dataset.prevMobile === '1';
        if (nowMobile === prevMobile) return;
        nav.dataset.prevMobile = nowMobile ? '1' : '0';

        // 状態リセット
        closeMobileNav();
        closeAllDesktopSubmenus();
        nav.dataset.submenusInit = '0';
        nav.dataset.desktopSubmenusInit = '0';

        // 再初期化
        initMobileSubmenus();
        initDesktopSubmenus();
    });
});
</script>
