$(function () {
  // showLoader はページによって `const showLoader = ...` または `window.showLoader = ...` の両方があるため両対応
  // - const showLoader: グローバル束縛だが window には載らない
  // - window.showLoader: window に載る
  const showLoaderFlag = (typeof showLoader !== 'undefined')
    ? showLoader
    : (typeof globalThis !== 'undefined' ? globalThis.showLoader : undefined);

  if (!showLoaderFlag) return;

  const $loader = $('.loader').first();
  const $spinner = $loader.find('.spinner');
  const $txt = $loader.find('.txt');
  const $progressBar = $loader.find('.progress-bar');
  const $progressPercent = $loader.find('.progress-percent');
  const $progressRole = $loader.find('[role="progressbar"]');

  // 期待するDOMが無い場合は最低限の表示/非表示だけ行う
  if ($loader.length === 0) return;

  // jQuery の show() は display:block を inline で付けてしまい、flex センタリングが効かなくなる
  $loader.css('display', 'flex');
  $spinner.css('display', 'flex');
  $txt.hide();

  // home だけ「ローディング中はページ本体を見せない」
  const hidePageDuringLoading = String($loader.data('hidePage') || $loader.data('hide-page') || '').toLowerCase() === 'true';
  if (hidePageDuringLoading) {
    document.body.classList.add('hide-page-during-loading');
  }

  if ($progressBar.length === 0 || $progressPercent.length === 0) {
    $(window).on('load', function () {
      $loader.fadeOut(400);
    });
    return;
  }

  // ランダムでラグの有無を決定（本番では条件分岐で制御可能）
  // true = ラグあり（遅い）、false = ラグなし（速い）
  const hasLag = Math.random() > 0.5;
  $loader.toggleClass('lag', hasLag);

  const pauseStartMs = hasLag ? 2000 : 0;
  const pauseDurationMs = hasLag ? 1000 : 0;
  const reach100Ms = hasLag ? 5000 : 1500;
  const hideAfterTextMs = hasLag ? 1500 : 800;

  let rafId = 0;
  const startedAt = (typeof performance !== 'undefined' && performance.now) ? performance.now() : Date.now();

  function setProgress(percent) {
    const clamped = Math.max(0, Math.min(100, Math.floor(percent)));
    $progressBar.css('width', clamped + '%');
    $progressPercent.text(clamped + '%');
    if ($progressRole.length) {
      $progressRole.attr('aria-valuenow', String(clamped));
    }
    updateLabel(clamped);
  }

  function updateLabel(p) {
    if (!$loader.length) return;
    const $label = $loader.find('.progress-label');
    if (!$label.length) return;

    // 文言はシンプルに：基本「読み込み中」→完了時のみ「完了」
    // NOTE: 100%到達後は finish() 側で少し表示してから消える
    const text = (p >= 100) ? '完了' : '読み込み中';

    // 無駄なDOM更新を避ける
    if ($label.text() !== text) $label.text(text);
  }

  function getNow() {
    return (typeof performance !== 'undefined' && performance.now) ? performance.now() : Date.now();
  }

  function effectiveElapsedMs(now) {
    let elapsed = now - startedAt;
    if (hasLag && elapsed > pauseStartMs) {
      elapsed -= Math.min(elapsed - pauseStartMs, pauseDurationMs);
    }
    return Math.max(0, elapsed);
  }

  function computePercent(elapsedMs) {
    return Math.min(100, (elapsedMs / reach100Ms) * 100);
  }

  function finish() {
    if (rafId) cancelAnimationFrame(rafId);

    setProgress(100);

    // 「完了」を一瞬見せてから消す
    const completeHoldMs = hasLag ? 650 : 450;
    const fadeOutMs = 600;

    const showTextOnComplete = ($txt.length && ($txt.data('showOnComplete') === true || $txt.data('show-on-complete') === true));
    if (showTextOnComplete) {
      $spinner.fadeOut(250, function () {
        $txt.fadeIn(250);
      });
      setTimeout(function () {
        $loader.fadeOut(fadeOutMs, function () {
          if (hidePageDuringLoading) document.body.classList.remove('hide-page-during-loading');
        });
      }, hideAfterTextMs);
      return;
    }

    // 通常: 「完了」を少し見せてからフェードアウト
    setTimeout(function () {
      $loader.fadeOut(fadeOutMs, function () {
        if (hidePageDuringLoading) document.body.classList.remove('hide-page-during-loading');
      });
    }, completeHoldMs);
  }

  // 初期表示
  setProgress(0);

  function tick() {
    const now = getNow();
    const elapsed = effectiveElapsedMs(now);
    const percent = computePercent(elapsed);
    setProgress(percent);

    if (percent >= 100) {
      finish();
      return;
    }

    rafId = requestAnimationFrame(tick);
  }

  rafId = requestAnimationFrame(tick);
});
