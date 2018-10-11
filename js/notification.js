function Notification(message, type, parent) {
    this.type = type || null;
    this.message = message || null;
    this.$parent = (typeof parent == 'undefined') ? $('.notification-wrapper') : $(parent);
    this.$element = null;
    this.element = null;

    this.create = function() {
        var $notification = $('<div class="notification anim"></div>');
        this.$element = $notification;
        this.element = $notification[0];

        $notification[0].setAttribute('title', 'Close');

        if (this.type != null) {
            $notification.addClass(type);
        }

        if (this.message != null) {
            this.setMessage(message);
        }

        this.$parent.append($notification);

        $notification.on('click', function() {
            $(this).addClass('is-removed');
        });
    };

    this.setContent = function(node) {
        this.$element.append(node);
    };

    this.setMessage = function(message) {
        var node = $('<span>').html(message);
        this.setContent(node);

        this.message = message;
    };

    this.setType = function(type) {
        this.$element.removeClass(this.type);
        this.$element.addClass(type);

        this.type = type;
    };

    this.autohide = function(delay) {
        delay = delay || 3000;
        var $notification = this.$element;

        setTimeout(function() {
            $notification.addClass('is-removed');
        }, delay);
    };

    this.reset = function() {
        this.$element.removeClass(this.type + ' anim is-removed');
        this.$element.addClass('anim');
        this.$element.html('');
    };

    this.create();
}