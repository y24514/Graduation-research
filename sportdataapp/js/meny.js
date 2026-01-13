(function () {
    const menybtn = document.querySelector('.meny-btn');
    if (!menybtn) return;

    let isOpen = false;

    menybtn.addEventListener('click', () => {
        isOpen = !isOpen;
        const nav = document.querySelector('.meny-nav');
        if (nav) {
            nav.classList.toggle('show');
        }

        menybtn.style.backgroundImage = isOpen
            ? "url('../img/meny_open.png')"
            : "url('../img/meny_close.png')";
    });
})();