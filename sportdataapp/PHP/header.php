<?php
// 共通ヘッダ（ナビ）
// 使用法: ページごとに $NAV_BASE を設定してから require_once 'header.php' してください。
// 例 (PHPルート内のページ): $NAV_BASE = '.'; require_once __DIR__ . '/header.php';
// 例 (swim サブフォルダ内のページ): $NAV_BASE = '..'; require_once __DIR__ . '/../header.php';
if (!isset($NAV_BASE)) {
    // デフォルトは親フォルダ扱い（安全側）
    $NAV_BASE = '.';
}
?>
<!-- 共通ナビ用スタイルを外部ファイルで読み込み -->
<?php /* $NAV_BASE を使ってページから見た css/nav.css の相対パスを生成します */ ?>
<link rel="stylesheet" href="<?= htmlspecialchars($NAV_BASE . '/../css/nav.css', ENT_QUOTES, 'UTF-8') ?>">
<!-- 共通ナビ -->
<div class="meny">
    <nav class="meny-nav">
        <ul class="menu-root">
            <li class="<?= (basename($_SERVER['PHP_SELF']) === 'home.php') ? 'active' : '' ?>"><button><a href="<?= htmlspecialchars($NAV_BASE, ENT_QUOTES, 'UTF-8') ?>/home.php">ホーム</a></button></li>
            <li class="<?= (basename($_SERVER['PHP_SELF']) === 'pi.php') ? 'active' : '' ?>"><button><a href="<?= htmlspecialchars($NAV_BASE, ENT_QUOTES, 'UTF-8') ?>/pi.php">身体情報</a></button></li>
            <li><button><a href="#">テニス</a></button></li>

            <li class="has-sub <?= (strpos($_SERVER['PHP_SELF'], 'swim') !== false) ? 'active' : '' ?>">
                <button>水泳</button>
                <ul class="sub-menu">
                    <li><a href="<?= htmlspecialchars($NAV_BASE, ENT_QUOTES, 'UTF-8') ?>/swim/swim_input.php">記録</a></li>
                    <li><a href="<?= htmlspecialchars($NAV_BASE, ENT_QUOTES, 'UTF-8') ?>/swim/swim_analysis.php">分析</a></li>
                </ul>
            </li>

            <li><button><a href="#">バスケ</a></button></li>
        </ul>
    </nav>
    <div class="app-title">Sports Analytics App</div>
</div>
