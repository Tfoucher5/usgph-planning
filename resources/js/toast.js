$(function () {
    afficherNotification();
});

function afficherNotification() {
    var toastElList = [].slice.call(document.querySelectorAll('.toast'))
    var toastList = toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl, {
            animation: true,
            autohide: true,
            delay: 3000
        })
    })
    toastList.forEach(toast => toast.show())
}
