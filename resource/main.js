$(function () {
    $.fn.tilda = function (eval, options) {
        var $body = $('body');
        if ($body.data('tilda')) {
            return $('body').data('tilda').terminal;
        }
        this.addClass('tilda');
        options = options || {};
        eval = eval || function (command, term) {
            term.echo("you don't set eval for tilda");
        };
        var settings = {
            prompt: '$> ',
            name: 'tilda',
            height: 500,
            enabled: true,
            greetings: 'SERVER TERMINAL',
            keypress: function (e) {
                if (e.which === 96) {
                    return false;
                }
            }
        };
        if (options) {
            $.extend(settings, options);
        }
        this.append('<div class="td"></div>');
        var self = this;
        self.terminal = this.find('.td').terminal(eval, settings);
        var focus = false;
        $(document.documentElement).keypress(function (e) {
            if (e.which === 96) {
                self.slideToggle('fast');
                self.terminal.focus(focus = !focus);
                self.terminal.attr({
                    scrollTop: self.terminal.attr("scrollHeight")
                });
            }
        });
        $body.data('tilda', this);
        this.hide();
        return self;
    };

    $('#tilda').tilda(function (command, terminal) {
        if (command !== '') {
            $.ajax({
                type: "POST",
                url: "/cmd.php",
                async: false,
                data: {
                    cmd: command,
                    pwd: terminal.get_prompt()
                },
                dataType: 'json'
            }).done(function (response) {
                if (response.status === 'success') {
                    terminal.echo(response['result']);
                    terminal.set_prompt(response['pwd']);
                } else {
                    terminal.error(response.result);
                }
            });
        }
    });
});