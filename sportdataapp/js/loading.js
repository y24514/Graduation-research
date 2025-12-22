$(function() {
  // showLoaderがtrueの場合のみローディングを表示
  if (typeof showLoader !== 'undefined' && showLoader) {
    // 最初に loader を表示
    $('.loader').show();
    $('.spinner').show();
    $('.txt').hide();

    // 3秒後にテキストに切り替え
    setTimeout(function(){
      $('.spinner').fadeOut(400, function() {
        $('.txt').fadeIn(400);
      });
    }, 3000);

    // さらに1秒後に loader を消す
    setTimeout(function(){
      $('.loader').fadeOut(800);
    }, 4000);
  }
});
