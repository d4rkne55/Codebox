function Notification(message, type, parent) {
    this.type = type || null;
    this.message = message || null;
    this.$parent = (typeof parent == 'undefined') ? $('.notification-wrapper') : $(parent);
    this.$element = null;
    this.element = null;
    this.autohideTimer = null;

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
        this.$element.html('');
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

    this.hide = function(animated) {
        animated = (typeof animated == 'undefined') ? true : animated;

        clearTimeout(this.autohideTimer);

        if (animated) {
            this.$element.addClass('anim');
        } else {
            this.$element.removeClass('anim');
        }

        this.$element.addClass('is-removed');
    };

    this.show = function(animated) {
        animated = (typeof animated == 'undefined') ? false : animated;

        if (animated) {
            this.$element.addClass('anim');
        } else {
            this.$element.removeClass('anim');
        }

        this.$element.removeClass('is-removed');
    };

    this.autohide = function(animated, delay) {
        animated = (typeof animated == 'undefined') ? true : animated;
        delay = delay || 3000;
        var self = this;

        this.autohideTimer = setTimeout(function() {
            self.hide(animated);
        }, delay);
    };

    this.reset = function() {
        clearTimeout(this.autohideTimer);
        this.$element.removeClass(this.type);
        this.$element.html('');
    };

    this.create();
}