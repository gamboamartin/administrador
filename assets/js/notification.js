if (Notification) {
    if (Notification.permission === 'denied') {
        $(document).ready(function() {
            $('.toast').toast('show');
        });
    }
    if (Notification.permission !== 'granted') {
        Notification.requestPermission()
    }
}